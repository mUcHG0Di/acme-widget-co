<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Offer;

use Marcosgodoy\AcmeWidgets\Money;
use Marcosgodoy\AcmeWidgets\Product;

final readonly class BuyOneGetSecondHalfPrice implements Offer
{
    public function __construct(public string $targetCode)
    {
    }

    public function discountFor(array $items): Money
    {
        $matches = array_values(
            array_filter($items, fn (Product $item): bool => $item->code === $this->targetCode),
        );

        $pairs = intdiv(\count($matches), 2);

        if ($pairs === 0) {
            return Money::zero();
        }

        $perPairDiscount = $matches[0]->price->dividedBy(2);

        return $perPairDiscount->times($pairs);
    }
}
