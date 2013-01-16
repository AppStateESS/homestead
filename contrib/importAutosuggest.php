#!/usr/bin/php
<?php

//require_once('SOAP.php');
require_once('cliCommon.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('input_file'=>'');
$switches = array();
check_args($argc, $argv, $args, $switches);

//$host = 'localhost';

// Open input and output files
$inputFile = fopen($args['input_file'], 'r');

if($inputFile === FALSE){
    die("Could not open input file.\n");
    exit;
}

echo "Database name: ";
system('stty -echo');
$dbname = trim(fgets(STDIN));
system('stty echo');
// add a new line since the users CR didn't echo
echo "\n";


echo "Database Username: ";
system('stty -echo');
$dbuser = trim(fgets(STDIN));
system('stty echo');
// add a new line since the users CR didn't echo
echo "\n";


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
//$soap = new PhpSOAP();

// Parse CSV input into fields line by line
while(($line = fgetcsv($inputFile, 0, '|')) !== FALSE) {
    foreach($line as $key=>$element){
        $line[$key] = pg_escape_string($element);
    }

    if(isset($line[5]) && $line[5] != ''){
        $sql = "INSERT INTO hms_student_autocomplete VALUES ({$line[0]},'','{$line[2]}', '{$line[3]}', '{$line[1]}', METAPHONE('{$line[2]}', 4), METAPHONE('{$line[3]}', 4), METAPHONE('{$line[1]}', 4), {$line[4]}, {$line[5]})";
    } else {
        $sql = "INSERT INTO hms_student_autocomplete VALUES ({$line[0]},'','{$line[2]}', '{$line[3]}', '{$line[1]}', METAPHONE('{$line[2]}', 4), METAPHONE('{$line[3]}', 4), METAPHONE('{$line[1]}', 4), {$line[4]})";
    }
    $result = pg_query($sql);
}

pg_close($db);
fclose($inputFile);

?>
