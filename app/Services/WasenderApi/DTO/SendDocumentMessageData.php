<?php

namespace App\Services\WasenderApi\DTO;

class SendDocumentMessageData
{
    public string $to;
    public string $documentUrl;
    public string $fileName;
    public ?string $caption;

    public function __construct(string $to, string $documentUrl, string $fileName, ?string $caption = null)
    {
        $this->to = $to;
        $this->documentUrl = $documentUrl;
        $this->fileName = $fileName;
        $this->caption = $caption;
    }

    public function toArray(): array
    {
        return ['to' => $this->to, 'document' => $this->documentUrl, 'file_name' => $this->fileName, 'caption' => $this->caption];
    }
}
