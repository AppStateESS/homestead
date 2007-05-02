<?php
ini_set(ERROR_REPORTING, E_ALL);
ini_set(display_errors, 0);

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}
if(Current_User::isLogged()) {
    PHPWS_Core::initModClass('hms', 'HMS.php');
    HMS::main();
} else if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'hms' && $_REQUEST['op'] == 'login') {
    require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');
    PHPWS_Core::initModClass('hms', 'HMS_Login.php');
    $type = NULL;
    $type = HMS_Login::login_user();
    
    if ($type == TOOEARLY) {
        $error = "<i><h2>It is too early for students to login.</h2></i>";
        HMS_Login::display_login_screen($error);
    } else if ($type == TOOLATE) {
        $error = "<i><h2>It is too late for students to login.<br />Please contact Housing and Residence Life at http://housing.appstate.edu.</h2></i>";
        HMS_Login::display_login_screen($error);
    } else if($type == BADTUPLE) {
        $error = "<i><h2>You have not entered a valid username/password combination!</h2></i>";
        HMS_Login::display_login_screen($error);
    } else if ($type == TOOOLD) {
        $error = "<i><h2>You must be under 23 to live in a Residence Hall.<br />";
        $error .= "Please contact Housing and Residence Life about living in Mountaineer Apartments.</h2></i>";
        HMS_Login::display_login_screen($error);
    } else if ($type == BADCLASS) {
        $error = "<i><h2>You are not a Freshman.<br />";
        $error .= "We are currently restricting access to HMS to freshman.<br />";
        $error .= "If you need to contact Housing and Residence Life please visit http://housing.appstate.edu</h2></i>";
        HMS_Login::display_login_screen($error);
    } else {
        PHPWS_Core::initModClass('hms', 'HMS.php');
        HMS::main($type);
    }
} else if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'roommate_approval' && 
           isset($_REQUEST['op']) && PHPWS_Text::isValidInput($_REQUEST['op']) &&
           isset($_REQUEST['hash']) && PHPWS_Text::isValidInput($_REQUEST['hash']) &&
           isset($_REQUEST['user']) && PHPWS_Text::isValidInput($_REQUEST['user'])) {
    PHPWS_Core::initModClass('hms', 'HMS_Roommate_Approval.php');
    if($_REQUEST['op'] == 'student_approval') {
        $success = HMS_Roommate_Approval::student_approve_roommates($_REQUEST['user'], $_REQUEST['hash']);
    } else if ($_REQUEST['op'] == 'student_denial') {
        $success = HMS_Roommate_Approval::student_deny_roommates($_REQUEST['user'], $_REQUEST['hash']);
    }
    Layout::add($success);
} else {
    PHPWS_Core::initModClass('hms', 'HMS_Login.php');
    HMS_Login::display_login_screen();
}

?>
