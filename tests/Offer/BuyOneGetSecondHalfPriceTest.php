<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests\Offer;

use Marcosgodoy\AcmeWidgets\Money;
use Marcosgodoy\AcmeWidgets\Offer\BuyOneGetSecondHalfPrice;
use Marcosgodoy\AcmeWidgets\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BuyOneGetSecondHalfPrice::class)]
final class BuyOneGetSecondHalfPriceTest extends TestCase
{
    #[Test]
    public function no_discount_when_no_items_match_the_target_code(): void
    {
        $offer = new BuyOneGetSecondHalfPrice('R01');

        $discount = $offer->discountFor([
            $this->greenWidget(),
            $this->blueWidget(),
        ]);

        self::assertSame(0, $discount->cents);
    }

    #[Test]
    public function no_discount_when_only_one_target_item_is_present(): void
    {
        $offer = new BuyOneGetSecondHalfPrice('R01');

        $discount = $offer->discountFor([
            $this->redWidget(),
            $this->greenWidget(),
        ]);

        self::assertSame(0, $discount->cents);
    }

    #[Test]
    public function discounts_one_item_when_two_target_items_are_present(): void
    {
        $offer = new BuyOneGetSecondHalfPrice('R01');

        $discount = $offer->discountFor([
            $this->redWidget(),
            $this->redWidget(),
        ]);

        self::assertSame(1648, $discount->cents);
    }

    /**
     * The pairing semantics: floor(count / 2) discounts at half the
     * target's price each. Odd counts leave one full-price item.
     *
     * @return array<string, array{int, int}>
     */
    public static function pairingCases(): array
    {
        return [
            'zero matches'    => [0, 0],
            'one match'       => [1, 0],
            'two matches'     => [2, 1648],
            'three matches'   => [3, 1648],
            'four matches'    => [4, 3296],
            'five matches'    => [5, 3296],
            'six matches'     => [6, 4944],
        ];
    }

    #[Test]
    #[DataProvider('pairingCases')]
    public function it_discounts_one_item_per_qualifying_pair(int $matchCount, int $expectedDiscountCents): void
    {
        $offer = new BuyOneGetSecondHalfPrice('R01');
        $items = array_fill(0, $matchCount, $this->redWidget());

        $discount = $offer->discountFor($items);

        self::assertSame($expectedDiscountCents, $discount->cents);
    }

    #[Test]
    public function non_target_items_do_not_affect_pairing(): void
    {
        $offer = new BuyOneGetSecondHalfPrice('R01');

        $discount = $offer->discountFor([
            $this->redWidget(),
            $this->greenWidget(),
            $this->redWidget(),
            $this->blueWidget(),
            $this->greenWidget(),
        ]);

        self::assertSame(1648, $discount->cents);
    }

    #[Test]
    public function the_offer_can_target_any_product_code(): void
    {
        $offer = new BuyOneGetSecondHalfPrice('G01');

        $discount = $offer->discountFor([
            $this->greenWidget(),
            $this->greenWidget(),
        ]);

        self::assertSame(1248, $discount->cents);
    }

    private function redWidget(): Product
    {
        return new Product('R01', 'Red Widget', Money::fromCents(3295));
    }

    private function greenWidget(): Product
    {
        return new Product('G01', 'Green Widget', Money::fromCents(2495));
    }

    private function blueWidget(): Product
    {
        return new Product('B01', 'Blue Widget', Money::fromCents(795));
    }
}
