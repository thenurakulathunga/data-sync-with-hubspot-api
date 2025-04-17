<?php

namespace App\Imports;

use App\Http\Livewire\Concerns\WithToast;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class ContactImport implements ToCollection
{
    use WithToast;

    public $contacts;

    public $validHeaders = ['firstname', 'lastname', 'email', 'phone', 'company'];

    public function collection(Collection $collection)
    {

        $originalHeaders = $collection->first()->values()->toArray();

        $mappedHeaders = collect($originalHeaders)->map(function ($header) {
            return $this->mapHeader($header);
        })->toArray();

        $missingHeaders = array_diff($this->validHeaders, $mappedHeaders);

        if (! empty($missingHeaders)) {
            $this->toastError('Invalid Csv!');
            throw ValidationException::withMessages([
                'contactCsv' => 'Invalid Csv!',
            ]);
        }

        $dataRows = $collection->slice(1);

        $this->contacts = $dataRows->map(function ($row) use ($mappedHeaders) {
            return array_combine($mappedHeaders, $row->toArray());
        });
    }

    protected function mapHeader($header)
    {
        switch (strtolower(trim($header))) {
            case 'First Name':
            case 'first name':
            case 'firstname':
                return 'firstname';
            case 'Last Name':
            case 'last name':
            case 'lastname':
                return 'lastname';
            case 'Email':
            case 'email':
                return 'email';
            case 'Phone Number':
            case 'phone number':
            case 'phone':
                return 'phone';
            case 'Company Name':
            case 'company name':
            case 'company':
                return 'company';
            default:
                return strtolower($header);
        }
    }
}
