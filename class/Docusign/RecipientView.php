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
        $data = array (
            "email" => $this->studentEmail,
            "returnUrl" => $returnUrl,
            "authenticationMethod" => "None",
            "userName" => $this->studentName,
            "clientUserId" => $this->clientUserId
        );

        //var_dump($data);
        //exit;

    	$http = new \GuzzleHttp\Client();
        try {
            //$request = $http->createRequest('POST', $this->client->getBaseUrl() . $this->envelope->getUri() . '/views/recipient', ['body' => json_encode($data)]);
            $request = new \GuzzleHttp\Psr7\Request('POST', $this->client->getBaseUrl() . $this->envelope->getUri() . '/views/recipient');
            $request->setBody(json_encode($data), 'application/json');
            $response = $http->send($request, ['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-DocuSign-Authentication' => $this->client->getAuthHeader()]]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                newrelic_notice_error($e->getResponse()->json(), $e);
            }
            throw $e;
        }
        $result = json_decode($response->getBody(), true);
        //var_dump($result);exit;
        return $result['url'];
    }
}
