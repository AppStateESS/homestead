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
        require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/axp.php');

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
        $db = &new PHPWS_DB('hms_deadlines');
        $db->addColumn('student_login_begin_timestamp');
        $db->addColumn('student_login_end_timestamp');
        $result = $db->select('row');

        if(time() < $result['student_login_begin_timestamp']) {
            return TOOEARLY;
        } else if (time() > $result['student_login_end_timestamp']) {
            return TOOLATE;
        }

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $student_type = HMS_SOAP::get_student_type($_REQUEST['asu_username']);
        $dob = explode('-', HMS_SOAP::get_dob($_REQUEST['asu_username']));
        
        /* 
        if($student_type != 'F') {
            return BADCLASS;
        } else if ($dob[0] < date('Y') - 25) {
            return TOOOLD;
        }
        */
       
        require_once(PHPWS_SOURCE_DIR . '/mod/hms/inc/accounts.php');
        Current_User::loginUser(HMS_STUDENT_USER, HMS_STUDENT_PASS);
        Current_User::getLogin();
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
