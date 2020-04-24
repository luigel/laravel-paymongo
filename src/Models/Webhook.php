<?php

namespace Luigel\Paymongo\Models;

use Luigel\Paymongo\Paymongo;

class Webhook
{
    public const SOURCE_CHARGEABLE = 'source.chargeable';

    protected $id;
    protected $type;
    protected $secret_key;
    protected $status;
    protected $url;
    protected $events;
    protected $updated_at;
    protected $created_at;
    protected $data;

    public function setData($data)
    {
        if (is_array($data) && isset($data['id']))
        {
            return $this->setSingleData($data);
        }
        $webhooks = collect();

        foreach ($data as $item)
        {
            $webhooks->push($this->setSingleData($item));
        }
        return $webhooks;
    }

    protected function setSingleData($data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->secret_key = $data['attributes']['secret_key'];
        $this->status = $data['attributes']['status'];
        $this->url = $data['attributes']['url'];
        $this->updated_at = $data['attributes']['updated_at'];
        $this->created_at = $data['attributes']['created_at'];

        $events = collect();
        foreach ($data['attributes']['events'] as $event)
        {
            $events->push($event);
        }

        $this->events = $events;

        $this->data = [
            'id' => $this->id,
            'type' => $this->type,
            'secret_key' => $this->secret_key,
            'status' => $this->status,
            'url' => $this->url,
            'events' => $this->events,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSecretKey()
    {
        return $this->secret_key;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getEvents($i = null)
    {
        if (is_int($i) && $i !== null)
        {
            return $this->events[$i];
        }
        
        return $this->events;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
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
