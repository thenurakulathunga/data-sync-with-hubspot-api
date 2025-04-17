<?php

namespace App\Jobs;

use App\Http\Integrations\Hubspot\HubspotConnector;
use App\Http\Integrations\Hubspot\Objects\Companies\Requests\Create;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Saloon\Exceptions\Request\RequestException as RequestRequestException;
use Saloon\Http\Response as HttpResponse;

class SyncCompaniesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $companies;

    /**
     * Create a new job instance.
     */
    public function __construct(array $companies)
    {
        $this->companies = $companies;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $companies = $this->companies;

        $inputs = collect($companies)->map(function ($company) {
            $properties = [
                'name' => $company['name'],
                'domain' => $company['domain'],
                'phone' => $company['phone'],
            ];

            if (! empty($company['industry'])) {
                $properties['industry'] = $company['industry'];
            }

            return [
                // 'idProperty' => 'domain',
                // 'id' => $company['domain'],
                // 'objectWriteTraceId' => Str::uuid(), // Optional
                'properties' => $properties,
            ];
        })->toArray();

        $request = [
            'inputs' => $inputs,
        ];

        $request = new Create($request);
        $request->headers()->add('Authorization', 'Bearer '.config('services.hubspot.api_key'));
        $confection = new HubspotConnector;
        $promise = $confection->sendAsync($request);

        $promise
            ->then(function (HttpResponse $response) {
                // Handle Response
                cache()->remember('Companies', now()->addDay(), function () use ($response) {
                    return $response->json();
                });
                $this->dispatch('showToast', [
                    'type' => 'success',
                    'message' => 'Number of 10 companies sync successfully!',
                ]);
                info('Response', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            })
            ->otherwise(function (RequestRequestException $exception) {
                // Handle Exception
                $this->dispatch('showToast', [
                    'type' => 'success',
                    'message' => 'Number of 10 companies sync unsuccess!',
                ]);
                info('Exception', [
                    'status' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ]);
            });

        $promise->wait();
    }
}
