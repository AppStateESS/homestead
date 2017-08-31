<?php

namespace Homestead;

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

require_once(PHPWS_SOURCE_DIR . 'mod/hms/HomesteadAutoLoader.php');
spl_autoload_register(array('HomesteadAutoLoader', 'HomesteadLoader'));

$controller = HMSFactory::getHMS();
$controller->process();
