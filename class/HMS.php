<?php

/**
 * Primary HMS class
 * Responsible for farming out tasks to HMS_Admin, HMS_Student
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 * @modified Matthew McNaney
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

    /**
     * Returns the current "housing year".
     *
     * 10 spring jan - may
     * 20 summer 1 may - june 15
     * 30 summer 2 june 15 - aug
     * 40 fall aug - dec
     * 
     * @author Matthew McNaney
     * @return int Term year
     */
    function get_current_year()
    {
        $today = mktime();
        $year = (int)date('Y');

        $spring_start      = mktime(0,0,0, 1,  1, $year);
        $summer_one_start  = mktime(0,0,0, 5, 15, $year);
        $summer_two_start  = mktime(0,0,0, 7, 1, $year);
        $fall_start        = mktime(0,0,0, 8, 15, $year);
        $next_spring_start = mktime(0,0,0, 1,  1, $year + 1);

        switch (1) {
        case ($today >= $spring_start && $today < $summer_one_start):
            $term = '10';
            break;

        case ($today >= $summer_one_start && $today < $summer_two_start):
            $term = '20';
            break;

        case ($today >= $summer_two_start && $today < $fall_start):
            $term = '30';
            break;

        case ($today >= $fall_start && $today < $next_spring_start):
            $term =  '40';
            break;

        default:
            return false;
        }

        return (int)"$year$term";
    }

}
    

?>
