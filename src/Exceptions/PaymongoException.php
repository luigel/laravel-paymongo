<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Exceptions;

use Luigel\Paymongo\Data\ApiError;
use RuntimeException;
use Throwable;

abstract class PaymongoException extends RuntimeException
{
    /**
     * @param list<ApiError> $errors
     */
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly array $errors = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * The parsed PayMongo `errors[]` entries, when the API returned any.
     *
     * @return list<ApiError>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?ApiError
    {
        return $this->errors[0] ?? null;
    }
}
