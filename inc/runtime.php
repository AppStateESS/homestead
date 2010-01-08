<?php

/*
if(!isset($_REQUEST['module']) && !Current_User::isLogged()) {
    HMS_Login::display_login_screen();
} else if (!isset($_REQUEST['module'])) {
    $_REQUEST['module'] = 'hms';
}
*/

if (PHPWS_Core::atHome()) {
    $path = $_SERVER['SCRIPT_NAME'].'?module=hms';

    header('HTTP/1.1 303 See Other');
    header("Location: $path");
    exit();
}

?>
