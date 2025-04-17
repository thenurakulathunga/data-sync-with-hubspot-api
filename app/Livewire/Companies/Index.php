<?php

namespace App\Livewire\Companies;

use App\Http\Integrations\Hubspot\HubspotConnector;
use App\Http\Integrations\Hubspot\Property\Company\Requests\FetchIndustries;
use App\Imports\CompaniesImport;
use App\Jobs\SyncCompaniesJob;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:csv,txt', message: 'Please upload a valid CSV file')]
    public $companiesCsv;

    public function save()
    {
        $this->validate();

        if (empty(cache()->get('CompanyProperty')['options'])) {
            $this->getCachedCompanyProperties();
        }

        $import = new CompaniesImport;
        Excel::import($import, $this->companiesCsv);

        $companies = $import->companies;

        $companies = $companies->values()->map(function ($company) {
            $matchedIndustry = $this->searchMatchedCompanyIndustry($company['industry'] ?? null);

            if ($matchedIndustry !== null) {
                $company['industry'] = $matchedIndustry;
            } else {
                unset($company['industry']);
            }

            return $company;
        });

        collect($companies)
            ->values()
            ->chunk(10)
            ->each(function ($chunk) {
                SyncCompaniesJob::dispatch($chunk->values()->toArray());
            });
    }

    protected function searchMatchedCompanyIndustry($injectSearchTerm)
    {
        $cachedIndustries = cache()->get('CompanyProperty')['options'] ?? null;

        $searchTerm = $injectSearchTerm;

        $index = null;

        foreach ($cachedIndustries as $key => $industry) {
            if (stripos($industry['label'], $searchTerm) !== false) {
                $index = $key;
                break;
            }
        }

        return $cachedIndustries[$index]['value'] ?? null;
    }

    protected function getCachedCompanyProperties()
    {
        return cache()->remember('CompanyProperty', now()->addDay(), function () {
            try {
                $request = new FetchIndustries;
                $request->headers()->add('Authorization', 'Bearer '.config('services.hubspot.api_key'));

                $confection = new HubspotConnector;
                $response = $confection->send($request);

                return $response->json();
            } catch (\Exception $e) {
                info('Failed to fetch company properties', [
                    'status' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    public function render()
    {
        return view('livewire.companies.index');
    }
}
