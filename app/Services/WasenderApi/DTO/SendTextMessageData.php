<?php

namespace App\Services\WasenderApi\DTO;

class SendTextMessageData
{
    public string $to;
    public string $text;

    public function __construct(string $to, string $text)
    {
        $this->to = $to;
        $this->text = $text;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'text' => $this->text];
    }
}
