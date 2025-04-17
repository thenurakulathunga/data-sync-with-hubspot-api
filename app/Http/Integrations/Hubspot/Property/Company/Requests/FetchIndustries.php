<?php

namespace App\Http\Integrations\Hubspot\Property\Company\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class FetchIndustries extends Request
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
        return '/crm/v3/properties/companies/industry';
    }
}
