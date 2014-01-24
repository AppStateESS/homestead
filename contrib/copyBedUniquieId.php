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

$query = "SELECT hms_residence_hall.hall_name, hms_floor.floor_number, hms_room.room_number, hms_bed.bedroom_label, hms_bed.bed_letter, hms_bed.persistent_id FROM hms_bed JOIN hms_room ON hms_room.id = hms_bed.room_id JOIN hms_floor ON hms_room.floor_id = hms_floor.id JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id WHERE hms_bed.term = {$args['fromTerm']}"; 
$result = pg_query($query);

while ($row = pg_fetch_array($result)){ 
    $sql = "UPDATE hms_bed SET persistent_id = '{$row['persistent_id']}' FROM hms_room, hms_floor, hms_residence_hall WHERE hms_residence_hall.hall_name = '{$row['hall_name']}' AND hms_floor.floor_number = '{$row['floor_number']}' AND hms_room.room_number = '{$row['room_number']}' AND hms_bed.bedroom_label = '{$row['bedroom_label']}' AND hms_bed.bed_letter = '{$row['bed_letter']}' AND hms_bed.room_id = hms_room.id AND hms_room.floor_id = hms_floor.id AND hms_floor.residence_hall_id = hms_residence_hall.id AND hms_bed.term = {$args['toTerm']}";
    //echo "$sql\n";
    pg_query($sql);
}

pg_close($db);

?>
