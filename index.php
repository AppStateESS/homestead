<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

//PHPWS_Core::initModClass('hms', 'HMS_Util.php');

//Layout::addStyle('hms','css/hms.css');

PHPWS_Core::initModClass('hms', 'HMSFactory.php');
$controller = HMSFactory::getHMS();
$controller->process();
?>
