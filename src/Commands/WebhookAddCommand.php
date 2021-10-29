<?php

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Models\BaseModel;
use Luigel\Paymongo\Models\Webhook;
use Luigel\Paymongo\Traits\HasCommandValidation;

class WebhookAddCommand extends Command
{
    use HasCommandValidation;

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

        $validate = $this->validate(
            ['url' => $url],
            ['url' => ['url', 'required']],
            'Webhook was not created. See error messages below:'
        );

        if ($validate === 1) {
            return 1;
        }

        $webhook = $this->createWebhook($url);
        $headers = ['id', 'livemode', 'secret_key', 'status', 'url'];
        $webhook = collect($webhook->getData())->only($headers)->toArray();
        $webhook['livemode'] = $webhook['livemode'] ? 'YES' : 'NO';

        $this->table($headers, [$webhook]);

        return 0;
    }

    protected function createWebhook(string $url): BaseModel
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
