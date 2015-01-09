<?php

namespace Docusign;

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

        var_dump($data);
        
    	$http = new \Guzzle\Http\Client();
        try {
            //$request = $http->createRequest('POST', $this->client->getBaseUrl() . $this->envelope->getUri() . '/views/recipient', ['body' => json_encode($data)]);
            $request = $http->createRequest('POST', $this->client->getBaseUrl() . $this->envelope->getUri() . '/views/recipient');
            $request->setBody(json_encode($data), 'application/json');
            $request->setHeader('Content-Type', 'application/json');
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('X-DocuSign-Authentication', $this->client->getAuthHeader());
            $response = $http->send($request);   
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            //var_dump($e->getResponse()->json());
            //var_dump($e);
            //var_dump($e->getRequest());
            //exit;
            throw $e;
        }
        $result = $response->json();
        //var_dump($result);exit;        
        return $result['url'];
    }
}
