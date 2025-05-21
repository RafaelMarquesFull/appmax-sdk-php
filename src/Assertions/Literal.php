<?php

namespace Appmax\Assertions;

use Appmax\Structures\AppmaxAPIError;

class Literal
{
    /**
     * Asserts that a value is a string
     * 
     * @param mixed $value The value to check
     * @param string|null $code Error code if assertion fails
     * @throws AppmaxAPIError If the value is not a string
     */
    public static function assertString($value, ?string $code = null): void
    {
        if (!is_string($value)) {
            $errorCode = $code ?? 'INVALID_STRING';
            throw new AppmaxAPIError($errorCode, "Expected string, received " . gettype($value));
        }
    }
}
