<?php
class HMS_Student {
    var $id;
    var $first_name;
    var $middle_name;
    var $last_name;
    var $asu_username;
    var $gender;
    var $deleted;
    var $application_received;
    var $added_by;
    var $added_on;
    var $deleted_by;
    var $deleted_on;
    var $updated_by;
    var $updated_on;
    var $deleted;

    function HMS_Student($student_id = NULL) {
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_first_name($first)
    {
        $this->first_name = $first;
    }

    function get_first_name()
    {
        return $this->first_name;
    }

    function set_middle_name($middle)
    {
        $this->middle_name = $middle;
    }

    function get_middle_name()
    {
        return $this->middle_name;
    }

    function set_last_name($last)
    {
        $this->last_name = $last;
    }

    function get_last_name()
    {
        return $this->last_name;
    }

    function set_full_name($first, $middle, $last)
    {
        $this->set_first_name($first);
        $this->set_middle_name($middle);
        $this->set_last_name($last);
    }

    function get_full_name()
    {
        return $this->get_first_name() . " " . $this->get_middle_name() . " " . $this->get_last_name();
    }

    function set_asu_username($email)
    {
        $this->asu_username = $email;
    }

    function get_asu_username()
    {
        return $this->asu_username;
    }

    function set_gender($gender)
    {
        $this->gender = $gender;
    }

    function get_gender()
    {
        return $this->gender;
    }

    function set_added_by($id)
    {
        $this->added_by = $id;
    }

    function get_added_by()
    {
        return $this->added_by;
    }

    function set_added_on($ts)
    {
        $this->added_on = $ts;
    }

    function get_added_on()
    {
        return $this->added_on;
    }

    function set_updated_by($id)
    {
        $this->updated_by = $id;
    }

    function get_updated_by()
    {
        return $this->updated_by;
    }

    function set_updated_on($ts)
    {
        $this->updated_on = $ts;
    }

    function get_updated_on()
    {
        return $this->updated_on;
    }

    function set_variables()
    {
        if(isset($_REQUEST['id'])) {
            $this->set_id($_REQUEST['id']);
        } else {
            $this->set_added_by(Current_User::getId());
            $this->set_added_on(time());
        }

        $this->set_first_name($_REQUEST['first_name']);
        $this->set_middle_name($_REQUEST['middle_name']);
        $this->set_last_name($_REQUEST['last_name']);
        $this->set_asu_username($_REQUEST['asu_username']);
        $this->set_gender($_REQUEST['gender']);
        $this->set_updated_by(Current_User::getId());
        $this->set_updated_on(time());
    }
       
    function add_student()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $student = &new HMS_Form;
        $content = $student->fill_student_data();
        return $content;
    }

    function save_student()
    {
        $student = &new HMS_Student;
        $db = &new PHPWS_DB('hms_student');
        if(isset($_REQUEST['id'])) {
            $student->set_id($_REQUEST['id']);
            $db->loadObject($student);
        }
        $student->set_variables();
        if(!$student->get_first_name() || !$student->get_last_name() || !$student->get_asu_username()) {
            return $student->edit_student("You need to fill out a first name, last name and asu username!");
        }
        $success = $db->saveObject($student);
        if(PEAR::isError($success)) {
            PHPWS_Error::log($success, 'hms', 'HMS_Student::save_student');
            return "There was a problem saving the student.<br /><br />" . $success; 
        } else {
            return "Student saved successfully!";
        }
    }

