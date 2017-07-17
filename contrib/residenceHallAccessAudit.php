#!/usr/bin/php
<?php

/**
 * Compares a csv file (supplied by Food Services) to current assignments.
 * Denotes valid assignments that *should* have access, so we can investigate anyone else who has access.
 * Add name/assignment info for everyone.
 */

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

require_once('cliCommon.php');

$args = array('phpwsPath' => '',
                'phpwsUser' => '',
                'term' => '',
                'inputFileName' => '',
                'outputFileName' => '',
                'hallCode' => ''
            );

$switches = array();
check_args($argc, $argv, $args, $switches);

$phpwsPath  = $args['phpwsPath'];
$phpwsUser  = $args['phpwsUser'];
$term       = $args['term'];
$inputFileName  = $args['inputFileName'];
$outputFileName = $args['outputFileName'];
$hallCode = $args['hallCode'];

require_once($phpwsPath . 'mod/hms/contrib/dbConnect.php');

require_once $phpwsPath . 'config/core/config.php';
define('DEFAULT_LANGUAGE', 'en_US');
define('CURRENT_LANGUAGE', 'en_US');
//require_once 'src/Bootstrap.php';

// For older versions of PHPWS, comment this out
require_once $phpwsPath . 'src/Autoloader.php';
require_once $phpwsPath . 'src/Translation.php';
require_once $phpwsPath . 'src/Log.php';

// For older versions of PHPWS, uncomment these
//require_once $phpwsPath . 'core/conf/defines.php';
require_once $phpwsPath . 'Global/Functions.php';
//require_once $phpwsPath . 'Global/Implementations.php';
//require_once $phpwsPath . 'config/core/source.php';
require_once $phpwsPath. 'src/phpws/config/defines.php';

// Composer autoloader for Homestead
require_once $phpwsPath . 'mod/hms/vendor/autoload.php';

// Load Users classes and classes needed for logging in
PHPWS_Core::initModClass('users', 'Users.php');
PHPWS_Core::initModClass('users', 'Current_User.php');
PHPWS_Core::initModClass('users', 'Authorization.php');

// Fake a phpws user login
$user = new PHPWS_User(0, $phpwsUser);
//Current_User::loadAuthorization($user);
Current_User::init($user->id);
$_SESSION['User']->_logged = true;
$_SESSION['User']->username = $phpwsUser;


// Connect to the database
/*
$db = connectToDb();
if(!$db){
    die('Could not connect to database.\n');
}
*/

// Include more HMS specific stuff
require_once $phpwsPath . 'mod/hms/inc/defines.php';
require_once $phpwsPath . 'inc/hms_defines.php';
PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'Term.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');

// Open the input file, read into an array
$lines = file($inputFileName);

// Open output file, for writing by fputcsv later
$outputFile = fopen($outputFileName, 'w');

// Loop over each line in the file
foreach ($lines as $line){

    // Initalize an array for this line of output
    $outputFields = array();

    // Break the row apart info separate fields
    $fields = explode(',', $line);

    echo "Banner Id: {$fields[0]}\n";

    // Get a student object, given the banner id (file the input file)
    //$student = StudentFactory::getStudentById($fields[0], $term);

    // Add basic data to file output
    //$outputFields[] = $fields[0]; // Banner ID
    //$outputFields[] = $student->getFirstName(); // Name
    //$outputFields[] = $student->getLastName();

    // Get an assignment using the student's banner ID and term. Returns null if not assigned.
    //$assignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $term);

    /*
    if($assignment !== null){
        // Student is assigned..
        // TODO compare assignments (if match then continue)
    } else {
        // Student is not assigned...
        // TODO
    }*/

    // Get a pending room change using the Student object and term. Returns null if no room change is pending for this student.
    //$pendingRoomChange = RoomChangeRequestFactory::getPendingByStudent($student, $term);

    // To skip this row
    //continue;

    // Write this row to the outputfile
    fputcsv($outputFile, $outputFields);
}
