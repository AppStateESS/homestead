#!/usr/bin/php
<?php

/*
 * Gets a list of all the new freshmen housing applications that are withdrawn, then double checks the student type of all those students.
 * This double-checks that we don't accidentally withrawn an application for a student who's not really withdrawn.
 */

require_once('SOAP.php');
require_once('cliCommon.php');
require_once('../db_config.php.inc');

$args = array('term' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();

$db = pg_connect("host=$host dbname=$database user=$dbuser password=$dbpasswd");

$sql = "select username from hms_new_application where term = {$args['term']} and (withdrawn = 1 OR student_type = 'W') and application_type = 'fall'";

$result = pg_query($sql);

while($row = pg_fetch_assoc($result)){

    echo "Checking {$row['username']}: ";
    try{
        $soapResult = $soap->getStudentInfo($row['username'], $args['term']);
        if($soapResult->student_type != 'W'){
            echo "Application withdrawn but student type is not W!!!!!!!!!!!1\n";
        }else{
            echo "\n";
        }
    }catch(Exception $e){
        echo "Unknown student. Ahh!!!\n";
    }
}
?>
