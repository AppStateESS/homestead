<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

require_once(PHPWS_SOURCE_DIR . '/mod/hms/inc/accounts.php');
require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

Layout::addStyle('hms','hms.css');

if(Current_User::isLogged()) {
    PHPWS_Core::initModClass('hms', 'HMS.php');

    if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'top_level'){
        switch($_REQUEST['op']){
            case 'go_back':
                PHPWS_Core::goBack();
                break;
        }
    }
    
    HMS::main();
} else if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'hms' && $_REQUEST['op'] == 'login') {
    PHPWS_Core::initModClass('hms', 'HMS_Login.php');
    $type = NULL;
    $type = HMS_Login::login_user();
    
    if($type == BADTUPLE) {
        $error = "<i><h2>You have not entered a valid username/password combination!</h2></i>";
        HMS_Login::display_login_screen($error);
    } else if (isset($type) && is_string($type)) {
        Layout::add($type);
    } else {
        PHPWS_Core::initModClass('hms', 'HMS.php');
        HMS::main($type);
    }
} else {
    PHPWS_Core::initModClass('hms', 'HMS_Login.php');
    HMS_Login::display_login_screen();
}

?>
