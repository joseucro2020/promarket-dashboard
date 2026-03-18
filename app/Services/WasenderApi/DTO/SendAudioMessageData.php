<?php

namespace App\Services\WasenderApi\DTO;

class SendAudioMessageData
{
    public string $to;
    public string $audioUrl;

    public function __construct(string $to, string $audioUrl)
    {
        $this->to = $to;
        $this->audioUrl = $audioUrl;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'audio' => $this->audioUrl];
    }
}
