#!/usr/bin/php
<?php

require_once('SOAP.php');
require_once('cliCommon.php');
require_once('../db_config.php.inc');

$args = array('term' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();

$db = pg_connect("host=$host dbname=$database user=$dbuser password=$dbpasswd");

$sql = "select asu_username from hms_assignment where term = {$args['term']}";

$result = pg_query($sql);

$upperCount = 0;
$lowerCount = 0;

$cutoffTerm = $args['term'] - 20;

while($row = pg_fetch_assoc($result)){

    echo "Checking {$row['asu_username']}: ";
    try{
        $soapResult = $soap->getStudentInfo($row['asu_username'], $args['term']);
        //if($soapResult->watauga_member == '1'){
        if($soapResult->honors == '1'){
            echo "Honors!!!\n";
            if($soapResult->application_term < $cutoffTerm){
                $upperCount++;
            }elseif ($soapResult->application_term >= $cutoffTerm){
                $lowerCount++;
            }else{
                echo "INVALID TERM!!!ahhhhh!\n";
            }
        }else{
            echo "\n";
        }
    }catch(Exception $e){
        echo "Unknown student. Ahh!!!\n";
    }
}

echo "Upper count: $upperCount\n";
echo "Lower count: $lowerCount\n";
?>
