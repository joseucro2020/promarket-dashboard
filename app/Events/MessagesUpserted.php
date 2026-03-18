<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class MessagesUpserted
{
    use SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
