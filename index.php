<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

//PHPWS_Core::initModClass('hms', 'HMS_Util.php');

//Layout::addStyle('hms','css/hms.css');
/*
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
$permission = new HMS_Permission();
$permission->id = 1;
$permission->load();
$floor = new HMS_Floor();
$floor->id = 1;
test($permission->getMembership(null, $floor), 1, true);
test($permission->verify('hms_student', $floor),1);
*/

PHPWS_Core::initModClass('hms', 'HMSFactory.php');
$controller = HMSFactory::getHMS();
$controller->process();
?>
