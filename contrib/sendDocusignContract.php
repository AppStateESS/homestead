#!/usr/bin/php
<?php

/**
 * Sends docusign contracts to the list of students given (by
 * their Banner IDs) in the input file.
 *
 * TODO: Update this to use the ContractFactory class's central methods.
 * TODO: Add logging entries
 */

ini_set('display_errors', 1);
ini_set('ERROR_REPORTING', E_WARNING);
error_reporting(E_ALL);

require_once('cliCommon.php');

$args = array('phpwsPath' => '',
                'phpwsUser' => '',
                'bannerIdListFile' => '',
                'term' => '');

$switches = array();
check_args($argc, $argv, $args, $switches);

$phpwsPath  = $args['phpwsPath'];
$phpwsUser  = $args['phpwsUser'];
$term       = $args['term'];
$bannerIdFile   = $args['bannerIdListFile'];

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
// require_once $phpwsPath . 'core/conf/defines.php';
// require_once $phpwsPath . 'Global/Functions.php';
// require_once $phpwsPath . 'Global/Implementations.php';
// require_once $phpwsPath . 'config/core/source.php';

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
PHPWS_Core::initModClass('hms', 'ContractFactory.php');
PHPWS_Core::initModClass('hms', 'Docusign/EnvelopeFactory.php');


// Create a DocusignClient object and Guzzle HTTP client
$docusignClient = DocusignClientFactory::getClient();
$http = new \Guzzle\Http\Client();


// Get lines in input file
$bannerIds = file($bannerIdFile);

// Get a term object
$termObj = new Term($term);

// Get the configured template IDs for the given term
$templateId = $termObj->getDocusignTemplate();
$under18TemplateId = $termObj->getDocusignUnder18Template();

foreach ($bannerIds as $bannerId){
    // Get the student object
    $student = StudentFactory::getStudentByBannerId($bannerId, $term);

    sendContractToStudent($student, $term, $docusignClient, $http, $templateId, $under18TemplateId);

    echo "Sent contract for $bannerId\n";
}

function sendContractToStudent($student, $term, $docusignClient, $http, $templateId, $under18TemplateId)
{
    $under18 = $student->isUnder18();

    $templateRoles = array(
        array(
            "roleName" => 'Student',
            "email" => $student->getEmailAddress(),
            "name" => $student->getLegalName()
            //"clientUserId" => $student->getBannerId()
        )
    );

    // If student is under 18, then add parent role to list of signers
    if ($under18) {
        // TODO: Get parent name/email from housing application
        /*
        $parentName = $context->get('parentName');
        $parentEmail = $context->get('parentEmail');

        $templateRoles[] = array(
            "roleName" => 'Parent',
            "email" => $parentEmail,
            "name" => $parentName
                //"clientUserId" => $student->getBannerId()
        );
        */
        echo "Under 18 contracts not supported yet.\n";
        return;
    }

    // Check for an existing contract
    $contract = ContractFactory::getContractByStudentTerm($student, $term);

    if ($contract === false) {
        // Create a new envelope and save it
        if ($under18) {
            // If student is under 18, use the template with parent signatures
            $envelope = Docusign\EnvelopeFactory::createEnvelopeFromTemplate($docusignClient, $under18TemplateId, "University Housing Contract - $term", $templateRoles, 'sent', $student->getBannerId());
        } else {
            // Student is over 18, so use the 1-signature template (without a parent signature)
            $envelope = Docusign\EnvelopeFactory::createEnvelopeFromTemplate($docusignClient, $templateId, "University Housing Contract - $term", $templateRoles, 'sent', $student->getBannerId());
        }

        // Create a new contract to save the envelope ID
        $contract = new Contract($student, $term, $envelope->getEnvelopeId(), $envelope->getStatus(), strtotime($envelope->getStatusDateTime()));
        ContractFactory::save($contract);
    } else {
        // Use the existing envelope id
        $envelope = Docusign\EnvelopeFactory::getEnvelopeById($docusignClient, $contract->getEnvelopeId());
    }
}
