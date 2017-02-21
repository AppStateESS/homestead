#!/usr/bin/php
<?php

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
PHPWS_Core::initModClass('hms', 'ContractFactory.php');
PHPWS_Core::initModClass('hms', 'Docusign/EnvelopeFactory.php');
PHPWS_Core::initModClass('hms', 'DocusignClientFactory.php');


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
    $bannerId = trim($bannerId);

    // Get the student object
    $student = StudentFactory::getStudentByBannerId($bannerId, $term);

    echo "Checking for contract for $bannerId: ";

    $result = voidDocusignContract($student, $term, $docusignClient, $http);
}

function voidDocusignContract($student, $term, $docusignClient, $http)
{
    // Check for an existing contract
    $contract = ContractFactory::getContractByStudentTerm($student, $term);

    if ($contract !== false) {
        // Contract exists, so void it

        $contract->updateEnvelope();

        $status = $contract->getEnvelopeStatus();

        // If the status is not 'sent' of 'delivered' then we cannot void it
        if($status !== 'sent' && $status !== 'delivered'){
            echo "Cannot cancel because status is: $status\n";
            return;
        }

        // Get the corresponding envelope id
        $envelope = Docusign\EnvelopeFactory::getEnvelopeById($docusignClient, $contract->getEnvelopeId());

        // Send the REST API request to void the envelope
        $envelope->voidEnvelope($docusignClient, 'Voided, per request of University Housing');

        // Update the contract in the local db
        //$contract->setEnvelopeStatus('voided');
        //$contract->setEnvelopeStatusTime(time());

        //ContractFactory::save($contract);

        echo "Voided\n";
    } else {
        echo "No envelope found\n";
    }
}
