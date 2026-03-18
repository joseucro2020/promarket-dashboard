<?php

namespace App\Services\WasenderApi\DTO;

class RetryConfig
{
    public bool $enabled;
    public int $maxRetries;

    public function __construct(bool $enabled = false, int $maxRetries = 0)
    {
        $this->enabled = $enabled;
        $this->maxRetries = $maxRetries;
    }
}
