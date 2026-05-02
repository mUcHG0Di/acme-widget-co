<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Offer;

use Marcosgodoy\AcmeWidgets\Money;
use Marcosgodoy\AcmeWidgets\Product;

interface Offer
{
    /**
     * Compute the total discount this offer applies to the given items.
     *
     * @param list<Product> $items
     */
    public function discountFor(array $items): Money;
}
