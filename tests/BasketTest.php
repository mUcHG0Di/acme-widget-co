<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests;

use Marcosgodoy\AcmeWidgets\Basket;
use Marcosgodoy\AcmeWidgets\Catalogue\ProductCatalogue;
use Marcosgodoy\AcmeWidgets\Catalogue\UnknownProductException;
use Marcosgodoy\AcmeWidgets\Delivery\DeliveryTier;
use Marcosgodoy\AcmeWidgets\Delivery\TieredDeliveryCalculator;
use Marcosgodoy\AcmeWidgets\Money;
use Marcosgodoy\AcmeWidgets\Offer\BuyOneGetSecondHalfPrice;
use Marcosgodoy\AcmeWidgets\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Basket::class)]
final class BasketTest extends TestCase
{
    #[Test]
    public function an_empty_basket_totals_zero(): void
    {
        $basket = $this->makeBasket();

        self::assertTrue($basket->total()->equals(Money::zero()));
    }

    #[Test]
    public function an_empty_basket_does_not_charge_delivery(): void
    {
        $basket = $this->makeBasket();

        self::assertTrue($basket->total()->equals(Money::zero()));
        self::assertSame(0, $basket->count());
    }

    #[Test]
    public function it_starts_with_zero_items(): void
    {
        $basket = $this->makeBasket();

        self::assertSame(0, $basket->count());
    }

    #[Test]
    public function adding_an_item_increases_the_count(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');

        self::assertSame(1, $basket->count());
    }

    #[Test]
    public function the_same_product_can_be_added_multiple_times(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');
        $basket->add('R01');
        $basket->add('R01');

        self::assertSame(3, $basket->count());
    }

    #[Test]
    public function adding_an_unknown_product_propagates_the_exception(): void
    {
        $basket = $this->makeBasket();

        $this->expectException(UnknownProductException::class);
        $this->expectExceptionMessage('Unknown product code: X99');

        $basket->add('X99');
    }

    #[Test]
    public function the_total_of_a_single_item_includes_delivery(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');

        self::assertTrue($basket->total()->equals(Money::fromCents(3790)));
    }

    #[Test]
    public function the_total_sums_line_items_and_applies_delivery(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');
        $basket->add('G01');
        $basket->add('B01');

        self::assertTrue($basket->total()->equals(Money::fromCents(6880)));
    }

    #[Test]
    public function repeated_items_contribute_their_price_each_time_with_delivery(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');
        $basket->add('R01');

        self::assertTrue($basket->total()->equals(Money::fromCents(6885)));
    }

    #[Test]
    public function offers_reduce_the_total(): void
    {
        $basket = new Basket(
            catalogue: $this->makeCatalogue(),
            deliveryCalculator: new TieredDeliveryCalculator(
                new DeliveryTier(Money::zero(), Money::fromCents(495)),
                new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
                new DeliveryTier(Money::fromCents(9000), Money::zero()),
            ),
            offers: [new BuyOneGetSecondHalfPrice('R01')],
        );

        $basket->add('R01');
        $basket->add('R01');

        self::assertTrue($basket->total()->equals(Money::fromCents(5437)));
    }

    #[Test]
    public function delivery_is_computed_on_the_post_discount_subtotal(): void
    {
        $basket = new Basket(
            catalogue: $this->makeCatalogue(),
            deliveryCalculator: new TieredDeliveryCalculator(
                new DeliveryTier(Money::zero(), Money::fromCents(495)),
                new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
                new DeliveryTier(Money::fromCents(9000), Money::zero()),
            ),
            offers: [new BuyOneGetSecondHalfPrice('R01')],
        );

        $basket->add('R01');
        $basket->add('R01');

        self::assertTrue($basket->total()->equals(Money::fromCents(5437)));
    }

    private function makeBasket(): Basket
    {
        return new Basket(
            catalogue: $this->makeCatalogue(),
            deliveryCalculator: new TieredDeliveryCalculator(
                new DeliveryTier(Money::zero(), Money::fromCents(495)),
                new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
                new DeliveryTier(Money::fromCents(9000), Money::zero()),
            ),
            offers: [],
        );
    }

    private function makeCatalogue(): ProductCatalogue
    {
        return new ProductCatalogue(
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
        );
    }
}
