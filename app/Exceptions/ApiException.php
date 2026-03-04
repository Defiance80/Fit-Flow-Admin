<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $data;
    protected $code;

    public function __construct(string $message = "", $data = null, int $code = 400)
    {
        parent::__construct($message);
        $this->data = $data;
        $this->code = $code;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStatusCode()
    {
        return $this->code;
    }
}
