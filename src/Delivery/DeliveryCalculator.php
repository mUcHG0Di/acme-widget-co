<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Delivery;

use Marcosgodoy\AcmeWidgets\Money;

interface DeliveryCalculator
{
    public function calculate(Money $subtotal): Money;
}
