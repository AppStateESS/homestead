<?php

namespace Homestead;

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

// Require the polyfill for getallheaders(), since this doesn't exist in PHP-FPM
// See: https://github.com/php/php-src/pull/910
//require_once(PHPWS_SOURCE_DIR . 'mod/hms/vendor/ralouphie/getallheaders/src/getallheaders.php');
// Could we just include vendor/autoload.php?
require_once(PHPWS_SOURCE_DIR . 'mod/hms/vendor/autoload.php');

require_once(PHPWS_SOURCE_DIR . 'mod/hms/HomesteadAutoLoader.php');
spl_autoload_register(array('HomesteadAutoLoader', 'HomesteadLoader'));

$controller = HMSFactory::getHMS();
$controller->process();
