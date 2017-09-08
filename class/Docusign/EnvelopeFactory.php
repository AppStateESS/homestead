<?php
namespace Homestead\Docusign;

class EnvelopeFactory {

	public static function createEnvelopeFromTemplate(Client $client, $templateId, $emailSubject, Array $templateRoles, $status, $bannerId) {

		// //Creates the data field containing the banner ID value
		$textTabs[0] = array("tabLabel" => "BannerId", "value" => $bannerId, "pageNumber" => "5", "documentId" => "1");

		$roles = $templateRoles;

		$roles[0]['tabs'] = array("textTabs" => $textTabs);

		// var_dump($templateRoles);exit;

		$data = array (
			"accountId" => $client->getAccountID(),
			"emailSubject" => $emailSubject,
			"templateId" => $templateId,
			"templateRoles" => $roles,
			"status" => $status
		);


        $http = new \GuzzleHttp\Client();
        //$request = $http->createRequest('POST', $client->getBaseUrl() . '/envelopes', ['body' => json_encode($data)]);
        $request = new \GuzzleHttp\Psr7\Request('POST', $client->getBaseUrl() . '/envelopes');
        //$request->setBody(json_encode($data), 'application/json');

        try{
            $response = $http->send($request, ['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-DocuSign-Authentication' => $client->getAuthHeader()]]);
            $result = json_decode($response->getBody(), true);
        }catch (\GuzzleHttp\Exception\BadResponseException $e){
            throw new \Exception($e);
        }


        return new Envelope($result['envelopeId'], $result['uri'], $result['statusDateTime'], $result['status']);
    }

	public static function getEnvelopeById(Client $client, $envelopeId) {
        $http = new \Guzzle\Http\Client();
        try {
            $request = new \GuzzleHttp\Psr7\Request('GET', $client->getBaseUrl() . '/envelopes/' . $envelopeId);
            $response = $http->send($request, ['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-DocuSign-Authentication' => $client->getAuthHeader()]]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \Exception(print_r($e->getResponse()->json(), true));
        }
        $result = json_decode($response->getBody(), true);

        $envelope = new Envelope($result['envelopeId'], '/envelopes/' . $envelopeId, $result['statusChangedDateTime'], $result['status']);
        return $envelope;
	}
}
