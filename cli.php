#!/usr/bin/php
<?php
namespace Homestead;

// Make sure this can only be run from the command line
if(php_sapi_name() !== 'cli'){
    die('This script can only be run from the command line.');
}

// Expected arguments
$args = array('phpwsConfigPath'=>'',
              'module' => '',
              'className'=>'');
$switches = array();

// Process arguments into $args
processArgs($argc, $argv, $args, $switches);

// Try to include the main phpws config file
includePhpwsConfigFile($args['phpwsConfigPath']);

// Change current dir to the root of phpws' install
chdir(PHPWS_SOURCE_DIR);

// Define these to avoid errors in sanity checking later
$_SERVER['REQUEST_URI'] = 'cli.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'homestead.ess.appstate.edu'; // NB: Pass SERVER_NAME="blah.blah" on the command line *before* the php executable

// Include PHPWS bootstrapping code
require_once PHPWS_SOURCE_DIR . 'config/core/source.php';
require_once PHPWS_SOURCE_DIR . 'src/Bootstrap.php';

require_once PHPWS_SOURCE_DIR . 'inc/hms_defines.php';
require_once PHPWS_SOURCE_DIR . 'inc/SOAPDataOverride.php';
require_once PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php';
require_once PHPWS_SOURCE_DIR . 'mod/hms/vendor/autoload.php';

require_once PHPWS_SOURCE_DIR . 'mod/hms/HomesteadAutoLoader.php';
spl_autoload_register(array('HomesteadAutoLoader', 'HomesteadLoader'));

// Set the 'module' request variable to help the autoloader find classes
$_REQUEST['module'] = $args['module'];

// Try to include and run the specified file/function
try {
    $className = $args['className'];
    $classNameWithNS = '\Homestead\Scheduled\\' . $args['className'];

    \PHPWS_Core::initModClass('hms', 'Scheduled/' . $className . '.php');

    $classNameWithNS::cliExec();

}catch (\Exception $e) {
    echo "Error:\n";
    echo $e->getMessage();
    print_r($e);
    echo "\n\n";
}

function processArgs($argc, $argv, &$args, &$switches)
{
    if($argc < count(array_keys($args)) + 1) {
        echo "USAGE: php {$argv[0]}";

        foreach(array_keys($switches) as $switch) {
            echo " [$switch]";
        }

        foreach(array_keys($args) as $arg) {
            echo " <$arg>";
        }

        echo "\n";
        exit();
    }

    $args_keys = array_keys($args);
    foreach($argv as $arg) {
        if($arg == $argv[0]) continue;

        if(in_array($arg, array_keys($switches))) {
            $switches[$arg] = true;
            continue;
        }

        if(substr($arg,0,1) == '-') {
            echo "Ignoring unknown switch: $arg\n";
            continue;
        }

        $args[current($args_keys)] = $arg;
        next($args_keys);
    }
}

function includePhpwsConfigFile($filePath)
{
    if (!is_file($filePath)) {
        exit("Configuration file not found: $filePath\n");
    }

    require_once $filePath;

    if (!defined('PHPWS_DSN')) {
        exit("Configuration file loaded, but database connection string (DSN) not found\n");
    }
}
