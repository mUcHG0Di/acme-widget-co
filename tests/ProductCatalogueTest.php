<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests;

use InvalidArgumentException;
use Marcosgodoy\AcmeWidgets\Catalogue\ProductCatalogue;
use Marcosgodoy\AcmeWidgets\Catalogue\UnknownProductException;
use Marcosgodoy\AcmeWidgets\Money;
use Marcosgodoy\AcmeWidgets\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductCatalogue::class)]
#[CoversClass(UnknownProductException::class)]
final class ProductCatalogueTest extends TestCase
{
    #[Test]
    public function it_resolves_a_known_product_by_code(): void
    {
        $red = new Product('R01', 'Red Widget', Money::fromCents(3295));
        $green = new Product('G01', 'Green Widget', Money::fromCents(2495));
        $catalogue = new ProductCatalogue($red, $green);

        $resolved = $catalogue->find('R01');

        self::assertSame($red, $resolved);
    }

    #[Test]
    public function it_throws_a_typed_exception_for_an_unknown_product(): void
    {
        $catalogue = new ProductCatalogue(
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
        );

        $this->expectException(UnknownProductException::class);
        $this->expectExceptionMessage('Unknown product code: X99');

        (void) $catalogue->find('X99');
    }

    #[Test]
    public function it_rejects_an_empty_catalogue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Catalogue cannot be empty');

        new ProductCatalogue();
    }

    #[Test]
    public function it_rejects_duplicate_product_codes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate product code: R01');

        new ProductCatalogue(
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Reddish Widget', Money::fromCents(3300)),
        );
    }

    #[Test]
    public function it_lists_all_known_codes(): void
    {
        $catalogue = new ProductCatalogue(
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
        );

        self::assertSame(['R01', 'G01', 'B01'], $catalogue->codes());
    }
}
