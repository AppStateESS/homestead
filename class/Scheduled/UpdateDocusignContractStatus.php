<?php

namespace Homestead\Scheduled;

use \Homestead\DocusignClientFactory;
use \Homestead\Term;
use \Homestead\PdoFactory;
use \Homestead\Docusign\Client;

class UpdateDocusignContractStatus {

    private $docusignClient;
    private $httpClient;

    public static function cliExec()
    {
        \PHPWS_Core::initModClass('users', 'Users.php');
        \PHPWS_Core::initModClass('users', 'Current_User.php');

        $userId = \PHPWS_DB::getOne("SELECT id FROM users WHERE username = 'jb67803'");

        $user = new \PHPWS_User($userId);

        // Uncomment for production on branches
        $user->auth_script = 'shibboleth.php';
        $user->auth_name = 'shibboleth';

        //$user->login();
        $user->setLogged(true);

        \Current_User::loadAuthorization($user);
        //\Current_User::init($user->id);
        $_SESSION['User'] = $user;

        $obj = new UpdateDocusignContractStatus();
        $obj->execute();
    }

    public function execute()
    {
        // TODO: Check permissions

        // Get Docusign and HTTP clients to be used in sending requests later
        $this->docusignClient = DocusignClientFactory::getClient();
        $this->httpClient = new \Guzzle\Http\Client();

        // Get all future terms
        $futureTerms = Term::getFutureTerms();

        // Update contracts for each future term
        foreach ($futureTerms as $term) {
            echo "Updating contracts for $term:\n";
            $this->updateContractsForTerm($term);
        }
    }

    // Update contracts for the given term
    private function updateContractsForTerm($term)
    {
        $db = PdoFactory::getPdoInstance();

        // Locate all the contracts we need to check for this term
        $query = "select envelope_id from hms_contract where term = :term and envelope_status NOT IN ('completed', 'voided')";

        $stmt = $db->prepare($query);
        $stmt->execute(array('term'=>$term));

        $envelopeIdList = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $envCount = sizeof($envelopeIdList);

        if($envCount === 0){
            echo "No envelopes need to be udpated.\n";
            return;
        } else {
            echo "Found $envCount envelopes to check\n";
        }

        // Lookup 25 contracts at a time (in batches), until there are no more remaining
        $offset = 0;
        $envelopesPerRequest = 25;
        while($offset < $envCount){
            echo "Requesting status for $envelopesPerRequest starting at offset $offset... \n";

            $envelopeSlice = array_slice($envelopeIdList, $offset, $envelopesPerRequest);

            $envelopes = $this->sendRequest($envelopeSlice, $this->docusignClient, $this->httpClient);
            $this->saveEnvelopes($envelopes);

            $offset += $envelopesPerRequest;

            // Wait at least another second before we get the next batch
            sleep(1);
        }
    }

    private function sendRequest(Array $envelopeIdList, $docusignClient, $guzzleClient) {
        $envelopeIdJson = json_encode(array('envelopeIds' => $envelopeIdList));

        $url = $docusignClient->getBaseUrl() . '/envelopes/status?envelope_ids=request_body';
        //echo $url . "\n\n";
        $request = $guzzleClient->createRequest('PUT', $url);
        $request->setBody($envelopeIdJson, 'application/json');
        $request->setHeader('Content-Type', 'application/json');
        $request->setHeader('Accept', 'application/json');
        $request->setHeader('X-DocuSign-Authentication', $docusignClient->getAuthHeader());

        try{
            $response = $guzzleClient->send($request);
            $result = $response->json();
        }catch (\Guzzle\Http\Exception\BadResponseException $e){
            throw new \Exception(print_r($e->getResponse()->json(), true));
        }

        if(!isset($result['envelopes']) || sizeof($result['envelopes']) == 0){
            // We didn't get anything back, so we should probably stop here
            echo "No 'envelopes' key in the response, or the key had size == 0. Quitting.\n\n";
            exit;
        }

        return $result['envelopes'];
    }

    private function saveEnvelopes(Array $envelopes){
        $db = PdoFactory::getPdoInstance();
        $sql = 'UPDATE hms_contract SET envelope_status = :status, envelope_status_time = :statusTime where envelope_id = :envelopeId';
        $stmt = $db->prepare($sql);

        // For each envelope we got back, try save the new data to our db
        foreach($envelopes as $env){
            // Update the database with each envelope's new status and date
            $status = $env['status'];
            $time = strtotime($env['statusChangedDateTime']);
            $envelopeId = $env['envelopeId'];

            echo "Updating $envelopeId...\n";
            $stmt->execute(array(
                'status'=>$status,
                'statusTime' => $time,
                'envelopeId' => $envelopeId
                ));
        }
    }
}
