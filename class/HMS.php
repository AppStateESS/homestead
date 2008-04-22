<?php

/**
 * Primary HMS class
 * Responsible for farming out tasks to HMS_Admin, HMS_Student
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 * @modified Matthew McNaney
 * @modified Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS 
{
    function main($type = NULL)
    {
        if(!Current_User::isLogged()) {
            $error = "<i><font color=red>Please enter a valid username/password pair.</font></i>";
            PHPWS_Core::initModClass('hms', 'HMS_Login.php');
            HMS_Login::display_login_screen($error);
        } else {
            $username = Current_User::getUsername();
            require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');
            if( isset($_REQUEST['login_as_student']) || isset($_SESSION['login_as_student']) ) {
                if( $type == ADMIN || Current_User::allow('hms', 'login_as_student') ) {
                    if( isset($_REQUEST['login_as_student']) ) {
                        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
                        PHPWS_Core::initModClass('hms', 'HMS_Activity_log.php');
                        $_SESSION['login_as_student'] = true;
                        $_REQUEST['asu_username']     = $_REQUEST['login_as_student'];
                        HMS_Login::student_login();
                        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_LOGIN_AS_STUDENT, Current_User::getUsername(), '');
                    } else if( isset($_REQUEST['end_student_session']) ) { 
                        unset($_SESSION['login_as_student']);
                        unset($_SESSION['asu_username']);
                        unset($_SESSION['application_term']);
                        header('Location: index.php?module=hms&type=maintenance&op=show_maintenance_options');
                        exit;
                    }
                    Layout::add('<table width=100%><tr><td bgcolor=#fa1515><center><h1><a href=index.php?module=hms&end_student_session=true>Logout of student Session</a></h1></center></td></tr></table>');
                } else {
                    # Someone is being naughty...
                    //exit();
                    unset($_SESSION);
                    header('Location: index.php');
                    exit;
                }
            }
                
            if($type == NULL) {
                if( $username == 'hms_student' || (Current_User::allow('hms', 'login_as_student') && isset($_SESSION['login_as_student'])) ) $type = STUDENT;
                else $type = ADMIN;
            }

            switch($type)
                {
                case STUDENT:
                    PHPWS_Core::initModClass('hms', 'HMS_Student.php');
                    $content = HMS_Student::main();
                    break;
                case ADMIN:
                    PHPWS_Core::initModClass('hms', 'HMS_Admin.php');
                    $content = HMS_Admin::main();
                    break;
                default:
                    $content = "wtf?";
                    break;
                }
            Layout::add($content);
        }
    }
}
    

?>
