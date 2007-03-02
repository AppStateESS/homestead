<?php

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
    } else {
        PHPWS_Core::initModClass('hms', 'HMS.php');
        HMS::main($type);
    }

} else {

    PHPWS_Core::initModClass('hms', 'HMS_Login.php');
    HMS_Login::display_login_screen();

}

?>
