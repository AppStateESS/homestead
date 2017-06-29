#!/usr/bin/php
<?php

// Make sure this can only be run from the command line
if(php_sapi_name() !== 'cli'){
    die('This script can only be run from the command line.');
}

require_once('cliCommon.php');
//require_once('dbConnect.php');
//require_once('SOAP.php');

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

$args = array('term' => '',
              'username' => '',
              'module' => '',
              'phpwsConfigPath' => '');

$switches = array();
check_args($argc, $argv, $args, $switches);

// Try to include the main phpws config file
includePhpwsConfigFile($args['phpwsConfigPath']);

// Change current dir to the root of phpws' install
chdir(PHPWS_SOURCE_DIR);

// Define these to avoid errors in sanity checking later
$_SERVER['REQUEST_URI'] = 'cli.php';
$_SERVER['HTTP_HOST'] = 'localhost';

// Include PHPWS bootstrapping code
require_once PHPWS_SOURCE_DIR . 'config/core/source.php';
require_once PHPWS_SOURCE_DIR . 'Global/Functions.php';

// May need to be commented out for production?
require_once PHPWS_SOURCE_DIR . 'src/Autoloader.php';
require_once PHPWS_SOURCE_DIR . 'src/Bootstrap.php';

require_once PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php';

// Set the 'module' request variable to help the autoloader find classes
$_REQUEST['module'] = $args['module'];

$term = $args['term'];

$db = PdoFactory::getPdoInstance();

// Get the list of un-assigned freshmen that need to be temporarily assigned
$query = "SELECT hms_new_application.*
            FROM hms_new_application
            LEFT OUTER JOIN hms_assignment ON
                (hms_new_application.username = hms_assignment.asu_username AND hms_new_application.term = hms_assignment.term)
            WHERE
                hms_assignment.asu_username IS NULL
                AND hms_new_application.term = :term
                AND hms_new_application.cancelled = 0
                AND hms_new_application.student_type = 'F'
                AND hms_new_application.banner_id NOT IN (SELECT banner_id FROM hms_temp_assignment where banner_id IS NOT NULL)";

$stmt = $db->prepare($query);
$stmt->execute(array('term'=>$term));

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . sizeof($results) . " unassigned freshmen that need to be assigned.\n";
$soap = SOAP::getInstance($args['username'], 'A');

foreach($results as $app){
    echo "Assigning {$app['username']} ({$app['banner_id']})\n";
    // Try to find a temp bed for that person
    $query = "SELECT * FROM hms_temp_assignment WHERE banner_id IS NULL ORDER BY room_number ASC LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $roomNumber = $stmt->fetchColumn();

    if($roomNumber === false){
        echo "Error: Out of empty temp beds.\n";
        exit;
    }

    // Lookup the student
    $student = StudentFactory::getStudentByBannerId($app['banner_id'], $term);

    $applicationObj = HousingApplicationFactory::getApplicationById($app['id']);

    // Send the assignment to Banner
    try{
        $soap->createRoomAssignment($app['banner_id'], $term, 'TMPR', $roomNumber);
    }catch(Exception $e){
       echo $e->toString();
       exit;
    }


    // Record the assignment in our temp assignment table
    $query = "UPDATE hms_temp_assignment SET banner_id = :bannerId where room_number = :roomNumber";
    $stmt = $db->prepare($query);
    try {
        $stmt->execute(array(
                    'bannerId' => $app['banner_id'],
                    'roomNumber' => $roomNumber
                ));
    }catch(\Exception $e){
        echo $e->toString();
        exit;
    }

    // Check for an existing meal plan
    $mealPlan = MealPlanFactory::getMealByBannerIdTerm($app['banner_id'], $term);

    if($mealPlan === null){
        echo "Creating & queuing meal plan.\n";
        $plan = MealPlanFactory::createPlan($student, $term, $applicationObj);
        MealPlanFactory::saveMealPlan($plan);
    }
}


/******************************************************/
?>
