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

    function HMS_Student($asu_username = NULL) {
        $this->asu_username = $asu_username;
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
   
    /******************
     * Static Methods *
     ******************/
     
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
            case 'admin_report_application':
                PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
                PHPWS_Core::initModClass('hms', 'HMS_Term.php');
                // TODO: Permission For This
                $result = NULL;
                $error  = NULL;
                if(isset($_REQUEST['username'])) {
                    $result = HMS_SOAP::report_application_received($_REQUEST['username'], HMS_Term::get_selected_term());
                } else {
                    $error = 'No username provided for application.';
                }
                if(!is_null($result) && $result != 0) {
                    $error = 'Reporting Application: Banner Error: ' . $result;
                } else {
                    $error = 'Reporting Application: Successful';
                }
                return HMS_Student::get_matching_students($error);
                break;
            case 'get_matching_students':
                return HMS_Student::get_matching_students();
                break;
            case 'show_terms_and_agreement_only':
                # This is used to just show the terms & agreement, and then go back to the main menu (not part of application process)
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_terms_and_agreement(TRUE);
                break;
            case 'agreed_to_terms':
                HMS_Student::agreed_to_terms();
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_main_menu();
                break;
            case 'show_terms_and_agreement':
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_terms_and_agreement();
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
                PHPWS_Core::initModClass('hms','UI/Application_UI.php');
                return Application_UI::show_housing_application();
                break;
            case 'submit_application':
                # Show the side thingie
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show(FALSE);

                PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');
                return Application_UI::submit_application();
                break;
            case 'submit_application_special_needs':
                # Show the side thingie
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show(FALSE);

                PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');
                return Application_UI::show_application_review();
            case 'redo_application':
                # Show the side thingie
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show(FALSE);
                
                PHPWS_Core::initModClass('hms','UI/Application_UI.php');
                return Application_UI::show_housing_application();
                break;
            case 'submit_application_review':
                # Show the side thingie
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show(FALSE);

                PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');
                return Application_UI::submit_application_review();
            case 'view_application':
                # Show the side thingie
                PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_APPLY);
                $side_thingie->show();
                
                PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');
                # Could be an admin or studnet, so figure out which data to use
                if(isset($_SESSION['asu_username'])){
                    return Application_UI::view_housing_application($_SESSION['asu_username'],$_SESSION['application_term']);
                }else{
                    return Application_UI::view_housing_application($_REQUEST['student'],HMS_SOAP::get_application_term($_RQUEST['student']));
                }
            case 'show_main_menu':
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_main_menu();
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
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_VERIFY);
                $side_thingie->show();
                return HMS_Student_UI::show_verify_assignment();                
                break;
            case 'lottery_signup_submit':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::lottery_signup_submit();
                break;
            case 'lottery_signup_special_needs':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::lottery_signup();
                break;
            case 'lottery_select_floor':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_select_floor();
                break;
            case 'lottery_select_room':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_select_room();
                break;
            case 'lottery_select_roommates': 
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_select_roommates();
                break;
            case 'lottery_show_confirm_roommates':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_confirm_roommates();
                break;
            case 'lottery_confirmed':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_confirmed();
                break;
            case 'lottery_show_confirm_roommate_request':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_confirm_lottery_roommate_request();
                break;
            case 'lottery_confirm_roommate_request':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::handle_lottery_roommate_confirmation();
                break;
            case 'main':
                //return HMS_Student::show_main_menu();
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_welcome_screen();
                break;
            default:
                return "unknown student op: {$_REQUEST['op']} <br />";
                break;
        }
    }

    /*********************
     * Static UI Methods *
     *********************/

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

    function enter_student_search_data($error = null)
    {
        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        javascript('/modules/hms/autosuggest');
        Layout::addStyle('hms', 'css/autosuggest.css');
        
        $form = &new PHPWS_Form('student_search_form');

        $form->setMethod('get');
        
        $form->addCheckBox('enable_autocomplete');
        $form->setLabel('enable_autocomplete', 'Enable Auto-complete: ');
        $form->setExtra('enable_autocomplete', 'checked');
        
        $form->addText('username');
        $form->setExtra('username', 'autocomplete="off" ');
        
        $form->addSubmit('submit_button', _('Submit'));

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'get_matching_students');

        $tpl = $form->getTemplate();
        $tpl['TITLE'] = "Student Search";
        $tpl['MESSAGE'] = "What ASU username would you like to look for?<br />";
        if(isset($error)) {
            $tpl['ERROR'] = $error;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/get_single_username.tpl');
    }

    function get_matching_students($error = NULL)
    {   
        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        if(isset($_REQUEST['actee'])){
            $username = $_REQUEST['actee'];
        }
        elseif(isset($_REQUEST['username'])){
            $username = $_REQUEST['username'];
        }
        
        if(!isset($username)) {
            $error = "You did not provide an ASU username.<br />";
            return HMS_Student::enter_student_search_data($error);
        } else if (!PHPWS_Text::isValidInput($username)) {
            $error = "ASU usernames can only be alphanumeric.<br />";
            return HMS_Student::enter_student_search_data($error);
        }

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $student_info = HMS_SOAP::get_student_info($username, HMS_Term::get_selected_term());
        
        //Add a note if we're returning to this page after clicking the "Add Note" link
        if(isset($_REQUEST['note'])){
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($username, ACTIVITY_ADD_NOTE, Current_User::getUsername(), $_REQUEST['note']);
        }

        #test($student_info);

        if(!is_null($error)) {
            $tpl['ERROR'] = $error;
        }

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
            $tpl['CLASS'] = 'Freshman';
        } else if ($student_info->projected_class == CLASS_SOPHOMORE) {
            $tpl['CLASS'] = 'Sophomore';
        } else if ($student_info->projected_class == CLASS_JUNIOR) {
            $tpl['CLASS'] = 'Junior';
        } else if ($student_info->projected_class == CLASS_SENIOR) {
            $tpl['CLASS'] = 'Senior';
        } else {
            $tpl['CLASS'] = "Unknown class: ({$student_info->projected_class})";
        }

        switch($student_info->student_type){
            case TYPE_FRESHMEN:
                $tpl['TYPE'] = 'Freshmen';
                break;
            case TYPE_TRANSFER:
                $tpl['TYPE'] = 'Transfer';
                break;
            case TYPE_CONTINUING:
                $tpl['TYPE'] = 'Continuing';
                break;
            case TYPE_RETURNING:
                $tpl['TYPE'] = 'Returning';
                break;
            case TYPE_READMIT:
                $tpl['TYPE'] = 'Re-admit';
                break;
            case TYPE_WITHDRAWN:
                $tpl['TYPE'] = 'Withdrawn';
                break;
            default:
                $tpl['TYPE'] = 'Unknown type: ' . $student_info->student_type;
                break;
        }
                

        $tpl['APPLICATION_TERM'] = $student_info->application_term;
        
        /*************
         * Addresses *
         *************/
        $pr_address = HMS_SOAP::get_address($username, ADDRESS_PRMT_RESIDENCE);

        $tpl['PR_ADDRESS_L1']       = $pr_address->line1;
        if(isset($pr_address->line2) && $pr_address->line2 != '')
            $tpl['PR_ADDRESS_L2']       = $pr_address->line2;
        if(isset($pr_address->line3) && $pr_address->line3 != '')
            $tpl['PR_ADDRESS_L3']       = $pr_address->line3;
        $tpl['PR_ADDRESS_CITY']     = $pr_address->city;
        $tpl['PR_ADDRESS_STATE']    = $pr_address->state;
        $tpl['PR_ADDRESS_ZIP']      = $pr_address->zip;

        $ps_address = HMS_SOAP::get_address($username, ADDRESS_PRMT_STUDENT);

        $tpl['PS_ADDRESS_L1']       = $ps_address->line1;
        if(isset($ps_address->line2) && $ps_address->line2 != '')
            $tpl['PS_ADDRESS_L2']       = $ps_address->line2;
        if(isset($ps_address->line3) && $ps_address->line3 != '')
            $tpl['PS_ADDRESS_L3']       = $ps_address->line3;
        $tpl['PS_ADDRESS_CITY']     = $ps_address->city;
        $tpl['PS_ADDRESS_STATE']    = $ps_address->state;
        $tpl['PS_ADDRESS_ZIP']      = $ps_address->zip;

        # Add a blank line between the addresses if they're both set
        if($pr_address->line1 != '' && $ps_address->line1 != ''){
            $tpl['ADDRESS_SPACE'] = '';
        }
        
        /****************
         * Phone number *
         ****************/
        
        $tpl['PHONE_AC'] = $student_info->phone->area_code;
        $tpl['PHONE_NUMBER'] = $student_info->phone->number;
        $tpl['USERNAME'] = $username;

        $tpl['TITLE'] = "Search Results - " . HMS_Term::term_to_text(HMS_Term::get_selected_term(),TRUE);

        $tpl['APPLICATION_TERM'] = HMS_SOAP::get_application_term($username);

        $this_term = HMS_Term::get_selected_term();

        /**************
         * Assignment *
         **************/

        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        $assignment = HMS_Assignment::get_assignment($username, $this_term);

        if(isset($assignment) && $assignment != FALSE){
            $tpl['ASSIGNED'] = "Yes";
            $unassign = PHPWS_Text::secureLink('Unassign', 'hms', array('type'=>'assignment', 'op'=>'show_unassign_student', 'username'=>$username));
            $reassign = PHPWS_Text::secureLink('Reassign', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$username));
            $tpl['ROOM_ASSIGNMENT'] = $assignment->where_am_i(TRUE) . " | $reassign | $unassign";
        }else if($assignment == FALSE){
            $tpl['ASSIGNED'] = "No";
            $tpl['ROOM_ASSIGNMENT'] = '' . PHPWS_Text::secureLink('Assign student now', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$username));
        }else{
            $tpl['ASSIGNED'] = "No";
            $tpl['ROOM_ASSIGNMENT'] = "Error: Could not look up the current assignment. Please contact ESS.";
        }

        /******************
         * Roommate Stuff *
         ******************/
