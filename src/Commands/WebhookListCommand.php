<?php

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Luigel\Paymongo\Traits\HasWebhooksTable;

class WebhookListCommand extends Command
{
    use HasWebhooksTable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymongo:list-webhooks';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'List all the webhooks from Paymongo.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->displayWebhooks();
    }
}
