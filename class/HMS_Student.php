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
        $confirm_vars['QUESTION'] = _('Are you sure you want to delete this student?');
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

    function get_roommate_username()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $content = HMS_Form::get_roommate_username();
        return $content;
    }

    function verify_roommate_username()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $content = HMS_Form::verify_roommate_username($_SESSION['asu_username'], $_REQUEST['username']);
        return $content;
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
            case 'begin_application':
                if(isset($_REQUEST['quit'])) {
                    PHPWS_Core::killAllSessions();
                    PHPWS_Core::home();
                }
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::display_application_form();
                break;
            case 'review_application':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::display_application_form(TRUE);
                break;
            case 'save_application':
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::save_application();
                break;
            case 'show_application_search':
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::display_application_search();
                break;
            case 'application_search':
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::application_search();
                break;
            case 'show_application':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::show_application($_REQUEST['user']);
                break;
            case 'show_profile':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_PROFILE);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms', 'HMS_Student_Profile.php');
                return HMS_Student_Profile::show_profile($_REQUEST['user']);
                break;
            case 'show_rlc_application_form':
                PHPWS_Core::initModClass('hms','HMS_Learning_Community.php');
                return HMS_Learning_Community::show_rlc_application_form();
                break;
            case 'view_rlc_application':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_RLC);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
                return HMS_Learning_Community::view_rlc_application();
                break;
            case 'rlc_application_page1_submit':
                PHPWS_Core::initModClass('hms','HMS_Learning_Community.php');
                return HMS_Learning_Community::rlc_application_page1_submit();
                break;
            case 'rlc_application_page2_submit':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_RLC);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms','HMS_Learning_Community.php');
                return HMS_Learning_Community::rlc_application_page2_submit();
                break;
            case 'show_profile_form':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_PROFILE);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms','HMS_Student_Profile.php');
                return HMS_Student_Profile::show_profile_form();
                break;
            case 'student_profile_submit':
                PHPWS_Core::initModClass('hms','HMS_Student_Profile.php');
                return HMS_Student_Profile::submit_profile();
                break;
            case 'get_roommate_username':
                return HMS_Student::get_roommate_username();
                break;
            case 'verify_roommate_username':
                return HMS_Student::verify_roommate_username();
                break;
            case 'main':
                PHPWS_Core::initModClass('hms', 'HMS_Application.php');
                if(HMS_Application::check_for_application($_SESSION['asu_username'])) {
                    PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                    // TODO: This will have to check whether a profile has been made
                    // or not, and then whether a roommate has been selected or not:
                    // if not profile, then HMS_SIDE_STUDENT_PROFILE
                    // if not roommate, then HMS_SIDE_STUDENT_ROOMMATE
                    // else, HMS_SIDE_STUDENT_VERIFY
                    $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_PROFILE);
                    $side_thingie->show();

                    $message  = "Welcome to the Housing Management System!<br /><br />";
                    $message .= "You have already completed a Housing Application. You may click below to review it.<br /><br />";
                    $message .= "You may also submit a new application. This will replace the one you already have saved.<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('View My Application'), 'hms', array('type'=>'student', 'op'=>'review_application'));
                    $message .= "<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Submit New Application'), 'hms', array('type'=>'student', 'op'=>'begin_application'));
                    $message .= "<br /><br />";
                    
                    PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
                    if(HMS_RLC_Application::check_for_application() === FALSE){
                        $message .= PHPWS_Text::secureLink(_('RLC Application Form'), 'hms', array('type'=>'student', 'op'=>'show_rlc_application_form'));
                        $message .="<br /><br />";
                    } else {
                        $message .= PHPWS_Text::secureLink(_('View My RLC Application'), 'hms', array('type'=>'student', 'op'=>'view_rlc_application'));
                        $message .= "<br /><br />";
                    }

                    $message .= PHPWS_Text::secureLink(_('Create your Profile'), 'hms', array('type'=>'student', 'op' =>'show_profile_form'));
                    $message .= "<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Select your roommate'), 'hms', array('type'=>'student','op'=>'get_roommate_username'));
                    $message .= "<br /><br />";
                    $message .= "If you do not know who you want to room with, please use our ";
                    $message .= PHPWS_Text::secureLink('roommate search tool.', 'hms', array('type'=>'student','op'=>'show_application_search'));
                    $message .= "<br /><br />";
                    $message .= PHPWS_Text::secureLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));
                    $message .= "<br /><br />";
                } else {
                    PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                    $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_AGREE);
                    $side_thingie->show();
                   
                    $form = new PHPWS_Form;
                    $form->addHidden('module', 'hms');
                    $form->addHidden('type', 'student');
                    $form->addHidden('op', 'begin_application');
                    $form->addSubmit('begin', _('I AGREE'));
                    $form->addSubmit('quit', _('I DISAGREE'));
                    
                    $message  = "<b>Please read the following License Agreement and click either 'I AGREE' or 'I DISAGREE'<br />";
                    $message .= 'Please note that if you click disagree you will be logged out of HMS.</b><br /><br />';
                    $message .= 'If you wish to read this Agreement as a printable PDF please ';
                    $message .= '<a href="http://hms.appstate.edu/files/contract.pdf" target="_blank">click here.</a><br /><br />';
                    $message .= 'If you need to update or download a PDF viewer you can <a href="http://www.adobe.com/products/acrobat/readstep2.html" target="_blank">get one here</a><br /><br />';

                    # Check for under 18, display link to print message
                    PHPWS_Core::initModClass('hms','HMS_SOAP.php');
                    $dob = explode('-', HMS_SOAP::get_dob($_REQUEST['asu_username']));
                    $dob_timestamp = mktime(0,0,0,$dob[1],$dob[2],$dob[0]);
                    $current_timestamp = mktime(0,0,0);
                    if(($current_timestamp - $dob_timestamp) < (3600 * 24 * 365 * 18)){
                        $message .= '<br /><font color="red">Because you are under age 18, you MUST print a copy of the Housing Contract Agreement, ';
                        $message .= 'have a parent or legal guardian sign it, and return it to the Department of ';
                        $message .= 'Housing and Residence Life. Your application cannot be fully processed until a Housing Contract ';
                        $message .= 'signed by a parent or gaurdian is on file. Please <a href="http://hms.appstate.edu/files/contract.pdf">click here </a>';
                        $message .= 'to open a printer-friendly version of the Housing Contract.</font><br /><br />';

                        # Set the 'agreed_to_terms' flag to false
                        $form->addHidden('agreed_to_terms',0);
                    }else{
                        $form->addHidden('agreed_to_terms',1);
                    }
                    
                    $tpl = $form->getTemplate();

                    $tpl['MESSAGE'] = $message;
                    $tpl['CONTRACT'] = str_replace("\n", "<br />", file_get_contents('mod/hms/inc/contract.txt'));
                    
                    $message = PHPWS_Template::process($tpl, 'hms', 'student/contract.tpl');
                }       
                return $message;
            default:
                return "{$_REQUEST['op']} <br />";
                break;
        }
    }
};
?>
