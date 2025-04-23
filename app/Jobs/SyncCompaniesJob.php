<?php

namespace App\Jobs;

use App\Events\ToastTrigger;
use App\Http\Integrations\Hubspot\HubspotConnector;
use App\Http\Integrations\Hubspot\Objects\Companies\Requests\Create;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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
        $request->headers()->add('Authorization', 'Bearer ' . config('services.hubspot.api_key'));
        $confection = new HubspotConnector;
        $promise = $confection->sendAsync($request);

        $promise
            ->then(function (HttpResponse $response) {
                // Handle Response
                cache()->put('Companies',  $response->json(),now()->addDay());

                ToastTrigger::dispatch(
                    'Job completed successfully!',
                    'success',
                    3000
                );

                logger()->debug('Response', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            })
            ->otherwise(function (RequestRequestException $exception) {
                // Handle Exception
                ToastTrigger::dispatch(
                    'Number of 10 companies sync unsuccess!',
                    'error',
                    3000
                );
                logger()->error('Exception', [
                    'status' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ]);
            });

        $promise->wait();
    }
    public function failed(\Throwable $exception)
    {
        logger()->error('Company sync failed after retries', [
            'error' => $exception->getMessage(),
            'companies' => $this->companies
        ]);

        // You could also dispatch a notification here
    }
}
