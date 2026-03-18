<?php

namespace App\Services\WasenderApi\DTO;

class SendLocationMessageData
{
    public string $to;
    public float $latitude;
    public float $longitude;
    public ?string $name;
    public ?string $address;

    public function __construct(string $to, float $latitude, float $longitude, ?string $name = null, ?string $address = null)
    {
        $this->to = $to;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->name = $name;
        $this->address = $address;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'name' => $this->name, 'address' => $this->address];
    }
}
