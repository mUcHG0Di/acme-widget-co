<?php

declare(strict_types=1);

namespace Acme\Widgets\Tests;

use InvalidArgumentException;
use Marcosgodoy\AcmeWidgets\Money;
use Marcosgodoy\AcmeWidgets\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Product::class)]
final class ProductTest extends TestCase
{
    #[Test]
    public function it_exposes_its_code_name_and_price(): void
    {
        $product = new Product('R01', 'Red Widget', Money::fromCents(3295));

        self::assertSame('R01', $product->code);
        self::assertSame('Red Widget', $product->name);
        self::assertTrue($product->price->equals(Money::fromCents(3295)));
    }

    #[Test]
    public function it_rejects_an_empty_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product code cannot be empty');

        new Product('', 'Red Widget', Money::fromCents(3295));
    }

    #[Test]
    public function it_rejects_a_blank_code(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Product('   ', 'Red Widget', Money::fromCents(3295));
    }

    #[Test]
    public function it_rejects_an_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');

        new Product('R01', '', Money::fromCents(3295));
    }
}
