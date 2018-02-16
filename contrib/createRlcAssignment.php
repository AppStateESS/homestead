#!/usr/bin/php
<?php

require_once('SOAP.php');
require_once('cliCommon.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('input_file'=>'',
              'rlcId' => '',
              'term'  => '');

$switches = array();
check_args($argc, $argv, $args, $switches);

// Open input file
$inputFile = file($args['input_file']);

if($inputFile === FALSE){
    die("Could not open input file.\n");
    exit;
}

$dbname = trim(readline("Database name: "));

$dbuser = trim(readline("User name: "));

// A bit of hackery here to avoid echoing the password
echo "Database Password: ";
system('stty -echo');
$dbpasswd = trim(fgets(STDIN));
system('stty echo');
// add a new line since the users CR didn't echo
echo "\n";

// Connect to the database
//$db = pg_connect("host=$host dbname=$database user=$dbuser password=$dbpasswd");
$db = pg_connect("user=$dbuser password=$dbpasswd dbname=$dbname");

if(!$db){
    die('Could not connect to database.\n');
}

// Get an instance of SOAP
$soap = new PhpSOAP('eberhardtm', 'A');

foreach($inputFile as $line) {

    $bannerId = trim($line);

    if ($line == '') {
        continue;
    }

    $username = $soap->getUsername($bannerId);
    $student = $soap->getStudentProfile($bannerId, $args['term']);
    $gender = $student->gender;

    if($gender == 'M'){
        $gender = 1;
    }else if ($gender == 'F'){
        $gender = 0;
    }else{
        echo "Bad gender!!\n\n\n";
        continue;
    }

    if($username == 'InvalidUser' || $username == ''){
        echo 'Invalid BannerID: ' . $bannerId . ' EXITING!!';
        continue;
    }

    $sql = "insert into hms_learning_community_applications VALUES (nextval('hms_learning_community_applications_seq'), extract(epoch from now()), {$args['rlcId']}, null, null, '', '', '', '', '', '$username', {$args['term']}, 0, 'returning') RETURNING id";

    $result = pg_query($sql);

    $applicationId = pg_fetch_assoc($result);
    $applicationId = $applicationId['id'];

    $sql = "insert into hms_learning_community_assignment VALUES (nextval('hms_learning_community_assignment_seq'), {$args['rlcId']}, 'eberhardtm', $gender, $applicationId, 'new')";
    $result = pg_query($sql);

    echo "Added $bannerId.\n";
}

pg_close($db);
