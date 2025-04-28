<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\Request;

class WebHookController extends Controller
{
    public function syncContact(Request $request)
    {
        $data = $request->all();


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
}
