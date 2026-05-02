<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets;

use Marcosgodoy\AcmeWidgets\Catalogue\ProductCatalogue;
use Marcosgodoy\AcmeWidgets\Delivery\DeliveryCalculator;

final class Basket
{
    /** @var list<Product> */
    private array $items = [];

    /**
     * @param list<object> $offers Reserved for a later commit; currently unused.
     */
    public function __construct(
        private readonly ProductCatalogue $catalogue,
        private readonly DeliveryCalculator $deliveryCalculator,
        private readonly array $offers = [],
    ) {
    }

    public function add(string $productCode): void
    {
        $this->items[] = $this->catalogue->find($productCode);
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function total(): Money
    {
        $subtotal = $this->subtotal();
        $delivery = $this->deliveryCalculator->calculate($subtotal);

        return $subtotal->plus($delivery);
    }

    private function subtotal(): Money
    {
        $running = Money::zero();
        foreach ($this->items as $item) {
            $running = $running->plus($item->price);
        }

        return $running;
    }
}
