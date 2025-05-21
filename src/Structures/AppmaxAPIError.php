<?php

namespace Appmax\Structures;

class AppmaxAPIError extends \Exception
{
    private $errorData;

    public function __construct(string $code, string $message, array $errorData = null)
    {
        parent::__construct($message, 0);
        $this->code = $code;
        $this->errorData = $errorData;
    }

    public function getErrorData()
    {
        return $this->errorData;
    }
}
