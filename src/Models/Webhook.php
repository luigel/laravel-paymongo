<?php

namespace Luigel\LaravelPaymongo\Models;

use Luigel\LaravelPaymongo\Paymongo;

class Webhook 
{
    public const SOURCE_CHARGEABLE = 'source.chargeable';

    public $id;
    public $type;
    public $secret_key;
    public $status;
    public $url;
    public $events;
    public $updated;
    public $created;

    public function setData($data)
    {
        if (is_array($data) && isset($data['id']))
        {
            return $this->convertToObject($data);
        }
        $webhooks = collect();

        foreach ($data as $item)
        {
            $webhooks->push($this->convertToObject($item));
        }
        return $webhooks;
    }

    protected function convertToObject($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->secret_key = $data['attributes']['secret_key'];
        $this->status = $data['attributes']['status'];
        $this->url = $data['attributes']['url'];
        $this->updated = $data['attributes']['updated'];
        $this->created = $data['attributes']['created'];

        $events = collect();
        foreach ($data['attributes']['events'] as $event)
        {
            $events->push($event);
        }

        $this->events = $events;

        return $this;
    }

    public function enable()
    {
        return (new Paymongo)->webhook()->enable($this);
    }

    public function disable()
    {
        return (new Paymongo)->webhook()->disable($this);
    }

    public function update($payload)
    {
        return (new Paymongo)->webhook()->update($this, $payload);
    }
}