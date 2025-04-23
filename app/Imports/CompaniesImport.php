<?php

namespace App\Imports;

use App\Http\Livewire\Concerns\WithToast;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class CompaniesImport implements ToCollection
{
    use WithToast;

    public $companies;

    public $validHeaders = ['name', 'domain', 'phone', 'industry'];

    public function collection(Collection $collection)
    {
        $originalHeaders = $collection->first()->values()->toArray();

        $mappedHeaders = collect($originalHeaders)->map(function ($header) {
            return $this->mapHeader($header);
        })->toArray();

        $missingHeaders = array_diff($this->validHeaders, $mappedHeaders);

        if (!empty($missingHeaders)) {
            $this->toastError('Invalid CSV!');
            throw ValidationException::withMessages([
                'companiesCsv' => 'Invalid CSV!',
            ]);
        }

        $dataRows = $collection->slice(1);

        $this->companies = $dataRows->map(function ($row) use ($mappedHeaders) {
            return array_combine($mappedHeaders, $row->toArray());
        });
    }

    protected function mapHeader($header)
    {
        switch (strtolower(trim($header))) {
            case 'Company Name':
            case 'company name':
            case 'company':
                return 'name';
            case 'Domain':
            case 'domain':
                return 'domain';
            case 'Phone Number':
            case 'phone':
            case 'phone number':
                return 'phone';
            case 'Industry':
            case 'industry':
                return 'industry';
            default:
                return strtolower($header);
        }
    }
}
