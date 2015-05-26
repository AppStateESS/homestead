#!/usr/bin/php
<?php

require_once('cliCommon.php');
require_once('dbConnect.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('input_file' => '',
              'term' => '',
              'group_name' => '');
$switches = array();
check_args($argc, $argv, $args, $switches);

$term = $args['term'];

$db = connectToDb();

if(!$db){
    die('Could not connect to database.\n');
}


// Get lines in input file
$bannerIds = file($args['input_file']);


foreach($bannerIds as $bId) {
    $banner = trim($bId);

    // Check if the student has applied for the term in question
    $sql = "select * from hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id where term = $term and banner_id = $banner";

    $result = pg_query($sql);

    $row = pg_fetch_array($result);
     if(!$row){
        echo "$banner did not apply!\n";
        continue;
    }


    // Update that application with the interest group name
    $sql = "update hms_lottery_application SET special_interest = '{$args['group_name']}' where id = {$row['id']}";
    $result = pg_query($sql);
}

pg_close($db);
