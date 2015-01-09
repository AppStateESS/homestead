<?php

namespace Docusign;

class RecipientView {
	
    private $client;
    private $envelope;
    private $clientUserId;
    private $name;
    
    public function __construct(Client $client, Envelope $envelope, $clientUserId, $name)
    {
    	$this->client = $client;
        $this->envelope = $envelope;
        $this->clientUserId = $clientUserId;
        $this->name = $name;
    }
    
    public function getRecipientViewUrl($returnUrl)
    {
        $data = array (
            "email" => $this->client->getCreds()->getEmail(),
            "returnUrl" => $returnUrl,
            "authenticationMethod" => "None",
            "username" => $this->name,
            "clientUserId" => $this->clientUserId
        );
        
    	$http = new \GuzzleHttp\Client();
        try {
            $request = $http->createRequest('POST', $this->client->getBaseUrl() . $this->envelope->getUri() . '/views/recipient', ['body' => json_encode($data)]);
            $request->setHeader('Content-Type', 'application/json');
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('X-DocuSign-Authentication', $this->client->getAuthHeader());
            $response = $http->send($request);   
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            var_dump($e);
            var_dump($e->getRequest());
            exit;
        }
        $result = $response->json();
        //var_dump($result);exit;        
        return $result['url'];
    }
}