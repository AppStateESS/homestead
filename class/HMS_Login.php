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
        $deadlines = HMS_Deadlines::get_deadlines();
        
        if(!HMS_Deadlines::check_deadline_past('student_login_begin_timestamp', $deadlines)) {
            return TOOEARLY;
        } else if (HMS_Deadlines::check_deadline_past('student_login_end_timestamp', $deadlines)) {
            return TOOLATE;
        }

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $student_type = HMS_SOAP::get_student_type($_REQUEST['asu_username']);
        $dob = explode('-', HMS_SOAP::get_dob($_REQUEST['asu_username']));
        
        /* Only allow freshmen to sign in
        if($student_type != 'F') {
            return BADCLASS;
        */

        # Return an error if the user is 25 years or older
        if($dob[0] < date('Y') - 25) {
            return TOOOLD;
        }
       
        require_once(PHPWS_SOURCE_DIR . '/mod/hms/inc/accounts.php');
        Current_User::loginUser(HMS_STUDENT_USER, HMS_STUDENT_PASS);
        Current_User::getLogin();

        # Log the student's login in their activity log
        PHPWS_Core::initModClass('hms','HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($_REQUEST['asu_username'],ACTIVITY_LOGIN, $_REQUEST['asu_username'], NULL); 

        # Setup the session variable
        $_SESSION['asu_username'] = $_REQUEST['asu_username'];
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
