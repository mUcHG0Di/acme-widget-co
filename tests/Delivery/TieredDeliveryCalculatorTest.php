<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests\Delivery;

use InvalidArgumentException;
use Marcosgodoy\AcmeWidgets\Delivery\DeliveryTier;
use Marcosgodoy\AcmeWidgets\Delivery\TieredDeliveryCalculator;
use Marcosgodoy\AcmeWidgets\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TieredDeliveryCalculator::class)]
final class TieredDeliveryCalculatorTest extends TestCase
{
    #[Test]
    public function it_rejects_an_empty_tier_list(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one delivery tier is required');

        new TieredDeliveryCalculator();
    }

    #[Test]
    public function it_requires_a_tier_starting_at_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A tier starting at zero is required');

        new TieredDeliveryCalculator(
            new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
        );
    }

    /**
     * The four tier-boundary cases for Acme's published rules:
     * - subtotal < $50            -> $4.95
     * - $50 <= subtotal < $90     -> $2.95
     * - subtotal >= $90           -> free
     *
     * @return array<string, array{int, int}>
     */
    public static function acmeBoundaryCases(): array
    {
        return [
            'empty subtotal falls in first tier'          => [0, 495],
            'just below first boundary'                    => [4999, 495],
            'exactly at first boundary'                    => [5000, 295],
            'just above first boundary'                    => [5001, 295],
            'just below second boundary'                   => [8999, 295],
            'exactly at second boundary qualifies for free' => [9000, 0],
            'just above second boundary'                   => [9001, 0],
            'far above second boundary'                    => [50000, 0],
        ];
    }

    #[Test]
    #[DataProvider('acmeBoundaryCases')]
    public function it_applies_acme_rules_at_every_boundary(int $subtotalCents, int $expectedChargeCents): void
    {
        $calculator = $this->acmeCalculator();

        $charge = $calculator->calculate(Money::fromCents($subtotalCents));

        self::assertSame($expectedChargeCents, $charge->cents);
    }

    #[Test]
    public function tiers_can_be_passed_in_any_order(): void
    {
        $reverseOrder = new TieredDeliveryCalculator(
            new DeliveryTier(Money::fromCents(9000), Money::zero()),
            new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
            new DeliveryTier(Money::zero(), Money::fromCents(495)),
        );

        $charge = $reverseOrder->calculate(Money::fromCents(8999));

        self::assertSame(295, $charge->cents);
    }

    private function acmeCalculator(): TieredDeliveryCalculator
    {
        return new TieredDeliveryCalculator(
            new DeliveryTier(Money::zero(), Money::fromCents(495)),
            new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
            new DeliveryTier(Money::fromCents(9000), Money::zero()),
        );
    }
}
