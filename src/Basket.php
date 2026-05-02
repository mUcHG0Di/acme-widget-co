<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets;

use Marcosgodoy\AcmeWidgets\Catalogue\ProductCatalogue;
use Marcosgodoy\AcmeWidgets\Delivery\DeliveryCalculator;
use Marcosgodoy\AcmeWidgets\Offer\Offer;

final class Basket
{
    /** @var list<Product> */
    private array $items = [];

    /**
     * @param list<Offer> $offers
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
        if ($this->items === []) {
            return Money::zero();
        }

        $discounted = $this->subtotal()->minus($this->totalDiscount());
        $delivery = $this->deliveryCalculator->calculate($discounted);

        return $discounted->plus($delivery);
    }

    private function subtotal(): Money
    {
        $running = Money::zero();
        foreach ($this->items as $item) {
            $running = $running->plus($item->price);
        }

        return $running;
    }

    private function totalDiscount(): Money
    {
        $running = Money::zero();
        foreach ($this->offers as $offer) {
            $running = $running->plus($offer->discountFor($this->items));
        }

        return $running;
    }
}
