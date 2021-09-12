<?php

namespace Luigel\Paymongo\Models;

use Exception;
use Illuminate\Support\Str;
use Luigel\Paymongo\Exceptions\MethodNotFoundException;

class BaseModel
{
    /**
     * The list of attributes of the model.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The method from the __call magic method.
     *
     * @var string
     */
    protected $method = '';

    /**
     * Set all the data to the attributes.
     *
     * @param  array  $data
     * @return \Illuminate\Support\Collection
     */
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

    /**
     * Set the single data to the attributes.
     *
     * @param  array  $data
     * @return $this
     */
    public function setSingleData($data)
    {
        $model = new static();

        foreach ($data as $key => $item) {
            $model->setAttributes($key, $item);
        }

        return $model;
    }

    /**
     * Get all the attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Alias for getAttributes.
     *
     * @return array
     */
    public function getData()
    {
        return $this->getAttributes();
    }

    /**
     * Set the attributes by key and value.
     *
     * @param  string  $key
     * @param  mixed  $item
     * @return void
     */
    public function setAttributes($key, $item)
    {
        if (is_array($item)) {
            foreach ($item as $itemKey => $element) {
                $this->$itemKey = $element;
            }

            return;
        }
        $this->$key = $item;
    }

    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->attributes)) {
            $this->attributes[$this->keyFormatFromClass($key)] = $value;
        } else {
            $this->attributes[$key] = $value;
        }
    }

    public function __get($key)
    {
        // This will ensure the amount is converted to float.
        if ($amount = $this->ensureFloatAmount($key)) {
            return $amount;
        }

        return $this->attributes[$key];
    }

    /**
     * The magic function that guesses the attribute.
     *
     * @param  mixed  $name
     * @param  mixed  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->guessAttributeFromMethodName($name);
    }

    /**
     * Guess the attribute from the method name.
     *
     * @param  string  $method
     * @return mixed
     */
    protected function guessAttributeFromMethodName($method)
    {
        $this->method = $method;

        $key = Str::snake(Str::after($method, 'get'));

        if (array_key_exists($key, $this->attributes)) {
            if ($amount = $this->ensureFloatAmount($key)) {
                return $amount;
            }

            return $this->attributes[$key];
        }

        $keys = explode('_', $key);

        $currentAttribute = null;
        foreach ($keys as $key) {
            $currentAttribute = $this->getGuessedData($key, $currentAttribute);
        }

        return $currentAttribute;
    }

    /**
     * Get the guessed data.
     *
     * @param  string  $key
     * @param  mixed  $currentAttribute
     * @return mixed
     *
     * @throws \Luigel\Paymongo\Exceptions\MethodNotFoundException
     */
    protected function getGuessedData($key, $currentAttribute)
    {
        try {
            if ($currentAttribute === null && ! is_array($this->attributes[$key])) {
                if ($amount = $this->ensureFloatAmount($key)) {
                    return $amount;
                }

                return $this->attributes[$key];
            }
        } catch (Exception $e) {
            $this->throwMethodNotFoundException();
        }

        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if (array_key_exists($key, $currentAttribute)) {
            $currentAttribute = $currentAttribute[$key];
        } else {
            $this->throwMethodNotFoundException();
        }

        return $currentAttribute;
    }

    /**
     * Throws the method not found exception.
     *
     * @return void
     *
     * @throws \Luigel\Paymongo\Exceptions\MethodNotFoundException
     */
    protected function throwMethodNotFoundException()
    {
        throw new MethodNotFoundException("Method [{$this->method}] not found in ".get_class($this));
    }

    /**
     * Get the class name in snake format.
     *
     * @param  string  $key
     * @return string
     */
    protected function keyFormatFromClass($key)
    {
        return Str::snake($this->getModel()).'_'.$key;
    }

    /**
     * Get the model.
     *
     * @return string
     */
    protected function getModel()
    {
        return Str::afterLast(get_class($this), '\\');
    }

    public function ensureFloatAmount($key)
    {
        if ($key === 'amount') {
            return floatval($this->attributes[$key] / 100);
        }
    }
}
