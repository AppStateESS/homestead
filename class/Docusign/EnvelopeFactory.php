<?php
namespace Docusign;

class EnvelopeFactory {

	static function createEnvelopeFromTemplate(Client $client, $templateId, $emailSubject, Array $templateRoles, $status) {
		$data = array (
			"accountId" => $client->getAccountID(),
			"emailSubject" => $emailSubject,
			"templateId" => $templateId,
			"templateRoles" => $templateRoles,
			"status" => $status
		);
        
        $http = new \GuzzleHttp\Client();
        try {
            $request = $http->createRequest('POST', $client->getBaseUrl() . '/envelopes', ['body' => json_encode($data)]);
            $request->setHeader('Content-Type', 'application/json');
            $request->setHeader('Accept', 'application/json');
            $request->setHeader('X-DocuSign-Authentication', $client->getAuthHeader());
            $response = $http->send($request);   
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            var_dump($e);
            var_dump($e->getRequest());
            exit;
        }
        $result = $response->json();
        
        return new Envelope($result['envelopeId'], $result['uri'], $result['statusDateTime'], $result['status']);
    }

	static function getEnvelopeById(Client $client, $envelopeId) {

	}
}
?>