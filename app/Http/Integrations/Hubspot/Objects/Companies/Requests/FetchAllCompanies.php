<?php

namespace App\Http\Integrations\Hubspot\Objects\Companies\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class FetchAllCompanies extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/crm/v3/objects/companies?limit=100';
    }
}
