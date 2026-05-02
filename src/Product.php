<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets;

use InvalidArgumentException;

final readonly class Product
{
    public string $code;

    public string $name;

    public function __construct(string $code, string $name, public Money $price)
    {
        $code = trim($code);
        $name = trim($name);

        if ($code === '') {
            throw new InvalidArgumentException('Product code cannot be empty.');
        }

        if ($name === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        $this->code = $code;
        $this->name = $name;
    }
}
