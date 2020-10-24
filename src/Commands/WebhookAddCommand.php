<?php

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\Webhook;

class WebhookAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymongo:webhook';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Add webhook to Paymongo.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->ask('Enter your webhook URL.');

        $validator = Validator::make(
            ['url' => $url],
            ['url' => ['url']]
        );

        if ($validator->fails()) {
            $this->info('Webhook was not created. See error messages below:');

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return 1;
        }

        $webhook = $this->createWebhook($url);
        $headers = ['id', 'livemode', 'secret_key', 'status', 'url'];
        $webhook = collect($webhook->getData())->only($headers)->toArray();
        $webhook['livemode'] = $webhook['livemode'] ? 'YES' : 'NO';

        $this->table($headers, [$webhook]);
    }

    protected function createWebhook($url)
    {
        $this->comment('Creating webhook to Paymongo.');

        $webhook = Paymongo::webhook()->create([
            'url' => $url,
            'events' => [
                Webhook::SOURCE_CHARGEABLE,
            ],
        ]);

        $this->line('Created webhook with an ID '.$webhook->id);

        return $webhook;
    }
}
