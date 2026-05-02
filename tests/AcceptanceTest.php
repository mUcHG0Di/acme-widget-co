<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests;

use Marcosgodoy\AcmeWidgets\Basket;
use Marcosgodoy\AcmeWidgets\Catalogue\ProductCatalogue;
use Marcosgodoy\AcmeWidgets\Delivery\DeliveryTier;
use Marcosgodoy\AcmeWidgets\Delivery\TieredDeliveryCalculator;
use Marcosgodoy\AcmeWidgets\Money;
use Marcosgodoy\AcmeWidgets\Offer\BuyOneGetSecondHalfPrice;
use Marcosgodoy\AcmeWidgets\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end acceptance suite.
 */
final class AcceptanceTest extends TestCase
{
    /**
     * The four basket examples from the spec, with their published totals.
     *
     * @return array<string, array{list<string>, int}>
     */
    public static function specExamples(): array
    {
        return [
            'B01, G01 -> $37.85'                     => [['B01', 'G01'], 3785],
            'R01, R01 -> $54.37'                     => [['R01', 'R01'], 5437],
            'R01, G01 -> $60.85'                     => [['R01', 'G01'], 6085],
            'B01, B01, R01, R01, R01 -> $98.27'      => [['B01', 'B01', 'R01', 'R01', 'R01'], 9827],
        ];
    }

    /**
     * @param list<string> $codesToAdd
     */
    #[Test]
    #[DataProvider('specExamples')]
    public function it_matches_the_published_totals_from_the_spec(array $codesToAdd, int $expectedTotalCents): void
    {
        $basket = $this->makeBasket();

        foreach ($codesToAdd as $code) {
            $basket->add($code);
        }

        self::assertSame(
            $expectedTotalCents,
            $basket->total()->cents,
            'Spec basket [' . implode(', ', $codesToAdd) . '] should total ' . Money::fromCents($expectedTotalCents)->format()
                . ', got ' . $basket->total()->format(),
        );
    }

    /**
     * Boundary and edge cases the spec doesn't show explicitly.
     *
     * @return array<string, array{list<string>, int}>
     */
    public static function boundaryCases(): array
    {
        return [
            'empty basket totals zero'
                => [[], 0],

            'single R01 with no pair gets first-tier delivery'
                => [['R01'], 3790],

            'single G01 falls in first delivery tier'
                => [['G01'], 2990],

            'single B01 falls in first delivery tier'
                => [['B01'], 1290],

            'three R01s yield one discounted pair'
                => [['R01', 'R01', 'R01'], 8532],

            'four R01s yield two discounted pairs and free delivery'
                => [['R01', 'R01', 'R01', 'R01'], 9884],

            'mixed basket with one of each non-paired R01'
                => [['R01', 'G01', 'B01'], 6880],

            'subtotal exactly at second tier boundary'
                => [['G01', 'G01', 'B01'], 6080],

            'subtotal in free delivery tier'
                => [['G01', 'G01', 'G01', 'G01'], 9980],
        ];
    }

    /**
     * @param list<string> $codesToAdd
     */
    #[Test]
    #[DataProvider('boundaryCases')]
    public function it_handles_boundary_cases_correctly(array $codesToAdd, int $expectedTotalCents): void
    {
        $basket = $this->makeBasket();

        foreach ($codesToAdd as $code) {
            $basket->add($code);
        }

        self::assertSame($expectedTotalCents, $basket->total()->cents);
    }

    private function makeBasket(): Basket
    {
        return new Basket(
            catalogue: new ProductCatalogue(
                new Product('R01', 'Red Widget', Money::fromCents(3295)),
                new Product('G01', 'Green Widget', Money::fromCents(2495)),
                new Product('B01', 'Blue Widget', Money::fromCents(795)),
            ),
            deliveryCalculator: new TieredDeliveryCalculator(
                new DeliveryTier(Money::zero(), Money::fromCents(495)),
                new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
                new DeliveryTier(Money::fromCents(9000), Money::zero()),
            ),
            offers: [new BuyOneGetSecondHalfPrice('R01')],
        );
    }
}
