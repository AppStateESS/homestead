<?php

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

class HMS_Login
{
    function display_login_screen($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        if($error != NULL) {
            $form->set_error_msg($error);
        }
        $final = $form->display_login_screen();
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
        require_once(PHPWS_SOURCE_DIR . '/mod/hms/inc/accounts.php');
        Current_User::loginUser(HMS_ADMIN_USER, HMS_ADMIN_PASS);
        Current_User::getLogin();
        $_SESSION['asu_username'] = $_REQUEST['asu_username'];
        return ADMIN;
    }
};

?>
