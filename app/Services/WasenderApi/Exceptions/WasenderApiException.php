<?php

namespace App\Services\WasenderApi\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class WasenderApiException extends Exception
{
    protected $response;

    public function __construct($message = "", $code = 0, ?Response $response = null)
    {
        parent::__construct($message, $code);
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
