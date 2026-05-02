<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests\Delivery;

use Marcosgodoy\AcmeWidgets\Delivery\DeliveryTier;
use Marcosgodoy\AcmeWidgets\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeliveryTier::class)]
final class DeliveryTierTest extends TestCase
{
    #[Test]
    public function it_exposes_its_minimum_subtotal_and_charge(): void
    {
        $tier = new DeliveryTier(
            minimumSubtotal: Money::fromCents(5000),
            charge: Money::fromCents(295),
        );

        self::assertSame(5000, $tier->minimumSubtotal->cents);
        self::assertSame(295, $tier->charge->cents);
    }

    #[Test]
    public function it_applies_when_subtotal_meets_the_minimum(): void
    {
        $tier = new DeliveryTier(
            minimumSubtotal: Money::fromCents(5000),
            charge: Money::fromCents(295),
        );

        self::assertTrue($tier->applies(Money::fromCents(5000)));
        self::assertTrue($tier->applies(Money::fromCents(8999)));
    }

    #[Test]
    public function it_does_not_apply_below_the_minimum(): void
    {
        $tier = new DeliveryTier(
            minimumSubtotal: Money::fromCents(5000),
            charge: Money::fromCents(295),
        );

        self::assertFalse($tier->applies(Money::fromCents(4999)));
        self::assertFalse($tier->applies(Money::fromCents(0)));
    }
}