/*
         PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
         $roommates = HMS_Roommate::get_all_roommates($username, HMS_Term::get_selected_term());
         if($student_info->student_type == TYPE_FRESHMEN){
            if(empty($roommates)) {
                $tpl['ROOMMATE'] = "This person has no roommates or roommate requests.<br />";
            } else {
                foreach($roommates as $roommate) {
                    if($roommate->confirmed) {
                        $mate = $roommate->get_other_guy($username);
                        $user_link = PHPWS_Text::secureLink(HMS_SOAP::get_full_name($mate), 'hms',
                            array('type'=>'student',
                                  'op'=>'get_matching_students',
                                  'username'=>$mate));
                        $tpl['ROOMMATE'] .= "Confirmed roommates with $user_link<br />";
                    } else {
                        $mate = $roommate->get_other_guy($username);
                        $user_link = PHPWS_Text::secureLink(HMS_SOAP::get_full_name($mate), 'hms',
                            array('type'=>'student',
                                  'op'=>'get_matching_students',
                                  'username'=>$mate));
                        if($roommate->requestor == $username) {
                            $tpl['ROOMMATE'] .= "Awaiting approval from $user_link<br />";
                        } else {
                            $tpl['ROOMMATE'] .= "Request Pending from $user_link<br />";
                        }
                    }
                }
            }
        } else {
*/
            PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
            PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
            PHPWS_Core::initModClass('hms', 'HMS_Room.php');
            $assignment = HMS_Assignment::get_assignment($username);
            $pending    = HMS_Roommate::get_unconfirmed_roommate($username);
            $confirmed  = HMS_Roommate::get_confirmed_roommate($username);
            
            $tpl['ROOMMATE']           = "";
            $tpl['REQUESTED_ROOMMATE'] = "";
            
            if(!empty($assignment)){
                $room       = new HMS_Room($assignment->get_room_id());
                $roommies   = $room->get_assignees();

                if(!empty($confirmed)){
                    if(sizeof($roommies > 1)){
                        foreach($roommies as $roommie){
                            if($roommie->asu_username != $username){
                                if($student_info->student_type == TYPE_FRESHMEN 
                                   && $roommie->asu_username == $confirmed->asu_username){
                                    $tpl['ROOMMATE']            .= "<a href=index.php?module=hms&type=student&op=get_matching_students&username=$roommie->asu_username&tab=student_info>$roommie->asu_username</a><br />";
                                    $tpl['REQUESTED_ROOMMATE']  .= "<tr><td></td><td>Student Requested ".$confirmed->asu_username."<a href=index.php?module=hms&type=roommate&op=show_confirmed_roommates&search=&pg=1&limit=10&pager_c_search=".$username.">Break this roommate group</a><td></tr>";
                                } else {
                                    $tpl['ROOMMATE']            .= "<a href=index.php?module=hms&type=student&op=get_matching_students&username=$roommie->asu_username&tab=student_info>$roommie->asu_username</a><br />"; 
                                }
                            }
                        }
                    }
                } else {
                    foreach($roommies as $roommie){
                        if($roommie->asu_username != $username)
                            $tpl['ROOMMATE'] .= "<a href=index.php?module=hms&type=student&op=get_matching_students&username=$roommie->asu_username&tab=student_info>$roommie->asu_username</a><br />";
                    }
                }

            } else {
                if(empty($pending) && empty($confirmed)){
                    $tpl['ROOMMATE'] .= "None";
                    if($student_info->student_type == TYPE_FRESHMEN)
                        $tpl['REQUESTED_ROOMMATE'] .= "[<a href=index.php?module=hms&type=roommate&op=show_admin_create_roommate_group>Pair this student</a>]";
                } elseif(!empty($pending)){
                    $tpl['ROOMMATE'] .= "Pending Requests: ".$pending;
                }
            }
            if(sizeof($tpl['REQUESTED_ROOMMATE']) == 0)
                unset($tpl['REQUESTED_ROOMMATE']);

