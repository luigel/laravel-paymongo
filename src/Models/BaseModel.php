<?php

namespace Luigel\Paymongo\Models;

class BaseModel
{
    protected $attributes = [];

    public function setData($data)
    {
        if (is_array($data) && isset($data['id'])) {
            return $this->setSingleData($data);
        }

        $collection = collect();

        foreach ($data as $item) {
            $collection->push($this->setSingleData($item));
        }

        return $collection;
    }

    public function setSingleData($data)
    {
        foreach ($data as $key => $item) {
            $this->setAttributes($key, $item);
        }

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getData()
    {
        return $this->getAttributes();
    }

    public function setAttributes($key, $item)
    {
        if (is_array($item)) {
            foreach ($item as $itemKey => $element) {
                $this->$itemKey = $element;
            }
            return ;
        }
        $this->$key = $item;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __get($key)
    {
        // This will ensure the amount is converted to float.
        if ($key === 'amount') {
            return floatval($this->attributes[$key] / 100);
        }

        return $this->attributes[$key];
    }
}
