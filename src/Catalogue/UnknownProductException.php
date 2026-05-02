<?php

declare(strict_types=1);

namespace Marcosgodoy\AcmeWidgets\Catalogue;

use RuntimeException;

final class UnknownProductException extends RuntimeException
{
    public static function forCode(string $code): self
    {
        return new self("Unknown product code: {$code}");
    }
}
