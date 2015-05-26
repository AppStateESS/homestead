#!/usr/bin/php
<?php

/**
 * Gets all assignments from HMS and compares them to Banner PROD
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');
require_once('../db_config.php.inc');

$args = array('term' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();

$db = pg_connect("host=$host dbname=$database user=$dbuser password=$dbpasswd");
$results = pg_query("
    SELECT
        hms_assignment.asu_username,
        hms_bed.banner_id,
        hms_residence_hall.banner_building_code
    FROM hms_assignment
    JOIN hms_bed ON
        hms_assignment.bed_id = hms_bed.id
    JOIN hms_room ON
        hms_bed.room_id = hms_room.id
    JOIN hms_floor ON
        hms_room.floor_id = hms_floor.id
    JOIN hms_residence_hall ON
        hms_floor.residence_hall_id = hms_residence_hall.id
    WHERE
        hms_room.is_online = 1 AND
        hms_floor.is_online = 1 AND
        hms_residence_hall.is_online = 1 AND
        hms_assignment.term = {$args['term']}
    ORDER BY
        hms_residence_hall.banner_building_code,
        hms_bed.banner_id
");

while($row = pg_fetch_assoc($results)) {
    $banner = $soap->getHousMealRegister($row['asu_username'], $args['term'], 'All');

    echo "{$row['asu_username']}:\t";
    echo "{$row['banner_building_code']} {$row['banner_id']}";
    echo "  <=>  ";

    if(!isset($banner->room_assign) || !is_object($banner->room_assign)){
        echo "No room assignment! *************************** \n";
        continue;
    }

    echo "{$banner->room_assign->bldg_code} {$banner->room_assign->room_code}";

    if($row['banner_building_code'] != $banner->room_assign->bldg_code ||
       $row['banner_id'] != $banner->room_assign->room_code) {
        echo "\t*********************";
    }

    echo "\n";
}
