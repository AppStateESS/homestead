#!/usr/bin/php
<?php
/**
 * Imports room damges from a CSV file.
 * Column order is: side (left/right), damage description/note, room persistent id
 */

require_once('cliCommon.php');
require_once('dbConnect.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('input_file'  => '',
              'term'        => '',
              'username'    => '');

$switches = array();
check_args($argc, $argv, $args, $switches);

$term = $args['term'];

// Open input and output files
$inputFile = fopen($args['input_file'], 'r');

$db = connectToDb();

if($inputFile === FALSE){
    die("Could not open input file.\n");
    exit;
}

while (($line = fgetcsv($inputFile, 0)) !== FALSE) {
    $side = $line[0];
    $desc = pg_escape_string($db, $line[1]);
    $roomPerstId = $line[2];
    $sql = "INSERT INTO hms_room_damage VALUES (nextval('hms_room_damage_seq'), '$roomPerstId', $term, 1, '$side', '$desc', 0, '$username', 1438401600)";
    pg_query($sql);
}

pg_close($db);
