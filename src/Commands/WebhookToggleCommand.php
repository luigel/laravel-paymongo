<?php

namespace Luigel\Paymongo\Commands;

use Illuminate\Console\Command;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Traits\HasCommandValidation;
use Luigel\Paymongo\Traits\HasWebhooksTable;

class WebhookToggleCommand extends Command
{
    use HasCommandValidation;
    use HasWebhooksTable;

    public const WEBHOOK_ENABLE = 'enable';
    public const WEBHOOK_DISABLE = 'disable';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paymongo:toggle-webhook {webhookIds?*}
        {--enable : Enables the webhook.}
        {--disable : Disables the webhook.}
        {--a|all : Enable or disable all webhooks.}';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Toggles status of the webhook.';

    /**
     * The list of webhooks to toggle.
     *
     * @var array
     */
    protected $webhookIds;

    /**
     * The mode of the webhooks.
     *
     * @var string
     */
    protected $mode = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setUp();

        collect($this->webhookIds)
            ->each(function ($id) {
                $mode = $this->mode;
                $webhook = Paymongo::webhook()->find($id);

                if ($webhook->status !== $this->mode.'d') {
                    $webhook->$mode();
                    $this->line(ucfirst($this->mode)."d webhook {$id}.");
                } else {
                    $this->line("Webhook {$id} is already {$this->mode}d");
                }
            });
    }

    protected function setUp(): void
    {
        if ($this->option('enable')) {
            $this->mode = self::WEBHOOK_ENABLE;
        }

        if ($this->option('disable')) {
            $this->mode = self::WEBHOOK_DISABLE;
        }

        if ($this->hasArgument('webhookIds')) {
            $this->webhookIds = $this->argument('webhookIds');
        }

        if (empty($this->webhookIds)) {
            $this->displayWebhooks();

            $this->webhookIds = $this->ask('Enter the webhook id you want to enable or disable.');

            $validate = $this->validate(
                ['webhook_id' => $this->webhookIds],
                ['webhook_id' => ['required']],
                'See error messages below:'
            );

            if ($validate === 1) {
                return;
            }

            $this->webhookIds = explode(',', $this->webhookIds);
        }

        if ($this->mode === null) {
            $this->mode = $this->choice('Enable or disable?', [
                self::WEBHOOK_ENABLE,
                self::WEBHOOK_DISABLE,
            ], self::WEBHOOK_DISABLE);
        }
    }
}
