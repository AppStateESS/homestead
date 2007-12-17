#!/usr/bin/php
<?php

/**
 * Rereport HMS Applications to Banner
 *
 * This script was designed to fix a case where HMS has
 * saved student housing applications, but those applications
 * do not appear in Banner.
 */

define('PHPWS_SOURCE_DIR', '../../../');

require('../class/HMS_SOAP.php');

define('CURR_TERM', 200810);

echo "Rereporting Applications...\n";

# Get the list of usernames
$usernames = file('applications_to_rereport-2007-12-12.txt');

echo sizeof($usernames) . " applications to report...\n";

# Connect to the database
pg_connect("") #TODO: Connection String
    or die("Could not connect to database... " . pg_last_error($db) . "\n");

# Foreach username, lookup their application and try to report it to banner
foreach($usernames as $username){

    echo "Looking up application for: $username...";
    
    $result = pg_query("SELECT * from hms_application WHERE hms_student_id ILIKE '$username' AND term=". CURR_TERM);

    if(!$result){
        echo "\nAn error occured looking up appliction for $username: " . pg_last_error($db) . "\n";
        exit;
    }

    if(sizeof($result) > 1){
        echo "Multiple applications returned, skipping.\n";
        continue;
    }

    if(sizeof($result) <= 0){
        echo "No application found, skipping.\n";
        continue;
    }

    # Get the application from the database
    $row = pg_fetch_assoc($result);
    
    # Actually try to report it to Banner
    $plan_meal = HMS_SOAP::get_plan_meal_codes($username, 'BLAH_DORM', $row['meal_option']);
    $banner_result = HMS_SOAP::report_application_received($username, CURR_TERM, $plan_meal['plan'], $plan_meal['meal']);

    if($banner_result !== 0){
        print_r($banner_result);
        echo "Banner error: $banner_result\n";
    }else{
        echo "Reported.\n";
    }
}

pg_close($db);

?>
