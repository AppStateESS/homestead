<?php

namespace Homestead\Docusign;

class RecipientView {

    private $client;
    private $envelope;
    private $clientUserId;
    private $studentName;
    private $studentEmail;

    public function __construct(Client $client, Envelope $envelope, $clientUserId, $studentName, $studentEmail)
    {
    	$this->client = $client;
        $this->envelope = $envelope;
        $this->clientUserId = $clientUserId;
        $this->studentName = $studentName;
        $this->studentEmail = $studentEmail;
    }

    public function getRecipientViewUrl($returnUrl)
    {
    	$http = new \GuzzleHttp\Client();
        try {
            $request = new \GuzzleHttp\Psr7\Request('POST', $this->client->getBaseUrl() . $this->envelope->getUri() . '/views/recipient');
            $response = $http->send($request, ['json' => ["email" => $this->studentEmail, "returnUrl" => $returnUrl, "authenticationMethod" => "None", "userName" => $this->studentName, "clientUserId" => $this->clientUserId],
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-DocuSign-Authentication' => $this->client->getAuthHeader()]]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                newrelic_notice_error($e);
            }
            throw $e;
        }
        $result = json_decode($response->getBody(), true);
        return $result['url'];
    }
}
