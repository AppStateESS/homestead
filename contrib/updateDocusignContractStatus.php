#!/usr/bin/php
<?php

require_once('mod/hms/contrib/cliCommon.php');
require_once('mod/hms/contrib/dbConnect.php');

require_once 'config/core/config.php';
//require_once 'src/Bootstrap.php';
require_once 'src/Autoloader.php';

require_once 'mod/hms/vendor/autoload.php';
require_once 'mod/hms/class/DocusignClientFactory.php';
require_once 'mod/hms/class/Docusign/Client.php';

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('term' => '');
$switches = array();
check_args($argc, $argv, $args, $switches);

$term = $args['term'];

$db = connectToDb();

if(!$db){
    die('Could not connect to database.\n');
}

$sql = "select envelope_id from hms_contract where term = {$term}";
$result = pg_query($sql);

$envelopeIdList = array();

while($row = pg_fetch_array($result)){
    $envelopeIdList[] = $row['envelope_id'];
}

$envelopeIdJson = json_encode(array('envelope_ids' => $envelopeIdList));
echo $envelopeIdJson . "\n\n";

$docusignClient = DocusignClientFactory::getClient();

$http = new \Guzzle\Http\Client();
$url = $docusignClient->getBaseUrl() . '/envelopes/status';
echo $url . "\n\n";
$request = $http->createRequest('PUT', $url);
$request->setBody($envelopeIdJson, 'application/json');
$request->setHeader('Content-Type', 'application/json');
$request->setHeader('Accept', 'application/json');
$request->setHeader('X-DocuSign-Authentication', $docusignClient->getAuthHeader());

try{
    $response = $http->send($request);
    $result = $response->json();
}catch (\Guzzle\Http\Exception\BadResponseException $e){
    throw new \Exception(print_r($e->getResponse()->json(), true));
}

var_dump($result);
echo "\n\n";
