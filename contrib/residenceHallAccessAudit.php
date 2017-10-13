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

// Make sure this can only be run from the command line
if(php_sapi_name() !== 'cli'){
    die('This script can only be run from the command line.');
}

// Expected arguments
$args = array('phpwsConfigPath'=>'',
              'module' => '',
              'phpwsUser' => '',
              'term' => '',
              'inputFileName' => '',
              'outputFileName' => '');
$switches = array();

// Process arguments into $args
check_args($argc, $argv, $args, $switches);

$phpwsUser  = $args['phpwsUser'];
$term       = $args['term'];
$inputFileName  = $args['inputFileName'];
$outputFileName = $args['outputFileName'];

// Try to include the main phpws config file
includePhpwsConfigFile($args['phpwsConfigPath']);

// Change current dir to the root of phpws' install
chdir(PHPWS_SOURCE_DIR);

// Define these to avoid errors in sanity checking later
$_SERVER['REQUEST_URI'] = 'cli.php';
$_SERVER['HTTP_HOST'] = 'localhost';
//$_SERVER['SERVER_NAME'] = 'aa.ess'; // NB: Pass SERVER_NAME="blah.blah" on the command line *before* the php executable

// Include PHPWS bootstrapping code
require_once PHPWS_SOURCE_DIR . 'config/core/source.php';
require_once PHPWS_SOURCE_DIR . 'src/Bootstrap.php';

// Set the 'module' request variable to help the autoloader find classes
$_REQUEST['module'] = $args['module'];

\PHPWS_Core::initModClass('users', 'Users.php');
\PHPWS_Core::initModClass('users', 'Current_User.php');

// Log in the requested user
$userId = \PHPWS_DB::getOne("SELECT id FROM users WHERE username = '$phpwsUser'");
$user = new \PHPWS_User($userId);
// Uncomment for production on branches

//$user->auth_script = 'shibboleth.php';
//$user->auth_name = 'shibboleth';
$user->auth_script = 'local.php';
$user->auth_name = 'local';

//$user->login();
$user->setLogged(true);
\Current_User::loadAuthorization($user);
//\Current_User::init($user->id);
$_SESSION['User'] = $user;

// Include more HMS specific stuff
require_once './mod/hms/inc/defines.php';
require_once './inc/hms_defines.php';
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
