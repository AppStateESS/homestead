#!/usr/bin/php
<?php

/**
 * Creates a meal plan based on the Housing Aplication (if it exsits) for the given student.
 * The meal plan is queued, but not directly sent/reported to Banner. You'll probably want to process
 * the meal plan queue after running this.
 */

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

require_once('cliCommon.php');

$args = array('phpwsPath' => '',
                'phpwsUser' => '',
                'bannerId' => '',
                'term' => '');

$switches = array();
check_args($argc, $argv, $args, $switches);

$phpwsPath  = $args['phpwsPath'];
$phpwsUser  = $args['phpwsUser'];
$term       = $args['term'];
$bannerId   = $args['bannerId'];

require_once($phpwsPath . 'mod/hms/contrib/dbConnect.php');

require_once $phpwsPath . 'config/core/config.php';
define('DEFAULT_LANGUAGE', 'en_US');
define('CURRENT_LANGUAGE', 'en_US');
//require_once 'src/Bootstrap.php';

// For older versions of PHPWS, comment this out
//require_once $phpwsPath . 'src/Autoloader.php';
//require_once $phpwsPath . 'src/Translation.php';
//require_once $phpwsPath . 'src/Log.php';

// For older versions of PHPWS, uncomment these
require_once $phpwsPath . 'core/conf/defines.php';
require_once $phpwsPath . 'Global/Functions.php';
require_once $phpwsPath . 'Global/Implementations.php';
require_once $phpwsPath . 'config/core/source.php';

require_once $phpwsPath . 'mod/hms/vendor/autoload.php';
require_once $phpwsPath . 'mod/hms/class/DocusignClientFactory.php';
require_once $phpwsPath . 'mod/hms/class/Docusign/Client.php';

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
$db = connectToDb();
if(!$db){
    die('Could not connect to database.\n');
}


// Include more HMS specific stuff
require_once $phpwsPath . 'mod/hms/inc/defines.php';
require_once $phpwsPath . 'inc/hms_defines.php';
PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'Term.php');
PHPWS_Core::initModClass('hms', 'MealPlanFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');


$existingMealPlan = MealPlanFactory::getMealByBannerIdTerm($bannerId, $term);

$student = StudentFactory::getStudentByBannerID($bannerId, $term);

if($existingMealPlan === null){
    $application = HousingApplicationFactory::getAppByStudent($student, $term);

    $plan = MealPlanFactory::createPlan($student, $term, $application);
    MealPlanFactory::saveMealPlan($plan);

    echo "Created and queued meal plan.\n";
} else {
    echo "Meal Plan exists!\n";
}
