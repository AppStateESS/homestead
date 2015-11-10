#!/usr/bin/php
<?php

require_once('cliCommon.php');
require_once('dbConnect.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('input_file'  => '',
              'output_file' => '',
              'term'        => '');
$switches = array();
check_args($argc, $argv, $args, $switches);

// Open input and output files
$inputFile = fopen($args['input_file'], 'r');
$outputFile = fopen($args['output_file'], 'w+');

if($inputFile === FALSE){
    die("Could not open input file.\n");
    exit;
}

$db = connectToDb();

if(!$db){
    die('Could not connect to database.\n');
}

$term = $args['term'];

// Parse CSV input into fields line by line
while(($line = fgetcsv($inputFile, 0)) !== FALSE) {
    foreach($line as $key=>$element){
        $line[$key] = pg_escape_string($element);
    }

    print_r($line);

    $hall = $line[0];
    $room = $line[1];

    $sql = "select persistent_id FROM hms_residence_hall JOIN hms_floor ON hms_residence_hall.id = hms_floor.residence_hall_id JOIN hms_room ON hms_floor.id = hms_room.floor_id WHERE hms_residence_hall.term = $term and hall_name = '$hall' and room_number ILIKE '%$room%'";
    //print_r($sql);echo "\n";

    $result = pg_query($sql);
    //print_r($result);echo "\n";

    $row = pg_fetch_row($result);
    //print_r("result row: ");

    //var_dump($row);
    if($row === false){
        echo "Skipping $hall $room\n";
    }else{
        $line[] = $row[0];
    }

    fputcsv($outputFile, $line);
}
