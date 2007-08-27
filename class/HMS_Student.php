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
        if(!isset($_REQUEST['username'])) {
            PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
            $error = "You did not provide an ASU username.<br />";
            return HMS_Form::enter_student_search_data($error);
        } else if (!PHPWS_Text::isValidInput($_REQUEST['username'])) {
            PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
            $error = "ASU usernames can only be alphanumeric.<br />";
            return HMS_Form::enter_student_search_data($error);
        } 

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $student_info = HMS_SOAP::get_student_info($_REQUEST['username']);

//        test($student_info);

        $tpl['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to Search'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));
        $tpl['FIRST_NAME'] = $student_info->first_name;
        $tpl['MIDDLE_NAME'] = $student_info->middle_name;
        $tpl['LAST_NAME'] = $student_info->last_name;
        
        if($student_info->gender == 'F') {
            $tpl['GENDER'] = "Female";
        } else if ($student_info->gender == 'M') {
            $tpl['GENDER'] = "Male";
        } else {
            $tpl['GENDER'] = "Unknown ({$student_info->gender})";
        }

        $tpl['DOB'] = $student_info->dob;

        if($student_info->projected_class == 'FR') {
            $tpl['CLASS'] = "Freshman";
        } else if ($student_info->projected_class == 'SO') {
            $tpl['CLASS'] = "Sophomore";
        } else if ($student_info->projected_class == 'JR') {
            $tpl['CLASS'] = "Junior";
        } else if ($student_info->projected_class == 'SR') {
            $tpl['CLASS'] = "Senior";
        } else {
            $tpl['CLASS'] = "Unknown ({$student_info->projected_class})";
        }

        $tpl['ADDRESS_L1'] = $student_info->address->line1;
        $tpl['ADDRESS_L2'] = $student_info->address->line2;
        $tpl['ADDRESS_L3'] = $student_info->address->line3;
        $tpl['ADDRESS_CITY'] = $student_info->address->city;
        $tpl['ADDRESS_STATE'] = $student_info->address->state;
        $tpl['ADDRESS_ZIP'] = $student_info->address->zip;
        $tpl['PHONE_AC'] = $student_info->phone->area_code;
        $tpl['PHONE_NUMBER'] = $student_info->phone->number;
        $tpl['USERNAME'] = $_REQUEST['username'];

        $tpl['TITLE'] = "Search Results";

        $sql  = "SELECT";
        $sql .= " hms_residence_hall.hall_name, ";
        $sql .= " hms_room.room_number, ";
        $sql .= " hms_assignment.id, ";
        $sql .= " hms_assignment.meal_option ";
        $sql .= "FROM";
        $sql .= " hms_residence_hall, ";
        $sql .= " hms_floor, ";
        $sql .= " hms_room, ";
        $sql .= " hms_bedrooms, ";
        $sql .= " hms_beds, ";
        $sql .= " hms_assignment ";
        $sql .= "WHERE";
        $sql .= " hms_assignment.deleted = '0' ";
        $sql .= " AND hms_assignment.bed_id = hms_beds.id ";
        $sql .= " AND hms_beds.bedroom_id = hms_bedrooms.id ";
        $sql .= " AND hms_bedrooms.room_id = hms_room.id ";
        $sql .= " AND hms_room.floor_id = hms_floor.id ";
        $sql .= " AND hms_floor.building = hms_residence_hall.id ";
        $sql .= " AND hms_assignment.asu_username ilike '" . $_REQUEST['username'] . "';";

        $db = &new PHPWS_DB();
        $db->setSQLQuery($sql);
        $results = $db->select();

        if($results != FALSE && $results != NULL) {
            $tpl['ROOM_ASSIGNMENT'] = $results[0]['room_number'] . " " . $results[0]['hall_name'];
            $tpl['MEAL_PLAN'] = '
    <form action="index.php" method="post">
        <input type="hidden" name="module" value="hms" />
        <input type="hidden" name="type" value="student" />
        <input type="hidden" name="op" value="set_meal_plan" />
        <input type="hidden" name="username" value="'.$_REQUEST['username'].'" />
        <input type="hidden" name="assignment_id" value="'.$results[0]['id'].'" />
        <select name="meal_option">';

            $selected[$results[0]['meal_option']] = ' selected="selected"';

            $tpl['MEAL_PLAN'] .= '<option value="0"'.$selected[0].'>Low</option>';
            $tpl['MEAL_PLAN'] .= '<option value="1"'.$selected[1].'>Standard</option>';
            $tpl['MEAL_PLAN'] .= '<option value="2"'.$selected[2].'>High</option>';
            $tpl['MEAL_PLAN'] .= '<option value="3"'.$selected[3].'>Super</option>';
            $tpl['MEAL_PLAN'] .= '<option value="4"'.$selected[4].'>None</option>';
        
            $tpl['MEAL_PLAN'] .= '
        </select>
        <input type="submit" value="Save" />
    </form>';
        } else {
            $tpl['ROOM_ASSIGNMENT'] = "This student does not live on campus.";
        }

        // get roommate or pending roommate
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        if(!HMS_Roommate::has_roommates($_REQUEST['username'])) {
            $tpl['ROOMMATE'] = "This person does not have a current roommate.<br />";
            $db = &new PHPWS_DB('hms_roommate_approval');
            $db->addColumn('roommate_zero');
            $db->addColumn('roommate_one');
            $db->addColumn('roommate_two');
            $db->addColumn('roommate_three');
            $db->addWhere('roommate_zero', $_REQUEST['username'], 'ILIKE');
            $db->addWhere('roommate_one', $_REQUEST['username'], 'ILIKE', 'OR');
            $db->addWhere('roommate_two', $_REQUEST['username'], 'ILIKE', 'OR');
            $db->addWhere('roommate_three', $_REQUEST['username'], 'ILIKE', 'OR');
            $roomies = $db->select();
            if($roomies == NULL) {
                $tpl['ROOMMATE'] .= "This person has no pending roommate requests.";
            } else {
                foreach($roomies as $roomie) {
                    if($roomie['roommate_zero'] != $_REQUEST['username']) {
                        $tpl['ROOMMATE'] = "Awaiting approval from " . HMS_SOAP::get_full_name($roomie['roommate_zero']) . ".<br />";
                    }

                    if($roomie['roommate_one'] != $_REQUEST['username']) {
                        $tpl['ROOMMATE'] = "Awaiting approval from " . HMS_SOAP::get_full_name($roomie['roommate_one']) . ".<br />";
                    }

                    if(isset($roomie['roommate_two']) && $roomie['roommate_two'] != $_REQUEST['username']) {
                        $tpl['ROOMMATE'] = "Awaiting approval from " . HMS_SOAP::get_full_name($roomie['roommate_two']) . ".<br />";
                    }

                    if(isset($roomie['roommate_three']) && $roomie['roommate_three'] != $_REQUEST['username']) {
                        $tpl['ROOMMATE'] = "Awaiting approval from " . HMS_SOAP::get_full_name($roomie['roommate_three']) . ".<br />";
                    }
                }
            }
        } else {
            $db = &new PHPWS_DB('hms_roommates');
            $db->addColumn('roommate_zero');
            $db->addColumn('roommate_one');
            $db->addColumn('roommate_two');
            $db->addColumn('roommate_three');
            $db->addWhere('roommate_zero', $_REQUEST['username'], 'ILIKE');
            $db->addWhere('roommate_one', $_REQUEST['username'], 'ILIKE', 'OR');
            $db->addWhere('roommate_two', $_REQUEST['username'], 'ILIKE', 'OR');
            $db->addWhere('roommate_three', $_REQUEST['username'], 'ILIKE', 'OR');
            $roomies = $db->select();
            foreach($roomies as $roomie) {
                if($roomie['roommate_zero'] != $_REQUEST['username']) {
                    $tpl['ROOMMATE'] = "Grouped with " . HMS_SOAP::get_full_name($roomie['roommate_zero']) . ".<br />";
                }

                if($roomie['roommate_one'] != $_REQUEST['username']) {
                    $tpl['ROOMMATE'] = "Grouped with " . HMS_SOAP::get_full_name($roomie['roommate_one']) . ".<br />";
                }

                if(isset($roomie['roommate_two']) && $roomie['roommate_two'] != $_REQUEST['username']) {
                    $tpl['ROOMMATE'] = "Grouped with " . HMS_SOAP::get_full_name($roomie['roommate_two']) . ".<br />";
                }

                if(isset($roomie['roommate_three']) && $roomie['roommate_three'] != $_REQUEST['username']) {
                    $tpl['ROOMMATE'] = "Grouped with " . HMS_SOAP::get_full_name($roomie['roommate_three']) . ".<br />";
                }
            }
        }

        // get student application
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        if(HMS_Application::check_for_application($_REQUEST['username'])) {
            $tpl['APPLICATION_LINK'] = PHPWS_Text::secureLink(_('Housing Application'), 'hms', array('type'=>'student', 'op'=>'view_housing_application', 'student'=>$_REQUEST['username']));
        } else {
            $tpl['APPLICATION_LINK'] = "No Housing Application exists for this user.";
        }

        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->addColumn('hms_learning_communities.community_name');
        $db->addWhere('hms_learning_community_assignment.rlc_id', 'hms_learning_communities.id');
        $db->addWhere('hms_learning_community_assignment.asu_username', $_REQUEST['username']);
        $results = $db->select();
        
        if($results != NULL && $results != FALSE) {
            $tpl['RLC_STATUS'] = $results['community_name'];
        } else {
            $db = &new PHPWS_DB('hms_learning_community_applications');
            $db->addColumn('id');
            $db->addWhere('user_id', $_REQUEST['username']);
            $results = $db->select('one');
            if($result != FALSE && $results != NULL) {
                $tpl['RLC_STATUS'] = "This student is currently awaiting RLC approval. You can view their application " . PHPWS_Text::secureLink(_('here'), 'hms', array('type'=>'rlc', 'op'=>'view_rlc_application', 'username'=>$_REQUEST['username']));
            } else {
                $tpl['RLC_STATUS'] = "This student is not in a Learning Community and has no pending approval.";
            }
        }

        $final = PHPWS_Template::process($tpl, 'hms', 'student/show_student_info.tpl');
        return $final;
    }

    function set_meal_plan()
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('id',$_REQUEST['assignment_id']);
        $db->addValue('meal_option',$_REQUEST['meal_option']);
        $db->update();

        $msg = '<font color="#0000FF">Meal option updated.</font><br /><br />';
        return $msg . HMS_Student::get_matching_students();
    }

