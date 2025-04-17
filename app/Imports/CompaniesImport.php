<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CompaniesImport implements ToCollection
{
    /**
     * @param  Collection  $collection
     */
    public $companies;

    public function collection(Collection $collection)
    {
        $headers = $collection->first();
        $dataRows = $collection->slice(1);

        $mapped = $dataRows->map(function ($row) {
            return array_combine(['name', 'domain', 'phone', 'industry'], $row->toArray());
        });

        $this->companies = $mapped;
    }
}
