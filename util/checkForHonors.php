#!/usr/bin/php
<?php

/**
 * checkForHonors.php
 * 
 * Input: List of student Banner IDs, one per line
 * Output: CSV list of student Banner IDs and a column
 *         indicating if the student is flagged as honors in Banner
 */

require_once('SOAP.php');
require_once('cliCommon.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('term'=>'',
              'input_file'=>'',
              'output_file'=>'');
$switches = array();
check_args($argc, $argv, $args, $switches);

$term = $args['term'];

// Get an instance of SOAP
$soap = new PhpSOAP();

// Open input and output files
//$students = fopen($args['input_file'], 'r');
$csvout = fopen($args['output_file'], 'w');
fputcsv($csvout, array('Banner ID', 'Honors?'), ',');

// pull list of Banner IDs from input file into an array
$bannerIds = file($args['input_file']);

foreach($bannerIds as $id){
    $banId = trim($id);

    $student = null;
    try{
        $username= $soap->getUsername($banId);
        $student = $soap->getStudentInfo($username, $term);
    }catch(Exception $e){
        echo "Missing student: $banId!!!!!\n";
        continue;
    }

    if($student->honors == 1){
        $honors = "yes";
    }else{
        $honors = "no";
    }

    fputcsv($csvout, array($banId, $honors), ',');
}

fclose($csvout);
