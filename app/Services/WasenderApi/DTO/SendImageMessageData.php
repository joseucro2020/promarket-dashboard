<?php

namespace App\Services\WasenderApi\DTO;

class SendImageMessageData
{
    public string $to;
    public string $imageUrl;
    public ?string $caption;

    public function __construct(string $to, string $imageUrl, ?string $caption = null)
    {
        $this->to = $to;
        $this->imageUrl = $imageUrl;
        $this->caption = $caption;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'image' => $this->imageUrl, 'caption' => $this->caption];
    }
}