/*
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
*/

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
        PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
        $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_ROOMMATE);
        $side_thingie->show();

        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $content = HMS_Form::get_roommate_username();
        return $content;
    }

    function verify_roommate_username()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
        $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_ROOMMATE);
        $side_thingie->show();
        
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $content = HMS_Form::verify_roommate_username($_SESSION['asu_username'], $_REQUEST['username']);
        return $content;
    }

    function show_main_menu()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');

        # Check to see if an application exists
        if(HMS_Application::check_for_application($_SESSION['asu_username'])) {
            PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
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
            } else {
                $message .= PHPWS_Text::secureLink(_('View My RLC Application'), 'hms', array('type'=>'student', 'op'=>'view_rlc_application'));
            }
            $message .= "<br /><br />";
    
/*            PHPWS_Core::initModClass('hms', 'HMS_Student_Profile.php');
            $message .= "The HMS Student Profile is optional and can be used to help you find a roommate who shares your interests. ";
            $message .= "<br />";
            $message .= PHPWS_Text::secureLink(_('Create/Edit your optional Student Profile'), 'hms', array('type'=>'student', 'op' =>'show_profile_form'));
            $message .= "<br /><br />";

            PHPWS_Core::initModClass('hms', 'HMS_Roommate_Approval.php');
            if(HMS_Roommate_Approval::has_requested_someone($_SESSION['asu_username'])) {
                $message .= "You have selected a roommate and are awaiting their approval.";
            } else {
                $message .= "If you know who you want to room with, you can go ahead and " . PHPWS_Text::secureLink(_('select your roommate.'), 'hms', array('type'=>'student','op'=>'get_roommate_username'));
            }

            $message .= "<br /><br />";*/
            $message .= 'If you need to download and print the License Agreement please ';
            $message .= '<a href="http://hms.appstate.edu/files/contract.pdf" target="_blank">click here.</a>';
            $message .= "<br /><br />";
