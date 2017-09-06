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
            $request->setHeader('Content-Type', 'application/json');
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('X-DocuSign-Authentication', $this->client->getAuthHeader());
            $response = $http->send($request);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                newrelic_notice_error($e->getResponse()->json(), $e);
            }
            throw $e;
        }
        $result = $response->json();
        //var_dump($result);exit;
        return $result['url'];
    }
}
