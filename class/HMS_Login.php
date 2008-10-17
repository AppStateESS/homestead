<?php

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

class HMS_Login
{
    function display_login_screen($error = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addText('asu_username');
        $form->addPassword('password');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hms');
        $form->addHidden('op', 'login');
        $form->addSubmit('submit', _('Login'));

        $tpl = $form->getTemplate();
        $welcome  = "Welcome to the Housing Management System.<br /><br />";
        $welcome .= "There are multiple parts to this process. These are:<br />";
        $welcome .= " - Logging in<br />";
        $welcome .= " - Agreeing to the Housing License Contract<br />";
        $welcome .= " - Completing a Housing Application<br />";
        $welcome .= " - Completing the Residential Learning Community Application if you wish to participate in a RLC<br />";
        $welcome .= " - Completing the *OPTIONAL* student profile<br /><br />";
        $welcome .= "Please note that once you complete the Housing Application you do not have to fill out anything else provided at this website.<br /><br />";
      
        $welcome .= "<br /><br />";
        $welcome .= "<b>If you are experiencing problems please read <a href=\"./index.php?module=webpage&id=1\" target=\"_blank\">this page</a>.</b>";
        $welcome .= "<br /><br />";

        $values = array('ADDITIONAL'=>'The Housing Management System will <strong>not</strong> work without cookies.  Please read about <a href="http://www.google.com/cookies.html" target="_blank">how to enable cookies</a>.');
        $tpl['COOKIE_WARNING'] = Layout::getJavascript('cookietest', $values);
        $tpl['WELCOME'] = $welcome;
        $tpl['ERROR']   = $error;
        $final = PHPWS_Template::process($tpl, 'hms', 'misc/login.tpl');

        Layout::add($final);
    }

    function login_user()
    {
        if(!file_exists(AXP_LOCATION)) {
            return "A critical error has occurred in the Housing Management System.  Please tell Electronic Student Services that their AXP driver is missing.";
        }

        require_once(PHPWS_SOURCE_DIR . AXP_LOCATION);

        if($type = axp_authorize($_REQUEST['asu_username'], $_REQUEST['password'])) {
            return HMS_Login::student_login();
        } else {
            if(Current_User::loginUser($_REQUEST['asu_username'], $_REQUEST['password'])){
                Current_User::getLogin();
                return ADMIN;
            } else {
                return BADTUPLE;
            }
        }
        
    }

    function student_login()
    {
        PHPWS_Core::initModClass('hms','HMS_Deadlines.php');
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $deadlines = HMS_Deadlines::get_deadlines();

        /* Don't destroy our admin session if an admin is logging in as a user */
        if( !Current_User::isLogged() ) {
            require_once(PHPWS_SOURCE_DIR . '/mod/hms/inc/accounts.php');
            Current_User::loginUser(HMS_STUDENT_USER, HMS_STUDENT_PASS);
            Current_User::getLogin();
        }

        $username = strtolower(trim($_REQUEST['asu_username']));

        # Log the student's login in their activity log
        PHPWS_Core::initModClass('hms','HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($username,ACTIVITY_LOGIN, $username, NULL); 

        # Setup the session variable
        $_SESSION['asu_username'] = $username;
        $_SESSION['application_term'] = HMS_SOAP::get_application_term($username);
        return STUDENT;
    }

    function admin_login()
    {
        Current_User::loginUser(HMS_ADMIN_USER, HMS_ADMIN_PASS);
        Current_User::getLogin();
        $_SESSION['asu_username'] = $_REQUEST['asu_username'];
        return ADMIN;
    }
};

?>
