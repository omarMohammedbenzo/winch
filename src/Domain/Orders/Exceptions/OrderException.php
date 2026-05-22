<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Exceptions;

use RuntimeException;


abstract class OrderException extends RuntimeException
{
    // Error code for API 
    abstract public function errorCode(): string;

    // Translation key 
    abstract public function translationKey(): string;

    /**
     * Placeholder replacements for the translation, resolved for the locale.
     *
     * @return array<string, string|int>
     */
    abstract protected function replacements(?string $locale): array;

    public function translate(?string $locale = null): string
    {
        return (string) __($this->translationKey(), $this->replacements($locale), $locale);
    }
}
