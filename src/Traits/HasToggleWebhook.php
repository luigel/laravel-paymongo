<?php

namespace Luigel\Paymongo\Traits;

use Illuminate\Database\Eloquent\Model;
use Luigel\Paymongo\Models\Webhook;

trait HasToggleWebhook
{
    /**
     * Enables the webhook.
     *
     * @param  Webhook  $webhook
     * @return Model
     */
    public function enable(Webhook $webhook)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl.$webhook->getId().'/enable';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }

    /**
     * Disables the webhook.
     *
     * @param  Webhook  $webhook
     * @return Model
     */
    public function disable(Webhook $webhook)
    {
        $this->method = 'POST';
        $this->apiUrl = $this->apiUrl.$webhook->getId().'/disable';

        $this->setOptions([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [config('paymongo.secret_key'), ''],
        ]);

        return $this->request();
    }
}
