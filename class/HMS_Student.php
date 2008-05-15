<?php

PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

class HMS_Student {
    var $id;
    var $first_name;
    var $middle_name;
    var $last_name;
    var $asu_username;
    var $gender;
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

        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        $student = &new HMS_Form;
        $content = $student->enter_student_search_data();
        return $content;
    }

    function get_matching_students()
    {   
        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
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

        #test($student_info);

        $tpl['MENU_LINK']   = PHPWS_Text::secureLink(_('Return to Search'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));
        
        $tpl['BANNER_ID']   = $student_info->banner_id;
        $tpl['FIRST_NAME']  = $student_info->first_name;
        $tpl['MIDDLE_NAME'] = $student_info->middle_name;
        $tpl['LAST_NAME']   = $student_info->last_name;
        
        if($student_info->gender == 'F') {
            $tpl['GENDER'] = "Female";
        } else if ($student_info->gender == 'M') {
            $tpl['GENDER'] = "Male";
        } else {
            $tpl['GENDER'] = "Unknown gender: ({$student_info->gender})";
        }

        $tpl['DOB'] = $student_info->dob;

        if($student_info->projected_class == CLASS_FRESHMEN) {
            $tpl['CLASS'] = "Freshman";
        } else if ($student_info->projected_class == CLASS_SOPHOMORE) {
            $tpl['CLASS'] = "Sophomore";
        } else if ($student_info->projected_class == CLASS_JUNIOR) {
            $tpl['CLASS'] = "Junior";
        } else if ($student_info->projected_class == CLASS_SENIOR) {
            $tpl['CLASS'] = "Senior";
        } else {
            $tpl['CLASS'] = "Unknown class: ({$student_info->projected_class})";
        }

        $tpl['APPLICATION_TERM'] = $student_info->application_term;
        
        /*************
         * Addresses *
         *************/
        $pr_address = HMS_SOAP::get_address($_REQUEST['username'], ADDRESS_PRMT_RESIDENCE);

        $tpl['PR_ADDRESS_L1']       = $pr_address->line1;
        $tpl['PR_ADDRESS_L2']       = $pr_address->line2;
        $tpl['PR_ADDRESS_L3']       = $pr_address->line3;
        $tpl['PR_ADDRESS_CITY']     = $pr_address->city;
        $tpl['PR_ADDRESS_STATE']    = $pr_address->state;
        $tpl['PR_ADDRESS_ZIP']      = $pr_address->zip;

        $ps_address = HMS_SOAP::get_address($_REQUEST['username'], ADDRESS_PRMT_STUDENT);
        
        $tpl['PS_ADDRESS_L1']       = $ps_address->line1;
        $tpl['PS_ADDRESS_L2']       = $ps_address->line2;
        $tpl['PS_ADDRESS_L3']       = $ps_address->line3;
        $tpl['PS_ADDRESS_CITY']     = $ps_address->city;
        $tpl['PS_ADDRESS_STATE']    = $ps_address->state;
        $tpl['PS_ADDRESS_ZIP']      = $ps_address->zip;
        
        /****************
         * Phone number *
         ****************/
        
        $tpl['PHONE_AC'] = $student_info->phone->area_code;
        $tpl['PHONE_NUMBER'] = $student_info->phone->number;
        $tpl['USERNAME'] = $_REQUEST['username'];

        $tpl['TITLE'] = "Search Results - " . HMS_Term::term_to_text(HMS_Term::get_selected_term(),TRUE);

        $tpl['APPLICATION_TERM'] = HMS_SOAP::get_application_term($_REQUEST['username']);

        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $this_term = HMS_Term::get_selected_term();

        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        $assignment = HMS_Assignment::get_assignment($_REQUEST['username'], $this_term);

        if(isset($assignment) && $assignment != FALSE){
            $unassign = PHPWS_Text::secureLink('Unassign', 'hms', array('type'=>'assignment', 'op'=>'show_unassign_student', 'username'=>$_REQUEST['username']));
            $reassign = PHPWS_Text::secureLink('Reassign', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$_REQUEST['username']));
            $tpl['ROOM_ASSIGNMENT'] = $assignment->where_am_i() . " | $reassign | $unassign";
        }else if($assignment == FALSE){
            $tpl['ROOM_ASSIGNMENT'] = 'Student is not assigned for ' . HMS_Term::term_to_text($this_term, TRUE) . '. ' . PHPWS_Text::secureLink('Assign student now.', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$_REQUEST['username']));
        }else{
            $tpl['ROOM_ASSIGNMENT'] = "Error: Could not look up the current assignment. Please contact ESS.";
        }

        // get roommate or pending roommate
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $roommates = HMS_Roommate::get_all_roommates($_REQUEST['username'], HMS_Term::get_selected_term());
        $tpl['ROOMMATE'] = "";
        if(empty($roommates)) {
            $tpl['ROOMMATE'] = "This person has no roommates or roommate requests.<br />";
        } else {
            foreach($roommates as $roommate) {
                if($roommate->confirmed) {
                    $mate = $roommate->get_other_guy($_REQUEST['username']);
                    $user_link = PHPWS_Text::secureLink(HMS_SOAP::get_full_name($mate), 'hms',
                        array('type'=>'student',
                              'op'=>'get_matching_students',
                              'username'=>$mate));
                    $tpl['ROOMMATE'] .= "Confirmed roommates with $user_link<br />";
                } else {
                    $mate = $roommate->get_other_guy($_REQUEST['username']);
                    $user_link = PHPWS_Text::secureLink(HMS_SOAP::get_full_name($mate), 'hms',
                        array('type'=>'student',
                              'op'=>'get_matching_students',
                              'username'=>$mate));
                    if($roommate->requestor == $_REQUEST['username']) {
                        $tpl['ROOMMATE'] .= "Awaiting approval from $user_link<br />";
                    } else {
                        $tpl['ROOMMATE'] .= "Request Pending from $user_link<br />";
                    }
                }
            }
        }

        /**************
         * RLC Status *
         **************/
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $rlc_names = HMS_Learning_Community::getRLCList();

        $rlc_assignment     = HMS_RLC_Assignment::check_for_assignment($_REQUEST['username'], HMS_Term::get_selected_term());
        $rlc_application    = HMS_RLC_Application::check_for_application($_REQUEST['username'], HMS_Term::get_selected_term(), FALSE);
        
        if($rlc_assignment != FALSE){
            $tpl['RLC_STATUS'] = "This student is assigned to: " . $rlc_names[$rlc_assignment['rlc_id']];
        }else if ($rlc_application != FALSE){
            $tpl['RLC_STATUS'] = "This student is currently awaiting RLC approval. You can view their application " . PHPWS_Text::secureLink(_('here'), 'hms', array('type'=>'rlc', 'op'=>'view_rlc_application', 'username'=>$_REQUEST['username']));
        }else{
            $tpl['RLC_STATUS'] = "This student is not in a Learning Community and has no pending approval.";
        }
        
        /********************
         * Login as Student *
         ********************/
        if( Current_User::allow('hms', 'login_as_student') ) { 
            $tpl['LOGIN_AS_STUDENT'] = '<tr><td>Login as this student: </td><td><a href=index.php?module=hms&op=main&login_as_student=' . $_REQUEST['username'] . '> '. $_REQUEST['username'] . '</a></td></tr>';
        }
        $final = PHPWS_Template::process($tpl, 'hms', 'student/show_student_info.tpl');

        /***********************/
        /* Tabify Student Info */
        /***********************/
        $link    = "index.php?module=hms&type=student&op=get_matching_students&username=" . $_REQUEST['username'];
        $content = $final;
        if( isset($_REQUEST['tab']) ){
            switch( $_REQUEST['tab'] ){
                case 'student_info':
                    $content = $final;
                    break;
                case 'housing_app':
                    $content = HMS_Application::view_housing_application($_REQUEST['username']);
                    break;
                case 'student_logs':
                    PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
                    $_REQUEST['actee'] = $_REQUEST['username'];
                    $content           = HMS_Activity_Log::main();
                    break;
                default:
                    $content = $final;
                    break;
            }
        } 

        $tags['student_info']   = array('title' => 'Student Info Page', 'link' => $link,
                                        'link_title' => 'Student Info Page');
        $tags['housing_app']    = array('title' => 'Housing Application', 'link' => $link,
                                        'link_title' => 'Housing Application');
        $tags['student_logs']   = array('title' => 'Student Logs', 'link' => $link,
                                        'link_title' => 'Student Logs');

        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $panel = &new PHPWS_Panel('studentInfo');
        $panel->quickSetTabs($tags);
        if( !isset($_REQUEST['tab']) ){
            $panel->setCurrentTab('student_info');
        }

        return $panel->display($content);
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
            return '<i>ERROR! ERROR!</i><br />Student ' . $_REQUEST['id'] . ' could not be marked deleted.<br />';
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

    # Used to set 'agreed_to_terms' true after a user has already applied
    function agreed_to_terms()
    {
        $db = &new PHPWS_DB('hms_application');
        $db->addwhere('hms_student_id', $_SESSION['asu_username'], 'ILIKE');
        $db->addValue('agreed_to_terms', 1);
        $result = $db->update();

        PHPWS_Error::logIfError($result);
        
        # Log the fact that the user agreed to the terms and agreemnts
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_AGREED_TO_TERMS, $_SESSION['asu_username'], NULL);

        return;
    }

    /*********************
     * Static UI Methods *
     *********************/

    function show_welcome_screen()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Deadlines.php');
        PHPWS_Core::initModClass('hms', 'HMS_Entry_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Contact_Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        #test(HMS_SOAP::get_student_info($_SESSION['asu_username']), 1);

        # Grab the current term for use late
        $current_term = HMS_Term::get_current_term();

        # Try initial lookups of the student's application_term, type, and class
        $application_term   = HMS_SOAP::get_application_term($_SESSION['asu_username']);
        $student_type       = HMS_SOAP::get_student_type($_SESSION['asu_username'], $application_term);
        $student_class      = HMS_SOAP::get_student_class($_SESSION['asu_username'], $application_term);
        $dob                = HMS_SOAP::get_dob($_SESSION['asu_username']);
        $gender             = HMS_SOAP::get_gender($_SESSION['asu_username']);

        # Check for banner errors in any of these calls
        if($application_term === FALSE ||
            $student_type === FALSE ||
            $student_class === FALSE ||
            $dob === FALSE ||
            $gender === FALSE ||
            empty($application_term) ||
            empty($student_type) ||
            empty($student_class) ||
            empty($dob) ||
            empty($gender))
            {
                # TODO: HMS_Mail here
                PHPWS_Error::log('Initial banner lookup failed', 'hms', 'show_welcome_screen', "username: {$_SESSION['asu_username']}");
                return HMS_Contact_Form::show_contact_form();
            }

        # Calculate the student's age and check for >= 25 years old
        $dob = explode('-', $dob);
        if($dob[0] < date('Y') - 25) {
            # Log that it happened
            HMS_Activity_Log::log_activity($_SESSION['asu_username'],
                                           ACTIVITY_TOO_OLD_REDIRECTED,
                                           $_SESSION['asu_username'],
                                           'DOB: ' . HMS_SOAP::get_dob($_SESSION['asu_username']));

            # Set a rediret and return the appropriate template
            $tpl = array();
            Layout::metaRoute('http://www.housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=33&MMN_position=164:116&MMN_position=190:190',10);
            return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_non_traditional.tpl');
        }

        # THIS IS A HACK
        $student = HMS_SOAP::get_student_info($_SESSION['asu_username']);
        
        if($student->application_term == 200820 || $student->application_term == 200830){
            $show_summer_hack_msg = TRUE;
        }else{
            $show_summer_hack_msg = FALSE;
        }
        
        # Get deadlines for the current term for future use
        $deadlines = HMS_Deadlines::get_deadlines($_SESSION['application_term']);
        
        /******************************************
         * Sort returning students (lottery) from *
         * freshmen (first-time application)      *
         ******************************************/
        # Check application term for past or future
        if($application_term <= $current_term){
            /**************
             * Continuing *
             **************/
            # Application term is in the past
            
            # Check the deadlines to see if users can sign up for the lottery yet
            # If so, show term->lottery entry,  or the "you're already entered" screen,
            # if not, show a "welcome back, but sorry we're not ready" screen
            # TODO
            # NOTE: This is a temporary redirect for now. The above still needs to be implemented. See Trac ticket #55.
            # TODO(workaround): if the admin is logged in as a student don't kill their session
            if( Current_User::getUsername() != 'hms_student' && Current_User::allow('hms', 'login_as_student') ) {
                Layout::add('<p>  The student would have been logged out at this point, if you would like to view the page that the student would be redirected to please go here: </p>');
                Layout::add('<a href=http://www.housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=253> http://www.housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=253</a> <br>');
                Layout::add('<p>Otherwise click <a href=index.php?module=hms&op=end_student_session>here</a> to logout of the student session.</p>');
            } else {
                header('Location: http://www.housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=253');
                exit;
            }

            #TODO: check for student type of 'C' or 'R'

            # Check if the user is already entered for the lottery
            # if so, display the appropriate message
            # TODO

            # Check that we're within deadlines for lottery signup in the current term
            if(HMS_Deadlines::check_within_deadlines('lottery_signup_begin_timestamp','lattery_signup_end_timestamp', $deadlines)){
                # We're within deadlines, so show the "we see you're a returning student, click continue to enter the lottery" message
                # TODO
            }else if(!HMS_Deadlines::check_deadline_past('lottery_signup_begin_timestamp', $deadlines)){
                # Show a too early to signup message.
                # TODO
            }else if(HMS_Deadlines::check_deadline_past('lottery_signup_end_timestamp', $deadlines)){
                # Show a too late message.
                # TODO
            }else{
                # Show a general error message.
                # TODO
            }

        }else if($application_term > $current_term){

            /*********************
             * Incoming Freshmen *
             *********************/
            # Application term is in the future
            $tpl = array();
            $tpl['BEGIN_DEADLINE'] = HMS_Deadlines::get_deadline_as_date('submit_application_begin_timestamp', $deadlines);
            $tpl['END_DEADLINE'] = HMS_Deadlines::get_deadline_as_date('submit_application_end_timestamp', $deadlines);

            # The the entry term is in the fall, show an entry term of "Fall yy - Spring yy++"
            # Otherwise just show the one entry term
            $semester = HMS_Entry_Term::get_entry_semester($_SESSION['asu_username']);
            if($semester == TERM_FALL){
                $tpl['ENTRY_TERM'] = HMS_Term::term_to_text($application_term, TRUE) . " - " . HMS_Term::term_to_text(HMS_Term::get_next_term($application_term),TRUE);
            }else{
                $tpl['ENTRY_TERM'] = HMS_Term::term_to_text($application_term, TRUE);
            }

            $tpl['CONTACT_LINK'] = PHPWS_Text::secureLink('click here', 'hms', array('type'=>'student', 'op'=>'show_contact_form'));

            # Check the student type, must be freshmen or transfer
            if($student_type != TYPE_FRESHMEN && $student_type != TYPE_TRANSFER && $student_type != TYPE_RETURNING){
                # No idea what's going on here, send to a contact page
                return HMS_Contact_Form::show_contact_form();
            }

            # Make sure the user's application term exists in hms_term,
            # otherwise give a "too early" message
            if(!HMS_Term::check_term_exists($application_term)){
                return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_no_entry_term.tpl');
            }
                        
            # Make sure the student doesn't already have an assignment on file for the current term
            if(HMS_Assignment::check_for_assignment($_SESSION['asu_username'])){
                # No idea what's going on here, send to a contact page
                return HMS_Contact_Form::show_contact_form();
            }
            
            # Check to see if the user has an application on file already
            # If so, forward to main menu
            if(HMS_Application::check_for_application($_SESSION['asu_username'], $application_term)){
                return HMS_Student::show_main_menu();
            }
            
            # Get deadlines for the user's application_term for future use
            $deadlines = HMS_Deadlines::get_deadlines($application_term);

            # Check to see if the user can apply yet
            if(!HMS_Deadlines::check_deadline_past('submit_application_begin_timestamp', $deadlines)){
                return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_too_soon.tpl');
            }
            
            # No application exists, check deadlines to see if the user can still apply
            if(!HMS_Deadlines::check_deadline_past('submit_application_end_timestamp', $deadlines)){
                
                # Setup the form for the 'continue' button.
                $form = &new PHPWS_Form;
                $form->addSubmit('submit', 'Continue');
                $form->addhidden('module', 'hms');
                $form->addHidden('type', 'student');
                $form->addHidden('op','show_terms_and_agreement');

                $form->mergeTemplate($tpl);
                $tpl = $form->getTemplate();

                # THIS IS PART OF THE SUMMER HACK
                if($show_summer_hack_msg){
                    $tpl['HACK_MSG'] = '<b>Note:</b> This is the <b>Fall 2008 & Spring 2009</b> housing application. As a freshmen student attending Appalachian for a summer semester, <b>you must also apply for summer housing on paper</b>. The Department of Housing & Residence Life will be mailing summer housing information packets on April 1st.';
                }
                
                # Application deadline has not passed, so show welcome page
                if($student_type == TYPE_FRESHMEN){
                    return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_freshmen.tpl');
                }else{
                    return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_transfer.tpl');
                }
            }else{
                # Application deadline has passed, show an error message;
                
                #TODO: Try to find a way to log the user out here
                return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_deadline_past.tpl');
            }

        }else{
            # No idea what's going on here, send to a contact page
            return HMS_Contact_Form::show_contact_form();
        }   
    }

    function show_main_menu()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_Deadlines.php');

        # Show the side thingie
        PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
        $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_NOT_STARTED);
        $side_thingie->show();
        
        # Decide which menu to show based on the entry term
        $entry_term = HMS_Entry_Term::get_entry_semester($_SESSION['asu_username']);

        # Lookup the deadline for that entry term
        $deadlines = HMS_Deadlines::get_deadlines($_SESSION['application_term']);
        
        if($entry_term == TERM_SPRING){
            return HMS_Student::show_spring_main_menu($deadlines);
        }else if($entry_term == TERM_FALL){
            return HMS_Student::show_fall_main_menu($deadlines);
        }
    }

    function show_terms_and_agreement($terms_and_agreement_only = FALSE)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
        $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_AGREE);
        $side_thingie->show(FALSE);
       
        $form = new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addSubmit('begin', _('I AGREE'));
        $form->addSubmit('quit', _('I DISAGREE'));

        if($terms_and_agreement_only){
            $form->addHidden('forward_to_main_menu', 1);
            $form->addHidden('op', 'agreed_to_terms');
        }else{
            $form->addHidden('op', 'begin_application');
        }
        
        $message  = "<b>Please read the following License Agreement and click either 'I AGREE' or 'I DISAGREE'<br />";
        $message .= 'Please note that if you click disagree you will be logged out of HMS.</b><br /><br />';
        $message .= 'If you wish to read this Agreement as a printable PDF please ';
        $message .= '<a href="http://hms.appstate.edu/files/contract.pdf" target="_blank">click here.</a><br /><br />';
        $message .= 'If you need to update or download a PDF viewer you can <a href="http://www.adobe.com/products/acrobat/readstep2.html" target="_blank">get one here</a><br /><br />';

        # Check for under 18, display link to print message
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $dob = explode('-', HMS_SOAP::get_dob($_SESSION['asu_username']));
        #test($dob);
        $dob_timestamp = mktime(0,0,0,$dob[1],$dob[2],$dob[0]);
        $current_timestamp = mktime(0,0,0);
        if(($current_timestamp - $dob_timestamp) < (3600 * 24 * 365 * 18)){
            #echo "under 18!!<br>\n";
            $message .= '<br /><font color="red">Because you are under age 18, you MUST print a copy of the Housing Contract Agreement, ';
            $message .= 'have a parent or legal guardian sign it, and return it to the Department of ';
            $message .= 'Housing and Residence Life. Your application cannot be fully processed until a Housing Contract ';
            $message .= 'signed by a parent or gaurdian is on file. Please <a href="http://hms.appstate.edu/files/contract.pdf">click here </a>';
            $message .= 'to open a printer-friendly version of the Housing Contract.</font><br /><br />';

            # Set the 'agreed_to_terms' flag to false
            $form->addHidden('agreed_to_terms',0);
        }else{
            #echo "over 18!!<br>\n";
            $form->addHidden('agreed_to_terms',1);
        }
        
        $tpl = $form->getTemplate();

        $tpl['MESSAGE'] = $message;
        $tpl['CONTRACT'] = str_replace("\n", "<br />", file_get_contents('mod/hms/inc/contract.txt'));
        
        $message = PHPWS_Template::process($tpl, 'hms', 'student/contract.tpl');
        
        return $message;
    }


    function show_fall_main_menu($deadlines)
    {

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $tags = array();

        # Get the student's application for later use
        $application = new HMS_Application($_SESSION['asu_username'], $_SESSION['application_term']);

        # Tags for status images
        $alert_img      = '<img src="images/mod/hms/icons/alert.png" />';
        $arrow_img      = '<img src="images/mod/hms/icons/arrow.png" />';
        $check_img      = '<img src="images/mod/hms/icons/check.png" />';
        $lock_img       = '<img src="images/mod/hms/icons/lock.png" />';
        $warning_img    = '<img src="images/mod/hms/icons/warning.png" />';

        /****************************************************
         * Welcome and view application link (always shown) *
         ***************************************************/
        $tags['WELCOME_MSG'] = 'Hello, ' . HMS_SOAP::get_full_name($_SESSION['asu_username'])  . '. Welcome to the Housing Management System!<br /><br />';
        #TODO: Make the term here dynamic.
        $tags['WELCOME_MSG'] .= 'Please follow the steps listed below to apply for housing for the <b>' . HMS_Term::term_to_text($_SESSION['application_term'], TRUE) . ' AND ' . HMS_Term::term_to_text(HMS_Term::get_next_term($_SESSION['application_term']), TRUE) . '</b> term.';

        /*************************************
         * Residence Hall Contract Agreement *
         ************************************/
        $tags['TERMS_INTRO'] = 'To view the Residence Hall Contract click the following link: <a href="http://hms.appstate.edu/files/contract.pdf">The Residence Hall Contract Agreement</a>.';
        if($application->agreed_to_terms == 1){
            $tags['TERMS_ICON']  = $check_img;
            $tags['TERMS_MSG']   = '<b>You have agreed to the Residence Hall Contract.</b> You may click the link above to review the Residence Hall Contract at any time.';
        }else{
            $tags['TERMS_ICON']  = $alert_img;
            $dob = explode('-', HMS_SOAP::get_dob($_SESSION['asu_username']));
            if($dob[0] < date('Y') - 18) {
                $tags['TERMS_MSG']   = '<b>You have not agreed to the Residence Hall Contract.</b> You may click the link below to view and agree to the Residence Hall Contract.';
                $tags['TERMS_LINK']  = PHPWS_Text::secureLink('View & Agree to the Residence Hall Contract', 'hms', array('type'=>'student', 'op'=>'show_terms_and_agreement_only'));
            }else{
                $tags['TERMS_MSG']   = '<b>You have not agreed to the Residence Hall Contract.</b> You were under 18 at the time you completed your application. You must print the last page of the Residence Hall Contract, complete it (including a parent/guardian signature), and return it to the Department of Housing & Residnce Life. (Note: If you have already mailed your completed Housing Contract Agreemnt, please allow 3-4 weeks for delivery and processing.)<br /><br /><b>Signed Residence Hall Contracts may be mailed to:</b><br />Housing Assignments<br />P.O. Box 32111<br />Boone, NC 28608-2111';
            }
        }
       
        /***************
         * Application *
         **************/
        $tags['APPLICATION_ICON'] = $check_img;
        $tags['APPLICATION_MSG']  = '<b>You have completed a Housing Application.</b> You may click the link below to review your current application.';
        $tags['APPLICATION_LINK'] = PHPWS_Text::secureLink(_('View My Application'), 'hms', array('type'=>'student', 'op'=>'review_application'));
        
        # Check deadlines for editing applications
        if(HMS_Deadlines::check_within_deadlines('submit_application_begin_timestamp','edit_application_end_timestamp', $deadlines)){
            $tags['NEW_APP_MSG']  = '<b>You may also submit a new application</b> until ' . HMS_Deadlines::get_deadline_as_date('edit_application_end_timestamp', $deadlines)  . '. This will replace the application you have already saved.';
            $tags['NEW_APP_LINK'] = PHPWS_Text::secureLink(_('Submit a New Application'), 'hms', array('type'=>'student', 'op'=>'begin_application'));
        }else if(!HMS_Deadlines::check_deadline_past('submit_application_begin_timestamp', $deadlines)){
            $tags['NEw_APP_MSG']  = "<b>It is too soon to resubmit your housing application.</b> You will be able to submit an edited application on " . HMS_Deadlines::get_deadline_as_date('submit_application_begin_timestamp', $deadlines) . ".";
        }else{
            $tags['NEW_APP_MSG']  = "<b>The deadline for editing your housing application passed</b> on " . HMS_Deadlines::get_deadline_as_date('edit_application_end_timestamp', $deadlines) . ".";
        }
        
        /*******************
         * RLC Application *
         ******************/
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        $tags['RLC_INTRO'] = 'For more infomration about Appalachian\'s Unique Housing Options please visit the <a href="http://housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=134" target="_blank">Housing & Residence Life Website</a>.';

        # Check deadlines for RLC application
        if(HMS_RLC_Application::check_for_application($_SESSION['asu_username'], $_SESSION['application_term']) === FALSE){
            # Check deadlines for RLC applications
            if(HMS_Deadlines::check_within_deadlines('submit_application_begin_timestamp','submit_rlc_application_end_timestamp', $deadlines)){
                if(HMS_SOAP::get_credit_hours($_SESSION['asu_username']) <= 15){
                    $tags['RLC_MSG']  = '<b>You may apply for a Unique Housing Option until</b> ' . HMS_Deadlines::get_deadline_as_date('submit_rlc_application_end_timestamp', $deadlines) . '.  To apply click on the link below.';
                    $tags['RLC_LINK'] = PHPWS_Text::secureLink(_('Unique Housing Options Application Form'), 'hms', array('type'=>'student', 'op'=>'show_rlc_application_form'));
                    $tags['RLC_ICON'] = $arrow_img;
                }else{
                    $contact_form_link = PHPWS_Text::secureLink('contact form', 'hms', array('type'=>'student','op'=>'show_contact_form'));
                    $tags['RLC_MSG']  = '<b>You are not eligible to apply for a Unique Housing Option for Underclassmen</b> because this will not be your first semester of college. If this is inaccurate and you are going be in your first semester of college,  please complete the ' . $contact_form_link . ' to let us know. Otherwise, please use the link below to view information about and apply for Unique Housing Options for Upperclassmen.';
                    $tags['RLC_LINK'] = '<a href="http://housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=293" target="_blank">Unique Housing Options for Upperclassmen</a>';
                    $tags['RLC_ICON'] = $lock_img;
                }
            }else if(!HMS_Deadlines::check_deadline_past('submit_application_begin_timestamp', $deadlines)){
                $tags['RLC_MSG']  = "It is too early to apply for Unique Housing Options. You will be able to submit an application on " . HMS_Deadlines::get_deadline_as_date('submit_application_begin_timestamp', $deadlines) . ".";
                $tags['RLC_ICON'] = $lock_img;
            }else{
                $tags['RLC_MSG']  = "It is too late to apply for a Unique Housing Options. The deadline passed on " . HMS_Deadlines::get_deadline_as_date('submit_rlc_application_end_timestamp', $deadlines) . ".";
                $tags['RLC_ICON'] = $lock_img;
            }
        }else{
            $tags['RLC_MSG']  = "You have completed a Residential Learning Community application. You can click the link below to review your application. If you need to change your application, please contact Housing and Residence Life via phone.";
            $tags['RLC_LINK'] = PHPWS_Text::secureLink(_('View My Learning Community Application'), 'hms', array('type'=>'student', 'op'=>'view_rlc_application'));
            $tags['RLC_ICON'] = $check_img;
        }
        
        /***************************************
         * Student Profile & Profile Searching *
         **************************************/

        PHPWS_Core::initModClass('hms', 'HMS_Student_Profile.php');
        $tags['PROFILE_INTRO'] = "The HMS Student Profile is optional and can be used to help you find a roommate who shares your hobbies and interests. Once you complete your profile, you will be able to search for other students who share your interests based on their profiles.  Please note that this is ONLY a tool for finding roommates; your housing assignment will NOT be affected by this profile.";
        
        $tags['PROFILE_ICON'] = $lock_img;

        # Check deadlines for editing profiles
        if(HMS_Deadlines::check_within_deadlines('edit_profile_begin_timestamp','edit_profile_end_timestamp', $deadlines)){
            $tags['PROFILE_MSG']  = '<b>You may create or edit your profile</b> until ' . HMS_Deadlines::get_deadline_as_date('edit_profile_end_timestamp', $deadlines) . '. Click the link below to create or edit your profile.';
            $tags['PROFILE_LINK'] = PHPWS_Text::secureLink(_('Create/Edit your optional Student Profile'), 'hms', array('type'=>'student', 'op' =>'show_profile_form'));
            $tags['PROFILE_ICON'] = $arrow_img;
        }else if(!HMS_Deadlines::check_deadline_past('edit_profile_begin_timestamp', $deadlines)){
            $tags['PROFILE_MSG']  = '<b>It is too early to create your profile.</b> You can create a profile on ' . HMS_Deadlines::get_deadline_as_date('edit_profile_begin_timestamp', $deadlines) . '.';
        }else{
            $tags['PROFILE_MSG']  = '<b>It is too late to create your profile.</b> The deadline passed on ' . HMS_Deadlines::get_deadline_as_date('edit_profile_end_timestamp', $deadlines)  . '.';
        }

        # Check deadlines for searching student profiles
        if(HMS_Deadlines::check_within_deadlines('search_profiles_begin_timestamp','search_profiles_end_timestamp', $deadlines)){
            $tags['PROFILE_ICON'] = $arrow_img;
            $profile = HMS_Student_Profile::check_for_profile();
            if($profile > 0 && $profile !== FALSE){
                # Show the search profiles link
                $tags['ROOMMATE_SEARCH_MSG']  = "<b>Click the link below to use the Roommate Search Tool</b> to look for potential roommate based on their profiles. You may use the Profile Search Tool until " . HMS_Deadlines::get_deadline_as_date('search_profiles_end_timestamp', $deadlines) . ".";
                $tags['ROOMMATE_SEARCH_LINK'] = PHPWS_Text::secureLink('Roommate Search Tool', 'hms', array('type'=>'student','op'=>'show_profile_search'));
            }else{
                $tags['ROOMMATE_SEARCH_MSG'] = 'To use the search feature, please create your profile first by clicking the above link.';
            }
        }else if(!HMS_Deadlines::check_deadline_past('search_profiles_begin_timestamp', $deadlines)){
            $tags['ROOMMATE_SEARCH_MSG'] = '<b>It is too early to search for a roommate.</b> You will be able to search roommate profiles on ' . HMS_Deadlines::get_deadline_as_date('search_profiles_begin_timestamp', $deadlines) . '.';
        }else{
            $tags['ROOMMATE_SEARCH_MSG'] = '<b>It is too late to search for a roommate.</b> The deadline passed on ' . HMS_Deadlines::get_deadline_as_date('search_profiles_end_timestamp', $deadlines) . '.';
        }


        /**********************
         * Roommate Selection *
         *********************/
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $tags['ROOMMATE_INTRO'] = 'Once you\'ve had a chance to communicate with your desired roommate and you have both agreed that you would like to room together, either of you can use the menu below to initiate an electronic handshake to confirm your desire to be roommates.';

        $roommate = HMS_Roommate::get_confirmed_roommate($_SESSION['asu_username']);
            
        if(!is_null($roommate)){
            $name = HMS_SOAP::get_full_name($roommate);
            $tags['ROOMMATE_MSG'] = "<b>$name</b> has confirmed your roommate request and will be your roommate.";
            $tags['ROOMMATE_ICON'] = $check_img;
        }else{
            $requests = HMS_Roommate::count_pending_requests($_SESSION['asu_username']);
            if($requests > 0) {
                $tags['ROOMMATE_REQUESTS'] = HMS_Roommate::display_requests($_SESSION['asu_username']);
                if($requests == 1) {
                    $tags['ROOMMATE_REQUESTS_MSG'] = "<b style='color: #F00'>You have a roommate request.</b> Please click the name below to confirm or reject the request.";
                } else {
                    $tags['ROOMMATE_REQUESTS_MSG'] = "<b style='color: #F00'>You have roommate requests.</b> Please click a name below to confirm or reject a request.";
                }
            }
            if(HMS_Roommate::has_roommate_request($_SESSION['asu_username'])) {
                $tags['ROOMMATE_MSG'] = "<b>You have selected a roommate</b> and are awaiting their approval.";
                $tags['ROOMMATE_ICON'] = $check_img;
            } else {
                if(HMS_Deadlines::check_within_deadlines('select_roommate_begin_timestamp','select_roommate_end_timestamp',$deadlines)){
                    $tags['ROOMMATE_MSG']  = 'If you know who you want your roommate to be, <b>you may select your roommate now</b>. You will need to know your roommate\'s ASU user name (their e-mail address). You have until ' . HMS_Deadlines::get_deadline_as_date('search_profiles_end_timestamp', $deadlines) . ' to choose a roommate. Click the link below to select your roommate.';
                    $tags['ROOMMATE_LINK'] = PHPWS_Text::secureLink(_('Select Your Roommate'), 'hms', array('type'=>'student','op'=>'show_request_roommate'));
                    $tags['ROOMMATE_ICON'] = $arrow_img;
                }else if(!HMS_Deadlines::check_deadline_past('select_roommate_begin_timestamp', $deadlines)){
                    $tags['ROOMMATE_MSG'] = '<b>It is too early to choose a roommate.</b> You can choose a roommate on ' . HMS_Deadlines::get_deadline_as_date('select_roommate_begin_timestamp', $deadlines) . '.';
                    $tags['ROOMMATE_ICON'] = $lock_img;
                }else{
                    $tags['ROOMMATE_MSG'] = '<b>It is too late to choose a roommate.</b> The deadline passed on ' . HMS_Deadlines::get_deadline_as_date('select_roommate_end_timestamp') . '.';
                    $tags['ROOMMATE_ICON'] = $lock_img;
                }
            }
        }

        /*********************
         * Verify Assignment *
         ********************/
         $tags['VERIFY_INTRO'] = 'Once the assignment process is complete, you can view your up-to-the-minute application, assignment, roommate, and Learning Community status. <b><font color="red">Please note that this status is not final and is subject to change.</font></b>';

        # Check deadlines for verify assignment
        if(HMS_Deadlines::check_within_deadlines('view_assignment_begin_timestamp','view_assignment_end_timestamp',$deadlines)){
            $tags['VERIFY_MSG'] = '<b>You may verify your housing status</b> until ' . HMS_Deadlines::get_deadline_as_date('view_assignment_begin_timestamp', $deadlines) . '. Click the link below to verify your assignment.';
            $tags['VERIFY_LINK'] = PHPWS_Text::secureLink(_('Verify Your Assignment'), 'hms', array('type'=>'student','op'=>'show_verify_assignment'));
            $tags['VERIFY_ICON'] = $arrow_img;
        }else if(!HMS_Deadlines::check_deadline_past('view_assignment_begin_timestamp', $deadlines)){
            $tags['VERIFY_MSG'] = '<b>It is too early to view your housing status</b>. You will be able to view your assignment on ' . HMS_Deadlines::get_deadline_as_date('view_assignment_begin_timestamp', $deadlines) . '.';
            $tags['VERIFY_ICON'] = $lock_img;
        }else{
            $tags['VERIFY_MSG'] = '<b>It is too late to view your housing status</b>. The deadline past on ' . HMS_Deadlines::get_deadline_as_date('view_assignment_end_timestamp', $deadlines) . '.';
            $tags['VERIFY_ICON'] = $lock_img;
        }


        # Logout link
        $tags['LOGOUT_LINK'] = PHPWS_Text::secureLink(_('Log Out'), 'users', array('action'=>'user', 'command'=>'logout'));
        
        return PHPWS_Template::process($tags, 'hms', 'student/main_menu_fall.tpl');
    }

    function show_spring_main_menu($deadlines)
    {

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $tags = array();

        /****************************************************
         * Welcome and view application link (always shown) *
         ***************************************************/
        $tags['WELCOME_MSG'] = 'Hello, ' . HMS_SOAP::get_full_name($_SESSION['asu_username'])  . '. Welcome to the Housing Management System!<br /><br />';
        #TODO: Make the term here dynamic.
        $tags['WELCOME_MSG'] .= 'Please follow the steps listed below to apply for housing for the <b>Spring 2008</b> term.';

        /*************************************
         * Residence Hall Contract Agreement *
         ************************************/
        $tags['TERMS_INTRO'] = 'If you are not at least 18 years old at the time you apply you must have a parent or guardian sign the <a href="http://hms.appstate.edu/files/contract.pdf">The Residence Hall Contract Agreement</a>. Your application will not be processed until the signed, last page of the contract is returned to Housing and Residence Life.';
        # TODO: Check the 'agreed_to_terms' field for the student, to see if they need to print the application.
        $tags['TERMS_MSG']   = 'You have agreed to the Residence Hall Contract. You may click the link above to review the Residence Hall Contract at any time.';
        #$tags['TERMS_MSG']   = 'You were under 18 at the time you completed your application. You must print the last page of the Residence Hall Contract, complete it (including a parent/guardian signature), and return it to the Department of Housing & Residnce Life. (Note: If you have already mailed your completed Housing Contract Agreemnt, please allow 3-4 weeks for delivery and processing.)';
        #$tags['TERMS_LINK']  = '<a href="http://hms.appstate.edu/files/contract.pdf">View the Residence Hall Contract Agreement</a>';
       
        /***************
         * Application *
         **************/
        $tags['APPLICATION_MSG']  = 'You have already completed a Housing Application. You may click the link below to review your current application.';
        $tags['APPLICATION_LINK'] = PHPWS_Text::secureLink(_('View My Application'), 'hms', array('type'=>'student', 'op'=>'review_application'));
        
        # Check deadlines for editing applications
        if(HMS_Deadlines::check_within_deadlines('submit_application_begin_timestamp','edit_application_end_timestamp', $deadlines)){
            $tags['NEW_APP_MSG']  = 'You may also submit a new application until ' . HMS_Deadlines::get_deadline_as_date('edit_application_end_timestamp', $deadlines)  . '. This will replace the application you have already saved.';
            $tags['NEW_APP_LINK'] = PHPWS_Text::secureLink(_('Submit a New Application'), 'hms', array('type'=>'student', 'op'=>'begin_application'));
        }else if(!HMS_Deadlines::check_deadline_past('submit_application_begin_timestamp', $deadlines)){
            $tags['NEw_APP_MSG']  = "It is too soon to resubmit your housing application. You will be able to submit an edited application on " . HMS_Deadlines::get_deadline_as_date('submit_application_begin_timestamp', $deadlines) . ".";
        }else{
            $tags['NEW_APP_MSG']  = "The deadline for editing your housing application passed on " . HMS_Deadlines::get_deadline_as_date('edit_application_end_timestamp', $deadlines) . ".";
        }

        /*************************************
         * 200810 Roommates Hack - TEMPORARY *
         *************************************/
         $tags['ROOMMATE_MSG'] = 'If you would like to request a roommate, follow the link below.  Please note that this step is optional, and due to limited housing availability, we can NOT guarantee that your roommate request will be honored for the Spring semester.';
         $tags['ROOMMATE_LINK'] = PHPWS_Text::secureLink(_('Request A Roommate'), 'hms', array('type'=>'student', 'op'=>'spring_roommate_hack'));
        
        # The following is entirely unimplemented and I don't have time to do it and Housing doesn't seem to want it anyway... we're going to do it eventually, but probably not for 200810
        /*********************
         * Verify Assignment *
         ********************
         $tags['VERIFY_INTRO'] = 'Once the assignment process is complete, you can verify your assignment and roommate selection.';

        # Check deadlines for verify assignment
        if(HMS_Deadlines::check_within_deadlines('view_assignment_begin_timestamp','view_assignment_end_timestamp',$deadlines)){
            $tags['VERIFY_MSG'] = 'You may verify your assignment until ' . HMS_Deadlines::get_deadline_as_date('view_assignment_begin_timestamp', $deadlines) . '. Click the link below to verify your assignment.';
            $tags['VERIFY_LINK'] = PHPWS_Text::secureLink(_('Verify Your Assignment'), 'hms', array('type'=>'student','op'=>'show_verify_assignment'));
        }else if(!HMS_Deadlines::check_deadline_past('view_assignment_begin_timestamp', $deadlines)){
            $tags['VERIFY_MSG'] = 'It is too early to view your assignment. You will be able to view your assignment on ' . HMS_Deadlines::get_deadline_as_date('view_assignment_begin_timestamp', $deadlines) . '.';
        }else{
            $tags['VERIFY_MSG'] = 'It is too late to view your assignment. The deadline past on ' . HMS_Deadlines::get_deadline_as_date('view_assignment_end_timestamp', $deadlines) . '.';
        }*/


        # Logout link
        $tags['LOGOUT_LINK'] = PHPWS_Text::secureLink(_('Log Out'), 'users', array('action'=>'user', 'command'=>'logout'));
        
        return PHPWS_Template::process($tags, 'hms', 'student/main_menu_spring.tpl');

    }

    function show_verify_assignment()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');

        $tpl = array();

        $assignment = HMS_Assignment::get_assignment($_SESSION['asu_username'], $_SESSION['application_term']);
        if($assignment === NULL || $assignment == FALSE){
            $tpl['NO_ASSIGNMENT'] = "You do not currently have a housing assignment.";
        }else{
            $tpl['ASSIGNMENT'] = $assignment->where_am_i() . '<br />';
            $tpl['ROOM_PHONE'] = $assignment->get_phone_number();

            # Determine the student's type and figure out their movein time
            $type = HMS_SOAP::get_student_type($_SESSION['asu_username'], $_SESSION['application_term']);

            if($type == TYPE_CONTINUING){
                $movein_time_id = $assignment->get_rt_movein_time_id();
            }else{
                $movein_time_id = $assignment->get_ft_movein_time_id();
            }
            
            if($movein_time_id == NULL){
                $tpl['MOVE_IN_TIME'] = 'To be determined<br />';
            }else{
                $movein_times = HMS_Movein_Time::get_movein_times_array($_SESSION['application_term']);        
                $tpl['MOVE_IN_TIME'] = $movein_times[$movein_time_id];
            }
        }

        $roommate = HMS_Roommate::get_confirmed_roommate($_SESSION['asu_username']);
        if($roommate == NULL){
            $tpl['ROOMMATE'] = 'You do not have a confirmed roommate.';
        }else{
            $tpl['ROOMMATE'] = 'Your confirmed roommate is: ' . HMS_SOAP::get_name($roommate) . ' (<a href="mailto:' . $roommate . '@appstate.edu">'. $roommate . '@appstate.edu</a>)';
        }

        $rlc_assignment = HMS_RLC_Assignment::check_for_assignment($_SESSION['asu_username'], $_SESSION['application_term']);
        if($rlc_assignment == NULL || $rlc_assignment === FALSE){
            $tpl['RLC'] = "You have not been accepted to an RLC.";
        }else{
            $rlc_list = HMS_Learning_Community::getRLCList();
            $tpl['RLC'] = 'You have been assigned to the ' . $rlc_list[$rlc_assignment['rlc_id']];
        }

        $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Back to Main Menu', 'hms', array('type'=>'student', 'op'=>'show_main_menu'));
        
        return PHPWS_Template::process($tpl, 'hms', 'student/verify_assignment.tpl');
    }

    /* 200810 Spring Roommate HACK.  This must be REMOVED and replaced
     * by something SANE before 200910.
     */
    function spring_roommate_hack()
    {
        $db = new PHPWS_DB('hms_roommate_hack');
        $db->addWhere('requestor', $_SESSION['asu_username'], 'ILIKE');
        $results = $db->select('row');

        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $form = new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'save_spring_roommate_hack');

        $form->addText('name');
        $form->setLabel('name', "Requested Roommate's Full Name");
        if(isset($results['requestee']))
            $form->setValue('name', $results['requestee']);

        $form->addText('username');
        $form->setLabel('username', "@appstate.edu  Requested Roommate's Email Address");
        $form->setSize('username',8);
        if(isset($results['requestee_username']))
            $form->setValue('username', $results['requestee_username']);

        $form->addSubmit('save', _('Save'));
        $form->addSubmit('cancel', _('Cancel Changes'));

        $tags = $form->getTemplate();
        $tags['WALL_OF_TEXT'] = _("Please specify your requested roommate's full name and ASU username below.  Note that we can NOT guarantee that your roommate request will be honored for the Spring semester.");

        return PHPWS_Template::process($tags, 'hms', 'student/roommate_hack.tpl');
    }

    /* Save data from the 200810 Spring Roommate HACK.  This must be
     * REMOVED and replaced by something SANE before 200910.
     */
    function save_spring_roommate_hack()
    {
        $db = new PHPWS_DB('hms_roommate_hack');
        $db->addWhere('requestor', $_SESSION['asu_username'], 'ILIKE');
        $db->delete();

        $db = new PHPWS_DB('hms_roommate_hack');
        $db->addValue('requestor', $_SESSION['asu_username']);
        $db->addValue('requestee', $_REQUEST['name']);
        $db->addValue('requestee_username', $_REQUEST['username']);
        $result = $db->insert();

        if(PHPWS_Error::isError($result)) {
            test($result, 1);
        }

        return "You have requested {$_REQUEST['name']} for your roommate.<br /><br />" . PHPWS_Text::secureLink(_('Return to Main Menu'), 'hms', array('type'=>'student', 'op'=>'main'));
    }

    function get_link($username, $show_user = FALSE)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $name = HMS_SOAP::get_full_name($username);

        $vars = array('type'     => 'student',
                      'op'       => 'get_matching_students',
                      'username' => $username);
        $link = PHPWS_Text::secureLink($name, 'hms', $vars);

        if($show_user) {
            return $link . " (<em>$username</em>)";
        }

        return $link;
    }
    
    function main()
    {

        # Check to make sure the 'op' variable is set, if not bail out here
        if(!isset($_REQUEST['op'])){
            PHPWS_Core::killAllSessions();
            PHPWS_Core::home();
        }
        
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
            case 'show_terms_and_agreement_only':
                # This is used to just show the terms & agreement, and then go back to the main menu (not part of application process)
                return HMS_Student::show_terms_and_agreement(TRUE);
                break;
            case 'agreed_to_terms':
                HMS_Student::agreed_to_terms();
                return HMS_Student::show_main_menu();
                break;
            case 'show_terms_and_agreement':
               return HMS_Student::show_terms_and_agreement();
               break;
            case 'begin_application':
                # Check to see if the user hit 'do not agree' on the terms/agreement page
                if(isset($_REQUEST['quit'])) {
                    PHPWS_Core::killAllSessions();
                    PHPWS_Core::home();
                }

                # Log the fact that the user agreed to the terms and agreemnts
                PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
                HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_AGREED_TO_TERMS, $_SESSION['asu_username'], NULL);
                
                # Show the side thingie
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show(FALSE);
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::display_application_form();
                break;
            case 'review_application':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show(FALSE);
                PHPWS_Core::initModClass('hms','HMS_Application.php');
                return HMS_Application::display_application_form(TRUE);
                break;
            case 'show_main_menu':
                return HMS_Student::show_main_menu();
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
                return HMS_Application::view_housing_application($_REQUEST['user']);
                break;
            case 'show_profile':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_PROFILE);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms', 'HMS_Student_Profile.php');
                return HMS_Student_Profile::show_profile($_REQUEST['user']);
                break;
            case 'show_rlc_application_form':
                PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
                return HMS_RLC_Application::show_rlc_application_form();
                break;
            case 'view_rlc_application':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_RLC);
                $side_thingie->show();
                PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
                return HMS_RLC_Application::view_rlc_application();
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
            case 'show_request_roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                return HMS_Roommate::show_request_roommate();
                break;
            case 'request_roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                return HMS_Roommate::create_roommate_request();
                break;
            case 'roommate_confirm_rlc_removal':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                return HMS_Roommate::create_roommate_request(TRUE);
                break;
            case 'set_meal_plan':
                return HMS_Student::set_meal_plan();
                break;
            case 'show_contact_form':
                PHPWS_Core::initModClass('hms', 'HMS_Contact_Form.php');
                return HMS_Contact_Form::show_contact_form();
                break;
            case 'submit_contact_form':
                PHPWS_Core::initModClass('hms', 'HMS_Contact_Form.php');
                return HMS_Contact_Form::submit_contact_form();
                break;
            case 'show_roommate_confirmation':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                $mate = &new HMS_Roommate($_REQUEST['id']);
                return HMS_Roommate::show_approve_reject($mate);
                break;
            case 'confirm_accept_roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                $mate = &new HMS_Roommate($_REQUEST['id']);
                return HMS_Roommate::confirm_accept($mate);
                break;
            case 'for_realz_accept_roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                $mate = &new HMS_Roommate($_REQUEST['id']);
                return HMS_Roommate::accept_for_realz($mate);
                break;
            case 'confirm_reject_roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                $mate = &new HMS_Roommate($_REQUEST['id']);
                return HMS_Roommate::reject_for_realz($mate);
                break;
            case 'show_verify_assignment':
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_VERIFY);
                $side_thingie->show();
                return HMS_Student::show_verify_assignment();                
                break;
            case 'main':
                //return HMS_Student::show_main_menu();
                return HMS_Student::show_welcome_screen();
                break;
            default:
                return "unknown student op: {$_REQUEST['op']} <br />";
                break;
        }
    }
};
?>
