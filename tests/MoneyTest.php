<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Tests;

use InvalidArgumentException;
use Marcosgodoy\AcmeWidgets\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Money::class)]
final class MoneyTest extends TestCase
{
    #[Test]
    public function it_is_constructed_from_cents(): void
    {
        $money = Money::fromCents(3295);

        self::assertSame(3295, $money->cents);
    }

    #[Test]
    public function it_is_constructed_from_dollars(): void
    {
        $money = Money::fromDollars(32.95);

        self::assertSame(3295, $money->cents);
    }

    #[Test]
    public function it_rejects_negative_cents(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Money cannot be negative');

        (void) Money::fromCents(-1);
    }

    #[Test]
    public function it_treats_zero_as_a_valid_amount(): void
    {
        $money = Money::fromCents(0);

        self::assertSame(0, $money->cents);
    }

    #[Test]
    public function two_money_instances_with_the_same_value_are_equal(): void
    {
        $a = Money::fromCents(3295);
        $b = Money::fromCents(3295);

        self::assertTrue($a->equals($b));
    }

    #[Test]
    public function two_money_instances_with_different_values_are_not_equal(): void
    {
        $a = Money::fromCents(3295);
        $b = Money::fromCents(2495);

        self::assertFalse($a->equals($b));
    }

    #[Test]
    public function it_adds_two_amounts(): void
    {
        $sum = Money::fromCents(3295)->plus(Money::fromCents(2495));

        self::assertSame(5790, $sum->cents);
    }

    #[Test]
    public function it_subtracts_two_amounts(): void
    {
        $difference = Money::fromCents(5000)->minus(Money::fromCents(1500));

        self::assertSame(3500, $difference->cents);
    }

    #[Test]
    public function it_rejects_subtraction_that_would_produce_a_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (void) Money::fromCents(100)->minus(Money::fromCents(200));
    }

    #[Test]
    public function it_multiplies_by_a_non_negative_integer(): void
    {
        $product = Money::fromCents(3295)->times(3);

        self::assertSame(9885, $product->cents);
    }

    #[Test]
    public function multiplying_by_zero_yields_zero(): void
    {
        $product = Money::fromCents(3295)->times(0);

        self::assertSame(0, $product->cents);
    }

    #[Test]
    public function it_rejects_multiplication_by_a_negative_integer(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (void) Money::fromCents(3295)->times(-1);
    }

    /**
     * The half-up rounding behavior is the core reason this class exists.
     * The spec's expected R01,R01 total of $54.37 only holds if half of
     * $32.95 rounds up to $16.48 (not down to $16.47).
     *
     * @return array<string, array{int, int, int}>
     */
    public static function divisionCases(): array
    {
        return [
            'exact division' => [3000, 2, 1500],
            'half-up rounds .5 up' => [3295, 2, 1648],
            'rounds below half down' => [3294, 2, 1647],
            'rounds above half up' => [3296, 2, 1648],
            'divides by 1 unchanged' => [3295, 1, 3295],
            'zero divided is still zero' => [0, 5, 0],
            'rounds 1 cent split of 3 down' => [1, 3, 0],
            'rounds 2 cent split of 3 up' => [2, 3, 1],
        ];
    }

    #[Test]
    #[DataProvider('divisionCases')]
    public function it_divides_with_half_up_rounding(int $cents, int $divisor, int $expected): void
    {
        $result = Money::fromCents($cents)->dividedBy($divisor);

        self::assertSame($expected, $result->cents);
    }

    #[Test]
    public function it_rejects_division_by_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (void) Money::fromCents(100)->dividedBy(0);
    }

    #[Test]
    public function it_rejects_division_by_a_negative_integer(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (void) Money::fromCents(100)->dividedBy(-2);
    }

    #[Test]
    public function it_formats_whole_dollars(): void
    {
        self::assertSame('$50.00', Money::fromCents(5000)->format());
    }

    #[Test]
    public function it_formats_amounts_with_cents(): void
    {
        self::assertSame('$32.95', Money::fromCents(3295)->format());
    }

    #[Test]
    public function it_formats_zero(): void
    {
        self::assertSame('$0.00', Money::fromCents(0)->format());
    }

    #[Test]
    public function it_pads_single_digit_cents(): void
    {
        self::assertSame('$0.05', Money::fromCents(5)->format());
    }
}
