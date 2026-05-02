<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests;

use Marcosgodoy\AcmeWidgets\Basket;
use Marcosgodoy\AcmeWidgets\Catalogue\ProductCatalogue;
use Marcosgodoy\AcmeWidgets\Catalogue\UnknownProductException;
use Marcosgodoy\AcmeWidgets\Delivery\DeliveryCalculator;
use Marcosgodoy\AcmeWidgets\Money;
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
    public function the_total_of_a_single_item_is_its_price(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');

        self::assertTrue($basket->total()->equals(Money::fromCents(3295)));
    }

    #[Test]
    public function the_total_sums_the_prices_of_all_items(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');
        $basket->add('G01');
        $basket->add('B01');

        // 32.95 + 24.95 + 7.95 = 65.85
        self::assertTrue($basket->total()->equals(Money::fromCents(6585)));
    }

    #[Test]
    public function repeated_items_contribute_their_price_each_time(): void
    {
        $basket = $this->makeBasket();

        $basket->add('R01');
        $basket->add('R01');

        self::assertTrue($basket->total()->equals(Money::fromCents(6590)));
    }

    private function makeBasket(): Basket
    {
        return new Basket(
            catalogue: $this->makeCatalogue(),
            deliveryCalculator: new class () implements DeliveryCalculator {
                public function calculate(Money $subtotal): Money
                {
                    return Money::zero();
                }
            },
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
