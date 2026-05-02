<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Catalogue;

use InvalidArgumentException;
use Marcosgodoy\AcmeWidgets\Product;

final readonly class ProductCatalogue
{
    /** @var array<string, Product> */
    private array $products;

    public function __construct(Product ...$products)
    {
        if ($products === []) {
            throw new InvalidArgumentException('Catalogue cannot be empty.');
        }

        $indexed = [];
        foreach ($products as $product) {
            if (isset($indexed[$product->code])) {
                throw new InvalidArgumentException(
                    "Duplicate product code: {$product->code}",
                );
            }
            $indexed[$product->code] = $product;
        }

        $this->products = $indexed;
    }

    #[\NoDiscard]
    public function find(string $code): Product
    {
        return $this->products[$code]
            ?? throw UnknownProductException::forCode($code);
    }

    /**
     * @return list<string>
     */
    public function codes(): array
    {
        return array_keys($this->products);
    }
}
