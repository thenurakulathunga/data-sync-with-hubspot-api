<?php

namespace App\Jobs;

use App\Events\ToastTrigger;
use App\Http\Integrations\Hubspot\HubspotConnector;
use App\Http\Integrations\Hubspot\Objects\Contacts\Requests\Create;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class SyncContactJob implements ShouldQueue
{
    use Queueable;

    public array $contacts;

    /**
     * Create a new job instance.
     */
    public function __construct(array $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $contacts = $this->contacts;

        $inputs = collect($contacts)->map(function ($contact) {
            $properties = [
                'firstname' => $contact['firstname'],
                'lastname' => $contact['lastname'],
                'email' => $contact['email'],
                'phone' => $contact['phone'],
            ];

            $request = [
                // 'iadProperty' => 'email',
                // 'id' => $contact['email'],
                // 'objectWriteTraceId' => Str::uuid(), // Optional
                'properties' => $properties,
            ];

            if (! empty($contact['associate_company'])) {
                $request['associations'] = [
                    [
                        'types' => [
                            [
                                'associationCategory' => 'HUBSPOT_DEFINED',
                                'associationTypeId' => 1,
                            ],
                        ],
                        'to' => [
                            'id' => $contact['associate_company'],
                        ],
                    ],
                ];
            }

            return $request;
        })->toArray();

        $request = [
            'inputs' => $inputs,
        ];

        // logger()->info('This is the request',$request);
        $request = new Create($request);
        $request->headers()->add('Authorization', 'Bearer '.config('services.hubspot.api_key'));
        $confection = new HubspotConnector;
        $promise = $confection->sendAsync($request);

        $promise
            ->then(function (Response $response) {
                // Handle Response
                ToastTrigger::dispatch(
                    'Number of 10 contacts sync successfully!',
                    'success',
                    3000
                );

                info('Response', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            })
            ->otherwise(function (RequestException $exception) {
                // Handle Exception

                ToastTrigger::dispatch(
                    'Number of 10 contacts sync unsuccess!',
                    'error',
                    3000
                );

                info('Exception', [
                    'status' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ]);
            });

        $promise->wait();
    }
}
