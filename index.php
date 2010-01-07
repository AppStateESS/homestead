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

} else if(LOGIN_TEST_FLAG == true && isset($_POST['action']) && $_POST['action'] == 'fake_login'){
	test('fake login!');
    PHPWS_Core::initModClass('hms', 'HMS_Login.php');

    $type = null;
    $type = HMS_Login::fake_login($_REQUEST['username']);
    test($type);

    PHPWS_Core::initModClass('hms', 'HMS.php');
    HMS::main($type);

} else if(LOGIN_TEST_FLAG == TRUE && isset($_GET['action']) && $_GET['action'] == 'show_fake_login'){
    PHPWS_Core::initModClass('hms', 'HMS_Login.php');
    HMS_Login::show_fake_login();

} else {
    PHPWS_Core::initModClass('hms', 'HMS_Login.php');
    HMS_Login::display_login_screen();
}
*/
?>
