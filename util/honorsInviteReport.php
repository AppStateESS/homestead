#!/usr/bin/php
<?php



require_once('SOAP.php');
require_once('cliCommon.php');
require_once('../db_config.php.inc');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('term'=>'',
              'input_file'=>'',
              'output_file'=>'');
$switches = array();
check_args($argc, $argv, $args, $switches);

$term = $args['term'];

// Open input and output files
$invites = fopen($args['input_file'], 'r');
$csvout = fopen($args['output_file'], 'w');

// Connect to the database
$db = pg_connect("host=$host dbname=$database user=$dbuser password=$dbpasswd");

$ids = array();

// pull list of Banner IDs from input file into an array
$line = fgetcsv($invites, 500);
while(($line = fgetcsv($invites, 500)) !== FALSE) {
    $ids[] = $line[0];
}


// Query all freshmen application for this term
// Join fall application preferences, confirmed roommates
$sql = "SELECT
            hms_new_application.*, hms_fall_application.*, hms_roommate.*
        FROM
            hms_new_application
        JOIN
            hms_fall_application ON hms_new_application.id = hms_fall_application.id
        LEFT OUTER JOIN
            hms_roommate ON (hms_new_application.username ILIKE hms_roommate.requestor OR hms_new_application.username ILIKE hms_roommate.requestee)
        WHERE
            hms_new_application.term = {$args['term']} AND
            ((hms_roommate.confirmed = 1 AND hms_roommate.term = {$args['term']}) OR (hms_roommate.term IS NULL))
        ";

$result = pg_query($sql);

// Get an instance of SOAP
$soap = new PhpSOAP();

// Setup output file with field labels
$line = array(  'Last Name',
                'First Name',
                'Middle Name',
                'Dorm Preference',
                'Bedtime',
                'Room Condition',
                'Roommate Last',
                'Roommate First',
                'Roommate Middle');
fputcsv($csvout, $line, ',');

// For each application from the db
while($row = pg_fetch_assoc($result)) {
    // Check if the student is in the list of honors students
    echo "Checking {$row['username']}\n";
    if(in_array($row['banner_id'], $ids)) {
        $student = null;
        try{
            $student = $soap->getStudentInfo($row['username'], $term);
        }catch(Exception $e){
            echo "Missing student: {$row['username']}!!!!\n";
        }

        $last   = fix($student->last_name);
        $first  = fix($student->first_name);
        $middle = fix($student->middle_name);

        echo "Writing $last, $first $middle ";
        $lifestyle = ($row['lifestyle_option'] == 1 ? 'Single-Gender' :
                        ($row['lifestyle_option'] == 2 ? 'Co-Ed' : 'Unrecognized'));
        $bedtime   = ($row['preferred_bedtime']   == 1 ? 'Early' :
                        ($row['preferred_bedtime']   == 2 ? 'Late'  : 'Unrecognized'));
        $condition = ($row['room_condition'] == 1 ? 'Clean' :
                        ($row['room_condition'] == 2 ? 'Dirty' : 'Unrecognized'));

        $roommate_last   = '-';
        $roommate_first  = '-';
        $roommate_middle = '-';
        if(!empty($row['requestor'])) {
            try {
                $roommate = (fix($row['username']) == fix($row['requestor']) ?
                    $soap->getStudentInfo($row['requestee'], $term) :
                    $soap->getStudentInfo($row['requestor'], $term));
                $roommate_last   = fix($roommate->last_name);
                $roommate_first  = fix($roommate->first_name);
                $roommate_middle = fix($roommate->middle_name);
                echo "with roommate $roommate_last, $roommate_first $roommate_middle";
            }catch(Exception $e){
                echo "Missing roommate: {$row['requestor']}!!!!!!!!\n";
            }
        }

        $line = array(  $last,
                        $first,
                        $middle,
                        $lifestyle,
                        $bedtime,
                        $condition,
                        $roommate_last,
                        $roommate_first,
                        $roommate_middle);
        fputcsv($csvout, $line, ',');
        $exact++;
        echo "\n";
    }
}

pg_close($db);
fclose($invites);
fclose($csvout);

function fix($string)
{
    return trim($string);
}

/*
function fputcsv(&$handle, $fields = array(), $delimiter = ';', $enclosure = '"')
{
    $str = '';
    $escape_char = '\\';
    foreach ($fields as $value)
    {
        if (strpos($value, $delimiter) !== false ||
        strpos($value, $enclosure) !== false ||
        strpos($value, "\n") !== false ||
        strpos($value, "\r") !== false ||
        strpos($value, "\t") !== false ||
        strpos($value, ' ') !== false)
        {
            $str2 = $enclosure;
            $escaped = 0;
            $len = strlen($value);
            for ($i=0;$i<$len;$i++)
            {
                if ($value[$i] == $escape_char)
                    $escaped = 1;
                else if (!$escaped && $value[$i] == $enclosure)
                    $str2 .= $enclosure;
                else
                    $escaped = 0;
                $str2 .= $value[$i];
            }
            $str2 .= $enclosure;
            $str .= $str2.$delimiter;
        }
        else
            $str .= $value.$delimiter;
        }
    $str = substr($str,0,-1);
    $str .= "\n";
    return fwrite($handle, $str);
}
*/
