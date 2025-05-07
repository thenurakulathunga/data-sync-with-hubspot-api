<?php

namespace App\Http\Integrations\Hubspot\Objects\Contacts\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class FetchContact extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * Create a new request instance
     */
    public function __construct(
        protected string $contactId,
        protected array $properties = [
            'firstname',
            'lastname',
            'email',
            'phone',
            'company',
            'hs_object_id'
        ],
        protected bool $includeArchived = false
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "/crm/v3/objects/contacts/{$this->contactId}";
    }

    /**
     * Default query parameters
     */
    protected function defaultQuery(): array
    {
        $query = [
            'archived' => $this->includeArchived ? 'true' : 'false'
        ];

        // Add each property as a separate query parameter
        foreach ($this->properties as $property) {
            $query['properties'][] = $property;
        }

        return $query;
    }
}