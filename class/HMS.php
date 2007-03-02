<?php

   /**
    * Primary HMS class
    * Responsible for farming out tasks to HMS_Admin, HMS_Student
    *
    * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
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
                require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');
                if($type == NULL) {
                    $username = Current_User::getUsername();
                    if($username == 'hms_student') $type = STUDENT;
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

        # Returns the current "housing year". Need to implement according to Housing's specs. Hard coded for now...
        function get_current_year(){
            return 200710;
        }

    }

?>