//            $message .= PHPWS_Text::secureLink('Roommate Search Tool.', 'hms', array('type'=>'student','op'=>'show_profile_search'));
//            $message .= "<br /><br />";
            $message .= PHPWS_Text::secureLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));
            $message .= "<br /><br />";
        } else {
            # No application exists, check deadlines to see if the user can still apply
            PHPWS_Core::initModClass('hms','HMS_Deadlines.php');
            if(!HMS_Deadlines::check_deadline_past('submit_application_end_timestamp')){
                # Application deadline has not passed, so show terms and agreement page
                $message = HMS_Student::show_terms_and_agreement();
            }else{
                # Application deadline has passed, show an error message;
                $message = "Sorry, it is too late to apply for housing. If you need assistance please contact the Department of Housing and Residence Life by phone.";
            }
        }
        
        return $message;
    }

    function show_terms_and_agreement(){
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
        
        return $message;
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
            case 'view_housing_application':
                PHPWS_Core::initModClass('hms', 'HMS_Application.php');
                return HMS_Application::view_housing_application($_REQUEST['student']);
                break;
            case 'save_application':
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::save_application();
                break;
            case 'show_profile_search':
                PHPWS_Core::initModClass('hms','HMS_Student_Profile.php');
                return HMS_Student_Profile::display_profile_search();
                break;
            case 'profile_search':
                PHPWS_Core::initModClass('hms','HMS_Student_Profile.php');
                return HMS_Student_Profile::profile_search();
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
            case 'save_roommate_username':
                if(isset($_REQUEST['cancel'])) {
                    return HMS_Student::get_roommate_username();
                } else {
                    PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                    $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_ROOMMATE);
                    $side_thingie->show();
                    PHPWS_Core::initModClass('hms', 'HMS_Roommate_Approval.php');
                    return HMS_Roommate_Approval::save_roommate_username();
                }
                break;
            case 'set_meal_plan':
                return HMS_Student::set_meal_plan();
                break;
            case 'main':
                return HMS_Student::show_main_menu();
                break;
               return $message;
            default:
                return "{$_REQUEST['op']} <br />";
                break;
        }
    }
};
?>