//        }


        /**************
         * RLC Status *
         **************/
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $rlc_names = HMS_Learning_Community::getRLCList();

        $rlc_assignment     = HMS_RLC_Assignment::check_for_assignment($username, HMS_Term::get_selected_term());
        $rlc_application    = HMS_RLC_Application::check_for_application($username, HMS_Term::get_selected_term(), FALSE);
        
        if($rlc_assignment != FALSE){
            $tpl['RLC_STATUS'] = "This student is assigned to: " . $rlc_names[$rlc_assignment['rlc_id']];
        }else if ($rlc_application != FALSE){
            $tpl['RLC_STATUS'] = "This student is currently awaiting RLC approval. You can view their application " . PHPWS_Text::secureLink(_('here'), 'hms', array('type'=>'rlc', 'op'=>'view_rlc_application', 'username'=>$username));
        }else{
            $tpl['RLC_STATUS'] = "This student is not in a Learning Community and has no pending approval.";
        }

        /**********************
         * Application Status *
         **********************/
        $report_app = '[<a href="index.php?module=hms&type=student&op=admin_report_application&username='.$username.'&tab=student_info">Report Application Received</a>]';
        if(HMS_Application::check_for_application($username, HMS_Term::get_selected_term(), TRUE)) {
            $tpl['APPLICATION'] = '[<a href="index.php?module=hms&type=student&op=get_matching_students&username='.$username.'&tab=housing_app">View Application</a>] '.$report_app;
            $app = &new HMS_Application($username, HMS_Term::get_selected_term());
            $tpl['APPLICATION_RECEIVED'] = 'Yes';
            
            if($app->meal_option == BANNER_MEAL_LOW) $tpl['MEAL_PLAN'] = "Low";
            else if($app->meal_option == BANNER_MEAL_STD) $tpl['MEAL_PLAN'] = "Standard";
            else if($app->meal_option == BANNER_MEAL_HIGH) $tpl['MEAL_PLAN'] = "High";
            else if($app->meal_option == BANNER_MEAL_SUPER) $tpl['MEAL_PLAN'] = "Super";
        
            /*************
             * Cellphone *
             *************/
            if(strlen($app->cellphone) == 10){
                $tpl['CELLPHONE']   .= '('.substr($app->cellphone, 0, 3).')';
                $tpl['CELLPHONE']   .= '-'.substr($app->cellphone, 3, 3);
                $tpl['CELLPHONE']   .= '-'.substr($app->cellphone, 6, 4);
            }
        } else {
            $tpl['APPLICATION']          = ''.$report_app;
            $tpl['APPLICATION_RECEIVED'] = "No";
            $tpl['MEAL_PLAN']            = 'None';
        }

        
        /********/
        /* Note */
        /********/
        $form = &new PHPWS_Form('add_note_dialog');
        $form->addTextarea('note');
        $form->addHidden('module',   'hms');
        $form->addHidden('type',     'student');
        $form->addHidden('op',       'get_matching_students');
        $form->addHidden('username', $username);
        $form->addSubmit('Add Note');

        $tpl = array_merge($tpl, $form->getTemplate());

        /********/
        /* Logs */
        /********/
        //TODO: Fix activity logs to accept a blacklist as well as a whitelist
        $everything_but_notes   = array();
        $everything_but_notes[] = ACTIVITY_LOGIN;
        $everything_but_notes[] = ACTIVITY_AGREED_TO_TERMS;
        $everything_but_notes[] = ACTIVITY_SUBMITTED_APPLICATION;
        $everything_but_notes[] = ACTIVITY_SUBMITTED_RLC_APPLICATION;
        $everything_but_notes[] = ACTIVITY_ACCEPTED_TO_RLC;
        $everything_but_notes[] = ACTIVITY_TOO_OLD_REDIRECTED;
        $everything_but_notes[] = ACTIVITY_REQUESTED_AS_ROOMMATE;
        $everything_but_notes[] = ACTIVITY_REJECTED_AS_ROOMMATE;
        $everything_but_notes[] = ACTIVITY_ACCEPTED_AS_ROOMMATE;
        $everything_but_notes[] = ACTIVITY_PROFILE_CREATED;
        $everything_but_notes[] = ACTIVITY_ASSIGNED;
        $everything_but_notes[] = ACTIVITY_AUTO_ASSIGNED;
        $everything_but_notes[] = ACTIVITY_REMOVED;
        $everything_but_notes[] = ACTIVITY_ASSIGNMENT_REPORTED;
        $everything_but_notes[] = ACTIVITY_REMOVAL_REPORTED;
        $everything_but_notes[] = ACTIVITY_LETTER_PRINTED;
        $everything_but_notes[] = ACTIVITY_BANNER_ERROR;
        $everything_but_notes[] = HMS_MULTIPLE_ASSIGNMENTS;
        $everything_but_notes[] = ACTIVITY_LOGIN_AS_STUDENT;
        $everything_but_notes[] = ACTIVITY_ADMIN_ASSIGNED_ROOMMATE;
        $everything_but_notes[] = ACTIVITY_ADMIN_REMOVED_ROOMMATE;
        $everything_but_notes[] = ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ;
        $everything_but_notes[] = ACTIVITY_WITHDRAWN_APP;
        $everything_but_notes[] = ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED;
        $everything_but_notes[] = ACTIVITY_WITHDRAWN_ROOMMATE_DELETED;
        $everything_but_notes[] = ACTIVITY_WITHDRAWN_RLC_APP_DENIED;
        $everything_but_notes[] = ACTIVITY_WITHDRAWN_RLC_ASSIGN_DELETED;
        $everything_but_notes[] = ACTIVITY_APPLICATION_REPORTED;
        $everything_but_notes[] = ACTIVITY_DENIED_RLC_APPLICATION;
        $everything_but_notes[] = ACTIVITY_UNDENIED_RLC_APPLICATION;
        $everything_but_notes[] = ACTIVITY_ASSIGN_TO_RLC;
        $everything_but_notes[] = ACTIVITY_RLC_APP_SUBMITTED;
        $everything_but_notes[] = ACTIVITY_USERNAME_UPDATED;
        $everything_but_notes[] = ACTIVITY_APPLICATION_UPDATED;
        $everything_but_notes[] = ACTIVITY_RLC_APPLICATION_UPDATED;
        $everything_but_notes[] = ACTIVITY_ASSIGNMENTS_UPDATED;
        $everything_but_notes[] = ACTIVITY_BANNER_QUEUE_UPDATED;
        $everything_but_notes[] = ACTIVITY_ROOMMATES_UPDATED;
        $everything_but_notes[] = ACTIVITY_ROOMMATE_REQUESTS_UPDATED;
        $everything_but_notes[] = ACTIVITY_CHANGE_ACTIVE_TERM;

        if( Current_User::allow('hms', 'view_activity_log') && Current_User::allow('hms', 'view_student_log') ){
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            $tpl['LOG_PAGER'] = HMS_Activity_Log::showPager(null, $username, TRUE, null, null, null, $everything_but_notes, 5, true);
            $tpl['NOTE_PAGER'] = HMS_Activity_Log::showPager(null, $username, TRUE, null, null, null, array(0 => ACTIVITY_ADD_NOTE), 5, true);

            $tpl['LOG_PAGER'] .= '<div align=center>[<a href="index.php?module=hms&type=student&op=get_matching_students&username='.$username.'&tab=student_logs">View More</a>]';
            //TODO: if we ever renumber the activities change a40 to the number of
            //the new logging activity
            $tpl['NOTE_PAGER'] .= '<div align=center>[<a href="index.php?module=hms&type=student&op=get_matching_students&username='.$username.'&tab=student_logs&a40=1">View More</a>]';
        }

        /********************
         * Login as Student *
         ********************/
        if( Current_User::allow('hms', 'login_as_student') ) { 
            $tpl['LOGIN_AS_STUDENT'] = '<a href=index.php?module=hms&op=main&login_as_student=' . $username . '> '. $username . '</a></td></tr>';
        }

        //test($tpl, 1);
        $final = PHPWS_Template::process($tpl, 'hms', 'student/fancy_student_info.tpl');

        /***********************/
        /* Tabify Student Info */
        /***********************/
        $link    = "index.php?module=hms&type=student&op=get_matching_students&username=" . $username;
        $content = $final;
        if( isset($_REQUEST['tab']) ){
            switch( $_REQUEST['tab'] ){
                case 'student_info':
                    $content = $final;
                    break;
                case 'housing_app':
                    PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');
                    $content = Application_UI::view_housing_application($username, HMS_Term::get_selected_term());
                    break;
                case 'student_logs':
                    PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
                    $_REQUEST['actee'] = $username;
                    $_REQUEST['actor'] = $username;
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
        if(Current_User::allow('hms', 'view_student_log')){
            $tags['student_logs']   = array('title' => 'Student Logs', 'link' => $link,
                                            'link_title' => 'Student Logs');
        } else {
            $tags['student_logs']   = array();
        }

        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $panel = &new PHPWS_Panel('studentInfo');
        $panel->quickSetTabs($tags);
        if( !isset($_REQUEST['tab']) ){
            $panel->setCurrentTab('student_info');
        }

        /***************************/
        /* Javascript Enhancements */
        /***************************/

        javascript('/jquery/');
        javascript('/modules/hms/jquery_ui/');
        javascript('/modules/hms/student_info/');

        return $panel->display($content);
    }
};
?>
