<?php

namespace App\Http\Controllers;

use App\Http\Integrations\Hubspot\HubspotConnector;
use App\Http\Integrations\Hubspot\Objects\Contacts\Requests\FetchContact;
use App\Http\Integrations\Hubspot\Property\Company\Requests\FetchCompany;
use App\Http\Integrations\Hubspot\Requests\Associations\ContactCompanyAssociations;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebHookController extends Controller
{
    public function syncContact(Request $request)
    {
        $data = $request->all();

        logger()->info(json_encode($data));

        $contact = Contact::upsert(
            [
                'firstname' => $data['firstname'] ?? null,
                'lastname' => $data['lastname'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'hs_object_id' => $data['hs_object_id'],
            ],
            ['hs_object_id'],
            ['firstname', 'lastname', 'email', 'phone', 'company']
        );

        logger()->info('Contact synced successfully', [
            'hs_object_id' => $data['hs_object_id'],
            'properties' => $contact,
        ]);
    }

    public function syncCompanies(Request $request)
    {
        $data = $request->all();
        logger()->info('syncCompanies', [
            'data' => $data,
        ]);

        $company = Company::upsert(
            [
                'name' => $data['name'] ?? null,
                'domain' => $data['domain'] ?? null,
                'phone' => $data['phone'] ?? null,
                'industry' => $data['industry'] ?? null,
                'hs_object_id' => $data['hs_object_id'],
            ],
            ['hs_object_id'],
            ['name', 'domain', 'phone', 'industry']
        );

        logger()->info('Contact synced successfully', [
            'hs_object_id' => $data['hs_object_id'],
            'properties' => $company,
        ]);
    }
    public function contactAssociationSync(Request $request)
    {
        $contactId = $request["properties"]['hs_object_id']['value'] ?? null;
    
        if (!$contactId) {
            logger()->error('Missing contact ID in request');
            return response()->json(['error' => 'Missing contact ID'], 400);
        }
    
        try {
            // Get associations from HubSpot
            $associations = $this->getContactCompanyAssociations($contactId);
            
            if (empty($associations)) {
                logger()->info("No company associations found for contact", ['contact_id' => $contactId]);
                return response()->json(['message' => 'No associations found']);
            }
    
            DB::transaction(function () use ($contactId, $associations) {
                DB::table('company_contact')->where('contact_id', $contactId)->delete();
                
                $successfulAssociations = 0;
                
                foreach ($associations as $company) {
                    $companyId = $company['toObjectId'];
                    
                    if ($this->syncContactIfMissing($contactId) && $this->syncCompanyIfMissing($companyId)) {
                        DB::table('company_contact')->insert([
                            'contact_id' => $contactId,
                            'company_id' => $companyId,
                        ]);
                        $successfulAssociations++;
                    }
                }
                
                logger()->info('Associations processed', [
                    'contact_id' => $contactId,
                    'total' => count($associations),
                    'successful' => $successfulAssociations
                ]);
            });
    
            return response()->json(['message' => 'Association sync completed']);
        } catch (\Exception $e) {
            logger()->error('HubSpot API error', [
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'HubSpot API error'], 500);
        }
    }
    
    protected function getContactCompanyAssociations($contactId)
    {
        $hubspotRequest = new ContactCompanyAssociations($contactId);
        $hubspotRequest->headers()->add('Authorization', 'Bearer ' . config('services.hubspot.api_key'));
    
        $connector = new HubspotConnector;
        $response = $connector->send($hubspotRequest);
    
        return $response->json('results') ?? [];
    }
    
    public function syncContactIfMissing($contactId): bool
    {
        if (Contact::find($contactId)) {
            return true;
        }
    
        try {
            $data = $this->fetchHubSpotContact($contactId);
            
            Contact::create([
                'firstname' => $data['properties']['firstname'] ?? null,
                'lastname' => $data['properties']['lastname'] ?? null,
                'email' => $data['properties']['email'] ?? null,
                'phone' => $data['properties']['phone'] ?? null,
                'company' => $data['properties']['company'] ?? null,
                'hs_object_id' => $data['properties']['hs_object_id'] ?? null,
            ]);
    
            logger()->info('Contact created', ['contact_id' => $contactId]);
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to sync contact', [
                'contact_id' => $contactId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public function syncCompanyIfMissing($companyId): bool
    {
        if (Company::find($companyId)) {
            return true;
        }
    
        try {
            $data = $this->fetchHubSpotCompany($companyId);
            
            Company::create([
                'name' => $data['properties']['name'] ?? null,
                'domain' => $data['properties']['domain'] ?? null,
                'phone' => $data['properties']['phone'] ?? null,
                'industry' => $data['properties']['industry'] ?? null,
                'hs_object_id' => $data['properties']['hs_object_id'] ?? null,
            ]);
    
            logger()->info('Company created', ['company_id' => $companyId]);
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to sync company', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    protected function fetchHubSpotContact($contactId)
    {
        $hubspotRequest = new FetchContact($contactId);
        $hubspotRequest->headers()->add('Authorization', 'Bearer ' . config('services.hubspot.api_key'));
    
        $connector = new HubspotConnector;
        $response = $connector->send($hubspotRequest);
    
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch contact from HubSpot");
        }
    
        return $response->json();
    }
    
    protected function fetchHubSpotCompany($companyId)
    {
        $hubspotRequest = new FetchCompany($companyId);
        $hubspotRequest->headers()->add('Authorization', 'Bearer ' . config('services.hubspot.api_key'));
    
        $connector = new HubspotConnector;
        $response = $connector->send($hubspotRequest);
    
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch company from HubSpot");
        }
    
        return $response->json();
    }
}
