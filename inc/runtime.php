<?php

if(!isset($_REQUEST['module']) && !Current_User::isLogged()) {
    HMS_Login::display_login_screen();
} else if (!isset($_REQUEST['module'])) {
    $_REQUEST['module'] = 'hms';
}

?>
