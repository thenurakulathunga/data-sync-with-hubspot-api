<?php

namespace App\Http\Integrations\Hubspot\Requests\Associations;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ContactCompanyAssociations extends Request
{
    public function __construct(protected string $contactId) {}

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return "/crm/v4/objects/contacts/{$this->contactId}/associations/companies";
    }
}
