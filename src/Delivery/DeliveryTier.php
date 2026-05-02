<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Delivery;

use Marcosgodoy\AcmeWidgets\Money;

final readonly class DeliveryTier
{
    public function __construct(
        public Money $minimumSubtotal,
        public Money $charge,
    ) {
    }

    public function applies(Money $subtotal): bool
    {
        return $subtotal->cents >= $this->minimumSubtotal->cents;
    }
}
