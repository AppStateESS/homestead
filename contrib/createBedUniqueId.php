#!/usr/bin/php
<?php

require_once('cliCommon.php');
require_once('dbConnect.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('term' => '');

$switches = array();
check_args($argc, $argv, $args, $switches);

$db = connectToDb();

$query = "SELECT * FROM hms_bed WHERE term = {$args['term']}"; 
$result = pg_query($query);

while ($room = pg_fetch_array($result)){ 
    $sql = "UPDATE hms_bed SET persistent_id = '" . uniqid() . "' where id = {$room['id']} and term = {$args['term']}";
    pg_query($sql);
}

pg_close($db);

?>
