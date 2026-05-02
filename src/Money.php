<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(public readonly int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException(
                "Money cannot be negative; got {$cents} cents.",
            );
        }
    }

    #[\NoDiscard]
    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    #[\NoDiscard]
    public static function fromDollars(float $dollars): self
    {
        // Round at the boundary so callers don't have to think about
        // float artifacts when constructing from human-readable prices.
        return new self((int) round($dollars * 100));
    }

    #[\NoDiscard]
    public static function zero(): self
    {
        return new self(0);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    #[\NoDiscard]
    public function plus(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    #[\NoDiscard]
    public function minus(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    #[\NoDiscard]
    public function times(int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException(
                "Multiplier cannot be negative; got {$multiplier}.",
            );
        }

        return new self($this->cents * $multiplier);
    }

    /**
     * Divides this amount by an integer divisor, using half-up rounding.
     *
     * Implementation stays in integer space throughout (no float
     * intermediate) by computing the quotient with intdiv and rounding
     * up when twice the remainder reaches or exceeds the divisor.
     */
    #[\NoDiscard]
    public function dividedBy(int $divisor): self
    {
        if ($divisor <= 0) {
            throw new InvalidArgumentException(
                "Divisor must be positive; got {$divisor}.",
            );
        }

        $quotient = intdiv($this->cents, $divisor);
        $remainder = $this->cents % $divisor;

        // Half-up: if remainder * 2 >= divisor, round up.
        if ($remainder * 2 >= $divisor) {
            ++$quotient;
        }

        return new self($quotient);
    }

    public function format(): string
    {
        return \sprintf('$%d.%02d', intdiv($this->cents, 100), $this->cents % 100);
    }
}
