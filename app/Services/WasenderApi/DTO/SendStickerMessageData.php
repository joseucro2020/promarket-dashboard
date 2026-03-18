<?php

namespace App\Services\WasenderApi\DTO;

class SendStickerMessageData
{
    public string $to;
    public string $stickerUrl;

    public function __construct(string $to, string $stickerUrl)
    {
        $this->to = $to;
        $this->stickerUrl = $stickerUrl;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'sticker' => $this->stickerUrl];
    }
}
