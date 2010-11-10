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

/*
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
$rc = RoomChangeRequest::getNew();
$rc->reason = 'Roommate masturbates excessively.';
$rc->cell_number = '867-5309';
$rc->preferences = array(199,200);
$rc->change(new PendingRoomChangeRequest);
$rc->bed_id = 54489;
$rc->change(new RDApprovedChangeRequest);
$rc->change(new HousingApprovedChangeRequest);
$rc->change(new DeniedChangeRequest);
test($rc,1);
*/
?>
