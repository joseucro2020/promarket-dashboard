<?php

namespace App\Services\WasenderApi;

use Illuminate\Support\Facades\Http;
use App\Services\WasenderApi\DTO\RetryConfig;
use App\Services\WasenderApi\Exceptions\WasenderApiException;

class WasenderClient
{
    protected $apiKey;
    protected $baseUrl = 'https://www.wasenderapi.com/api';

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?: config('wasenderapi.api_key');
        $this->baseUrl = config('wasenderapi.base_url', $this->baseUrl);
    }

    protected function sendRequest(string $endpoint, array $payload = [], ?RetryConfig $retry = null)
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $attempts = 1;
        $maxAttempts = $retry && $retry->enabled ? max(1, $retry->maxRetries) : 1;

        beginning:
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post($url, $payload);

        if ($response->status() === 429 && $attempts < $maxAttempts) {
            $attempts++;
            $wait = $response->header('Retry-After') ? (int) $response->header('Retry-After') : 1;
            sleep($wait);
            goto beginning;
        }

        if (!$response->successful()) {
            throw new WasenderApiException('API request failed', $response->status(), $response);
        }

        return $response->json();
    }

    /**
     * Send a simple text message. Accepts DTO or raw params.
     */
    public function sendText($toOrDto, ?string $text = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendTextMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'text' => $text];
        }

        $payload = array_merge($payload, $options);

        return $this->sendRequest('send-message', $payload, $retry);
    }

    public function sendImage($toOrDto, ?string $imageUrl = null, ?string $caption = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendImageMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'image' => $imageUrl, 'caption' => $caption];
        }
        $payload = array_merge($payload, $options);
        return $this->sendRequest('send-image', $payload, $retry);
    }

    public function sendVideo($toOrDto, ?string $videoUrl = null, ?string $caption = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendVideoMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'video' => $videoUrl, 'caption' => $caption];
        }
        $payload = array_merge($payload, $options);
        return $this->sendRequest('send-video', $payload, $retry);
    }

    public function sendDocument($toOrDto, ?string $documentUrl = null, ?string $fileName = null, ?string $caption = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendDocumentMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'document' => $documentUrl, 'file_name' => $fileName, 'caption' => $caption];
        }
        $payload = array_merge($payload, $options);
        return $this->sendRequest('send-document', $payload, $retry);
    }

    public function sendAudio($toOrDto, ?string $audioUrl = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendAudioMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'audio' => $audioUrl];
        }
        $payload = array_merge($payload, $options);
        return $this->sendRequest('send-audio', $payload, $retry);
    }

    public function sendSticker($toOrDto, ?string $stickerUrl = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendStickerMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'sticker' => $stickerUrl];
        }
        $payload = array_merge($payload, $options);
        return $this->sendRequest('send-sticker', $payload, $retry);
    }

    public function sendContact($toOrDto, ?string $contactName = null, ?string $contactPhone = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendContactMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'contact_name' => $contactName, 'contact_phone' => $contactPhone];
        }
        $payload = array_merge($payload, $options);
        return $this->sendRequest('send-contact', $payload, $retry);
    }

    public function sendLocation($toOrDto, ?float $latitude = null, ?float $longitude = null, ?string $name = null, ?string $address = null, array $options = [], ?RetryConfig $retry = null)
    {
        if ($toOrDto instanceof \App\Services\WasenderApi\DTO\SendLocationMessageData) {
            $payload = $toOrDto->toArray();
        } else {
            $payload = ['to' => $toOrDto, 'latitude' => $latitude, 'longitude' => $longitude, 'name' => $name, 'address' => $address];
        }
        $payload = array_merge($payload, $options);
        return $this->sendRequest('send-location', $payload, $retry);
    }

    // Contacts
    public function getContacts()
    {
        return $this->sendRequest('contacts/list', []);
    }

    public function getContactInfo(string $phone)
    {
        return $this->sendRequest('contacts/info', ['phone' => $phone]);
    }

    public function getContactProfilePicture(string $phone)
    {
        return $this->sendRequest('contacts/profile-picture', ['phone' => $phone]);
    }

    public function blockContact(string $phone)
    {
        return $this->sendRequest('contacts/block', ['phone' => $phone]);
    }

    public function unblockContact(string $phone)
    {
        return $this->sendRequest('contacts/unblock', ['phone' => $phone]);
    }

    // Groups
    public function getGroups()
    {
        return $this->sendRequest('groups/list', []);
    }

    public function getGroupMetadata(string $jid)
    {
        return $this->sendRequest('groups/metadata', ['jid' => $jid]);
    }

    public function getGroupParticipants(string $jid)
    {
        return $this->sendRequest('groups/participants', ['jid' => $jid]);
    }

    public function addGroupParticipants(string $jid, array $participants)
    {
        return $this->sendRequest('groups/add-participants', ['jid' => $jid, 'participants' => $participants]);
    }

    public function removeGroupParticipants(string $jid, array $participants)
    {
        return $this->sendRequest('groups/remove-participants', ['jid' => $jid, 'participants' => $participants]);
    }

    public function updateGroupSettings(string $jid, array $settings)
    {
        return $this->sendRequest('groups/update-settings', array_merge(['jid' => $jid], $settings));
    }

    // Sessions
    public function getAllWhatsAppSessions()
    {
        return $this->sendRequest('sessions/list', []);
    }

    public function createWhatsAppSession(array $payload)
    {
        return $this->sendRequest('sessions/create', $payload);
    }

    public function getWhatsAppSessionDetails(string $sessionId)
    {
        return $this->sendRequest("sessions/{$sessionId}");
    }

    public function updateWhatsAppSession(string $sessionId, array $payload)
    {
        return $this->sendRequest("sessions/{$sessionId}/update", $payload);
    }

    public function deleteWhatsAppSession(string $sessionId)
    {
        return $this->sendRequest("sessions/{$sessionId}/delete");
    }

    public function connectWhatsAppSession(string $sessionId, bool $qrAsImage = false)
    {
        return $this->sendRequest("sessions/{$sessionId}/connect", ['qr_as_image' => $qrAsImage]);
    }

    public function getWhatsAppSessionQrCode(string $sessionId)
    {
        return $this->sendRequest("sessions/{$sessionId}/qr");
    }

    public function disconnectWhatsAppSession(string $sessionId)
    {
        return $this->sendRequest("sessions/{$sessionId}/disconnect");
    }

    public function regenerateApiKey(string $sessionId)
    {
        return $this->sendRequest("sessions/{$sessionId}/regenerate-api-key");
    }

    public function getSessionStatus(string $sessionId)
    {
        return $this->sendRequest("sessions/{$sessionId}/status");
    }
}
