<?php

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

class HMS_Login
{
    public function display_login_screen($error = NULL)
    {
        $values = array('ADDITIONAL'=>'The Housing Management System will <strong>not</strong> work without having your web browser\'s cookie features enabled.  Please read about <a href="http://www.google.com/cookies.html" target="_blank">how to enable cookies</a>.');
        $tpl['COOKIE_WARNING'] = Layout::getJavascript('cookietest', $values);

        # If the user has cookies enabled (and therefore is not being shown the cookie warning message...
        if(is_null($tpl['COOKIE_WARNING'])){
            $tpl['LOGIN_LINK'] = HMS_LOGIN_LINK; // a dummy tag to make the actual login content show
        }

        Layout::add(PHPWS_Template::process($tpl, 'hms', 'misc/login.tpl'));
    }
};

?>
