<?php

namespace App\Services\WasenderApi\DTO;

class SendVideoMessageData
{
    public string $to;
    public string $videoUrl;
    public ?string $caption;

    public function __construct(string $to, string $videoUrl, ?string $caption = null)
    {
        $this->to = $to;
        $this->videoUrl = $videoUrl;
        $this->caption = $caption;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'video' => $this->videoUrl, 'caption' => $this->caption];
    }
}
