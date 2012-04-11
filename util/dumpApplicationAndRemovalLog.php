#!/usr/bin/php
<?php

/**
  * For each user name in an input file, this script outputs a CSV line (to an output file)
  * containing the student's lottery application and the 'removed from room' log entry.
  *
  * The purpose is to find out more information about returning (lottery) students who were removed.
  *
  * @author Jeremy Booker
  */

require_once('cliCommon.php');
require_once('../db_config.php.inc');

$args = array('term' => '',
              'inputFile'=>'',
              'outputFile'=>'');
$switches = array();

check_args($argc, $argv, $args, $switches);

$db = pg_connect("host=$host dbname=$database user=$dbuser password=$dbpasswd");

$users = file($args['inputFile']);
$outputFile = fopen($args['outputFile'],"w+");

$first = true;

foreach($users as $user){
    $user = trim($user);

    echo "Looking up: $user\n";

    $sql = "select * from hms_new_application LEFT OUTER JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id LEFT OUTER JOIN (SELECT * from hms_activity_log WHERE user_id = '$user' and activity = 12 ORDER BY timestamp DESC LIMIT 1) as foo  ON hms_new_application.username = foo.user_id WHERE hms_new_application.term = {$args['term']} AND username = '$user'";

    //echo $sql . "\n";

    $result = pg_query($sql);


    $row = pg_fetch_assoc($result);
    $row['timestamp_human'] = date("m/d/Y h:i:s A",$row['timestamp']);
    $row['notes'] = str_replace("\r\n", '', $row['notes']);

    //print_r($row);

    if($first == true){
        fputcsv($outputFile, array_keys($row));
        $first = false;
    }

    fputcsv($outputFile, $row);
    //echo print_r($row);
}

?>
