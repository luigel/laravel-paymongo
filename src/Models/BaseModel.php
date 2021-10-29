<?php

namespace Luigel\Paymongo\Models;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Luigel\Paymongo\Exceptions\MethodNotFoundException;

class BaseModel
{
    protected array $attributes = [];

    /**
     * The method from the __call magic method.
     */
    protected string $method = '';

    /**
     * Set all the data to the attributes.
     */
    public function setData(array $data): Collection|self
    {
        if (isset($data['id'])) {
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
     */
    public function setSingleData(array $data): self
    {
        $model = new static();

        foreach ($data as $key => $item) {
            $model->setAttributes($key, $item);
        }

        return $model;
    }

    /**
     * Get all the attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Alias for getAttributes.
     */
    public function getData(): array
    {
        return $this->getAttributes();
    }

    /**
     * Set the attributes by key and value.
     */
    public function setAttributes(string $key, mixed $item): void
    {
        if (is_array($item)) {
            foreach ($item as $itemKey => $element) {
                $this->$itemKey = $element;
            }

            return;
        }
        $this->$key = $item;
    }

    public function __set(string $key, mixed $value): void
    {
        if (array_key_exists($key, $this->attributes)) {
            $this->attributes[$this->keyFormatFromClass($key)] = $value;
        } else {
            $this->attributes[$key] = $value;
        }
    }

    public function __get(string $key): mixed
    {
        // This will ensure the amount is converted to float.
        if ($amount = $this->ensureFloatAmount($key)) {
            return $amount;
        }

        return $this->attributes[$key];
    }

    /**
     * The magic function that guesses the attribute.
     */
    public function __call(string $name, mixed $arguments): mixed
    {
        return $this->guessAttributeFromMethodName($name);
    }

    /**
     * Guess the attribute from the method name.
     */
    protected function guessAttributeFromMethodName(string $method): mixed
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
     * @throws \Luigel\Paymongo\Exceptions\MethodNotFoundException
     */
    protected function getGuessedData(string $key, mixed $currentAttribute): mixed
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
     * @throws \Luigel\Paymongo\Exceptions\MethodNotFoundException
     */
    protected function throwMethodNotFoundException(): void
    {
        throw new MethodNotFoundException("Method [{$this->method}] not found in ".get_class($this));
    }

    /**
     * Get the class name in snake format.
     */
    protected function keyFormatFromClass(string $key): string
    {
        return Str::snake($this->getModel()).'_'.$key;
    }

    /**
     * Get the model.
     */
    protected function getModel(): string
    {
        return Str::afterLast(get_class($this), '\\');
    }

    public function ensureFloatAmount(string $key): null|float
    {
        if ($key === 'amount') {
            return floatval($this->attributes[$key] / 100);
        }

        return null;
    }
}
