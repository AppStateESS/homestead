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
            $request->setHeader('Content-Type', 'application/json');
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('X-DocuSign-Authentication', $client->getAuthHeader());
            $data = array('envelopeId' => $this->envelopeId);
            $request->setBody(json_encode($data), 'application/json');
            $response = $http->send($request);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw $e;
        }
        $result = $response->json();
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

            $request->setBody(json_encode($obj), 'application/json');

            $request->setHeader('Content-Type', 'application/json');
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('X-DocuSign-Authentication', $client->getAuthHeader());
            $response = $http->send($request);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \Exception(print_r($e->getResponse()->json(), true));
        }

        $result = $response->json();
    }
}
