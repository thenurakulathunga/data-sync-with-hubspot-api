<?php

namespace App\Livewire\Contact;

use App\Http\Integrations\Hubspot\HubspotConnector;
use App\Http\Integrations\Hubspot\Objects\Companies\Requests\FetchAllCompanies;
use App\Imports\ContactImport;
use App\Jobs\SyncContactJob;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:csv,txt', message: 'Please upload a valid CSV file')]
    public $contactCsv;

    public function render()
    {
        return view('livewire.contact.index');
    }

    public function save()
    {
        $this->validate();

        $this->getCachedCompany();


        $import = new ContactImport;
        Excel::import($import, $this->contactCsv);

        $contacts = $import->contacts;

        $contacts = $contacts->values()->map(function ($contact) {
            $associateCompanyId = $this->associateContactsWithCompanies($contact);
            if (! empty($associateCompanyId)) {
                $contact['associate_company'] = $associateCompanyId;
            }

            return $contact;
        });

        collect($contacts)
            ->values()
            ->chunk(10)
            ->each(function ($chunk) {
                SyncContactJob::dispatch($chunk->values()->toArray());
            });
    }

    public function associateContactsWithCompanies(array $contact)
    {
        $companies = cache()->get('Companies') ?? null;
        $matchedCompanyId = null;
        foreach ($companies['results'] as $item) {
            if ($item['properties']['name'] === $contact['company']) {
                $matchedCompanyId = $item['id'];
            }
        }

        return $matchedCompanyId;
    }

    public function getCachedCompany()
    {
        try {
            $request = new FetchAllCompanies;
            $request->headers()->add('Authorization', 'Bearer ' . config('services.hubspot.api_key'));

            $confection = new HubspotConnector;
            $response = $confection->send($request);
            cache()->put('Companies', $response->json(), now()->addDay());
        } catch (\Exception $e) {
            info('Failed to fetch company properties', [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
