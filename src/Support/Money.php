<?php

declare(strict_types=1);

namespace Luigel\Paymongo\Support;

use JsonSerializable;
use Stringable;

/**
 * An immutable PHP-peso amount expressed in integer centavos, as PayMongo expects.
 */
final readonly class Money implements JsonSerializable, Stringable
{
    private function __construct(
        private int $centavos,
    ) {}

    public static function ofCentavos(int $centavos): self
    {
        return new self($centavos);
    }

    public function centavos(): int
    {
        return $this->centavos;
    }

    /**
     * Exact decimal representation, e.g. 150050 => "1500.50". No float math.
     */
    public function toDecimal(): string
    {
        return sprintf(
            '%s%d.%02d',
            $this->centavos < 0 ? '-' : '',
            abs(intdiv($this->centavos, 100)),
            abs($this->centavos % 100),
        );
    }

    /**
     * Display representation, e.g. 150050 => "₱1,500.50".
     */
    public function format(string $symbol = '₱'): string
    {
        $units = (string) abs(intdiv($this->centavos, 100));
        $grouped = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $units) ?? $units;

        return sprintf(
            '%s%s%s.%02d',
            $this->centavos < 0 ? '-' : '',
            $symbol,
            $grouped,
            abs($this->centavos % 100),
        );
    }

    public function add(self $other): self
    {
        return new self($this->centavos + $other->centavos);
    }

    public function subtract(self $other): self
    {
        return new self($this->centavos - $other->centavos);
    }

    public function equals(self $other): bool
    {
        return $this->centavos === $other->centavos;
    }

    public function jsonSerialize(): int
    {
        return $this->centavos;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
