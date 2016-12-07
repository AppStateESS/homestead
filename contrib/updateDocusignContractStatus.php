#!/usr/bin/php
<?php

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

require_once('cliCommon.php');

$args = array('phpwsPath' => '',
                'term' => '');
$switches = array();
check_args($argc, $argv, $args, $switches);

$phpwsPath = $args['phpwsPath'];
$term = $args['term'];

require_once($phpwsPath . 'mod/hms/contrib/dbConnect.php');

require_once $phpwsPath . 'config/core/config.php';
//require_once 'src/Bootstrap.php';
require_once $phpwsPath . 'src/Autoloader.php';

require_once $phpwsPath . 'mod/hms/vendor/autoload.php';
require_once $phpwsPath . 'mod/hms/class/DocusignClientFactory.php';
require_once $phpwsPath . 'mod/hms/class/Docusign/Client.php';

$db = connectToDb();

if(!$db){
    die('Could not connect to database.\n');
}

$docusignClient = DocusignClientFactory::getClient();
$http = new \Guzzle\Http\Client();


// Locate all the contracts we need to check, make a list of them
$sql = "select envelope_id from hms_contract where term = {$term} and envelope_status NOT IN ('completed', 'voided')";
$result = pg_query($sql);

// Fetch each row and add it to a list
$envelopeIdList = array();
while($row = pg_fetch_array($result)){
    $envelopeIdList[] = $row['envelope_id'];
}

$envCount = sizeof($envelopeIdList);

if($envCount === 0){
    echo "No envelopes need to be udpated.\n";
    exit;
} else {
    echo "Found $envCount envelopes to check\n";
}


// Lookup 25 contracts at a time, until there are no more remaining
$offset = 0;
$envelopesPerRequest = 25;
while($offset < $envCount){
    echo "Requesting status for $envelopesPerRequest starting at offset $offset... \n";

    $envelopeSlice = array_slice($envelopeIdList, $offset, $envelopesPerRequest);

    $envelopes = sendRequest($envelopeSlice, $docusignClient, $http);
    saveEnvelopes($envelopes);

    $offset += $envelopesPerRequest;

    // Wait at least another second before we get the next batch
    sleep(1);
}
echo "\n\n";


function sendRequest(Array $envelopeIdList, $docusignClient, $guzzleClient) {
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

function saveEnvelopes(Array $envelopes){
    // For each envelope we got back, try save the new data to our db
    foreach($envelopes as $env){
        // Update the database with each envelope's new status and date
        $status = $env['status'];
        $time = strtotime($env['statusChangedDateTime']);
        $envelopeId = $env['envelopeId'];

        echo "Updating $envelopeId...\n";
        $sql = "UPDATE hms_contract SET envelope_status = '$status', envelope_status_time = $time where envelope_id = '$envelopeId'";
        $result = pg_query($sql);

        if($result === false){
            echo "Failed to update envelopeId: $enveloepId\n";
            continue;
        }
    }
}