    function enter_student_search_data()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $student = &new HMS_Form;
        $content = $student->enter_student_search_data();
        return $content;
    }

    function get_matching_students()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $student = &new HMS_Form;
        $content = $student->get_matching_students();
        return $content;
    }

    function get_row_pager_tags()
    {
        $row['ACTIONS'] = HMS_Student::get_row_actions();
        return $row;
    }

    function get_row_actions()
    {
        $link['type']   = 'student';
        $link['id']     = $this->get_id();
        
        $link['op']     = 'edit_student';
        $list[]         = PHPWS_Text::secureLink(_('Edit'), 'hms', $link);
        
        $link['op']     = 'delete_student';
        $confirm_vars['QUESTION'] = _('Are you sure you ant to delete this student?');
        $confirm_vars['ADDRESS']  = PHPWS_Text::linkAddress('hms', $link, true);
        $confirm_vars['LINK'] = _('Delete');
        $list[] = Layout::getJavascript('confirm', $confirm_vars);

        return implode(' | ', $list);
    }

    function edit_student($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $editor = &new HMS_Form;
        $content = $editor->fill_student_data($error);
        return $content;
    }

    function delete_student()
    {
        $db = &new PHPWS_DB('hms_student');
        $db->addValue('deleted', 1);
        $db->addWhere('id', $_REQUEST['id']);
        $success = $db->update();
        if($success == FALSE || $success == NULL) {
            return "<i>ERROR! ERROR!</i><br />Student " . $_REQUEST['id'] . " could not be marked deleted.<br />";
        } else {
            return "Student has successfully been marked deleted.<br />";
        }
    }

    function get_banner_profile($username, $term)
    {
        include('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $testing = $wsdl->getProxy();
        return $testing->GetStudentProfile($username, $term);
    }

    function main()
    {
        if($_REQUEST['op'] == 'login') {
            $_REQUEST['op'] = 'main';
        }

        switch($_REQUEST['op'])
        {
            case 'add_student':
                return HMS_Student::add_student();
                break;
            case 'save_student':
                return HMS_Student::save_student();
                break;
            case 'enter_student_search_data':
                return HMS_Student::enter_student_search_data();
                break;
            case 'get_matching_students':
                return HMS_Student::get_matching_students();
                break;
            case 'edit_student':
                return HMS_Student::edit_student();
                break;
            case 'delete_student':
                return HMS_Student::delete_student();
                break;
            case 'begin_questionnaire':
                PHPWS_Core::initModClass('hms','HMS_Questionnaire.php');
                return HMS_Questionnaire::display_questionnaire_form();
                break;
            case 'review_questionnaire':
                PHPWS_Core::initModClass('hms','HMS_Questionnaire.php');
                return HMS_Questionnaire::display_questionnaire_form(TRUE);
                break;
            case 'save_questionnaire':
                PHPWS_Core::initModClass('hms','HMS_Questionnaire.php');
                return HMS_Questionnaire::save_questionnaire();
                break;
            case 'show_questionnaire_search':
                PHPWS_Core::initModClass('hms','HMS_Questionnaire.php');
                return HMS_Questionnaire::display_questionnaire_search();
                break;
            case 'questionnaire_search':
                PHPWS_Core::initModClass('hms','HMS_Questionnaire.php');
                return HMS_Questionnaire::questionnaire_search();
                break;
            case 'main':
                $message  = "Welcome to the Housing Management System!<br /><br />";
                
                PHPWS_Core::initModClass('hms', 'HMS_Questionnaire.php');
                if(HMS_Questionnaire::check_for_questionnaire($_SESSION['asu_username'])) {
                    $message .= "You have already completed a Housing Questionnaire. You may click below to review it.<br /><br />";
                    $message .= "You may also submit a new questionnaire. This will replace the one you already have saved.<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('View My Questionnaire'), 'hms', array('type'=>'student', 'op'=>'review_questionnaire'));
                    $message .= "<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Submit New Questionnaire'), 'hms', array('type'=>'student', 'op'=>'begin_questionnaire'));
                    $message .= "<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Search for a roomate'), 'hms', array('type'=>'student','op'=>'show_questionnaire_search'));
                    $message .= "<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));
                } else {
                    $message .= "You have not completed a Housing Questionnaire.<br /><br />";
                    $message .= "Click below to fill out a new questionnaire. <br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Submit new Questionnaire'), 'hms', array('type'=>'student', 'op'=>'begin_questionnaire'));
                    $message .= "<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));
                }       
                return $message;
            default:
                return "{$_REQUEST['op']} <br />";
                break;
        }
    }
};
?>
