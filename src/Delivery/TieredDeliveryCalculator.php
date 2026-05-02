<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Delivery;

use InvalidArgumentException;
use LogicException;
use Marcosgodoy\AcmeWidgets\Money;

final readonly class TieredDeliveryCalculator implements DeliveryCalculator
{
    /** @var list<DeliveryTier> */
    private array $tiers;

    public function __construct(DeliveryTier ...$tiers)
    {
        if ($tiers === []) {
            throw new InvalidArgumentException(
                'At least one delivery tier is required.',
            );
        }

        $hasZeroStart = false;
        foreach ($tiers as $tier) {
            if ($tier->minimumSubtotal->cents === 0) {
                $hasZeroStart = true;
                break;
            }
        }

        if (! $hasZeroStart) {
            throw new InvalidArgumentException(
                'A tier starting at zero is required so every subtotal resolves to a charge.',
            );
        }

        usort(
            $tiers,
            static fn (DeliveryTier $a, DeliveryTier $b): int =>
                $b->minimumSubtotal->cents <=> $a->minimumSubtotal->cents,
        );


        // usort() reindexes from 0 already, but PHPStan's list<T> type requires sequential integer keys
        // @phpstan-ignore-next-line
        $this->tiers = array_values($tiers);
    }

    public function calculate(Money $subtotal): Money
    {
        foreach ($this->tiers as $tier) {
            if ($tier->applies($subtotal)) {
                return $tier->charge;
            }
        }

        throw new LogicException(
            'No delivery tier matched; construction invariants were violated.',
        );
    }
}
