<?php

namespace App\Services\WasenderApi\DTO;

class SendContactMessageData
{
    public string $to;
    public string $contactName;
    public string $contactPhone;

    public function __construct(string $to, string $contactName, string $contactPhone)
    {
        $this->to = $to;
        $this->contactName = $contactName;
        $this->contactPhone = $contactPhone;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'contact_name' => $this->contactName, 'contact_phone' => $this->contactPhone];
    }
}
