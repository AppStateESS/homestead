<?php

namespace Homestead\Docusign;

class Envelope {

    private $envelopeId;
    private $uri;
    private $statusDateTime;
    private $status;

    public function __construct($envelopeId, $uri, $statusDateTime, $status)
    {
        $this->envelopeId = $envelopeId;
        $this->uri = $uri;
        $this->statusDateTime = $statusDateTime;
        $this->status = $status;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getEnvelopeId()
    {
        return $this->envelopeId;
    }

    public function getStatusDateTime()
    {
        return $this->statusDateTime;
    }

    public function getStatusDateTimeUnixTimestamp()
    {
        return strtotime($this->statusDateTime);
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the URL to view this envelope's documents.
     */
    public function getEnvelopeViewURI(Client $client)
    {
        $http = new \GuzzleHttp\Client();
        try {
            $request = new \GuzzleHttp\Psr7\Request('POST', $client->getBaseUrl() . '/views/console/');
            $response = $http->send($request, ['json' => ['envelopeId' => $this->envelopeId],
            'header' => ['envelopeId' => $this->envelopeId],'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-DocuSign-Authentication' => $client->getAuthHeader()]]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw $e;
        }
        $result = json_decode($response->getBody(), true);
        return $result['url'];
    }

    public function voidEnvelope(Client $client, $reason)
    {
        $http = new \GuzzleHttp\Client();

        $obj = new \stdClass();
        $obj->status = 'voided';
        $obj->voidedReason = $reason;

        try {
            $request = new \GuzzleHttp\Psr7\Request('PUT', $client->getBaseUrl() . '/envelopes/' . $this->envelopeId);
            $response = $http->send($request, ['json' => $obj,
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-DocuSign-Authentication' => $client->getAuthHeader()]]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \Exception($e);
        }

        $result = json_decode($response->getBody(), true);
    }
}
