<?php

namespace Docusign;

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

	public function getEnvelopeViewURI(Client $client)
	{
		$http = new \Guzzle\Http\Client();
        try {
            $request = $http->createRequest('POST', $client->getBaseUrl() . '/views/console/');
            $request->setHeader('Content-Type', 'application/json');
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('X-DocuSign-Authentication', $client->getAuthHeader());
            $data = array('envelopeId' => $this->envelopeId);
            $request->setBody(json_encode($data), 'application/json');
            $response = $http->send($request);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            //var_dump($e);
            //var_dump($e->getRequest());
            //exit;
            throw $e;
        }
        $result = $response->json();
        return $result['url'];
	}

}
