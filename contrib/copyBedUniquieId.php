#!/usr/bin/php
<?php

require_once('cliCommon.php');
require_once('dbConnect.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('fromTerm' => '', 'toTerm' => '');

$switches = array();
check_args($argc, $argv, $args, $switches);

$db = connectToDb();

$query = "SELECT hms_bed.bedroom_label, hms_bed.bed_letter, hms_bed.persistent_id, hms_room.persistent_id as room_p_id FROM hms_bed JOIN hms_room ON hms_room.id = hms_bed.room_id WHERE hms_bed.term = {$args['fromTerm']}"; 
$result = pg_query($query);

while ($row = pg_fetch_array($result)){ 
    $sql = "UPDATE hms_bed SET persistent_id = '{$row['persistent_id']}' FROM hms_room WHERE hms_bed.room_id = hms_room.id AND hms_room.persistent_id = '{$row['room_p_id']}' AND hms_bed.bedroom_label = '{$row['bedroom_label']}' AND hms_bed.bed_letter = '{$row['bed_letter']}' AND hms_bed.term = {$args['toTerm']}";
    //echo "$sql\n";
    pg_query($sql);
}

pg_close($db);

?>
