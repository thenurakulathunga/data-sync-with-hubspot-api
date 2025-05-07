<?php

namespace App\Http\Integrations\Hubspot\Property\Company\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class FetchCompany extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * Default properties to fetch for a company
     */
    protected array $defaultProperties = [
        'name',
        'domain',
        'phone',
        'industry',
        'hs_object_id'
    ];

    /**
     * Create a new request instance
     */
    public function __construct(
        protected string $companyId,
        protected array $properties = [],
        protected bool $includeArchived = false
    ) {
        // Merge with default properties if custom properties are provided
        $this->properties = empty($properties) 
            ? $this->defaultProperties 
            : array_unique(array_merge($this->defaultProperties, $properties));
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "/crm/v3/objects/companies/{$this->companyId}";
    }

    /**
     * Default query parameters
     */
    protected function defaultQuery(): array
    {
        return [
            'archived' => $this->includeArchived ? 'true' : 'false',
            'properties' => $this->properties
        ];
    }
}