#!/usr/bin/php
<?php

require_once('SOAP.php');
require_once('cliCommon.php');
require_once('../db_config.php.inc');

$args = array('output_file'=>'','term'=>'');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();

$csvout = fopen($args['output_file'], 'w');

# Header line
$line = array( 'User Name',
               'Banner Id',
               'Residence Hall',
               'Room Number',
               'Assignment Term',
               'Application Term',
               'Class');
fputcsv($csvout, $line, ',');


$db = pg_connect("host=$host dbname=$database user=$dbuser password=$dbpasswd");

$sql = "SELECT asu_username, hall_name, room_number, hms_assignment.term FROM hms_assignment JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id JOIN hms_room ON hms_bed.room_id = hms_room.id JOIN hms_floor ON hms_room.floor_id = hms_floor.id JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id WHERE hms_assignment.term = {$args['term']} ORDER BY banner_building_code";

$result = pg_query($sql);

while($row = pg_fetch_assoc($result)){
    echo "Getting soap date for: {$row['asu_username']}\n";
    try{
        $student = $soap->getStudentInfo($row['asu_username'], $row['term']);

        $class = $student->projected_class;
        if(empty($class) || is_null($class)){
            $class = 'unknown';
        }

        $applicationTerm = $student->application_term;
        if(empty($applicationTerm) || is_null($applicationTerm)){
            $applicationTerm = 'unknown';
        }

        if($student->honors == '1'){
            $line = array(  $row['asu_username'],
                            $student->banner_id,
                            $row['hall_name'],
                            $row['room_number'],
                            $row['term'],
                            $applicationTerm,
                            $class);
            fputcsv($csvout, $line, ',');
        }
    }catch(Exception $e){
        echo "Unknown student!!!!!!!\n";
        echo "$e\n";
    }
}
