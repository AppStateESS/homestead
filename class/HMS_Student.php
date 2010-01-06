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

    public function HMS_Student($asu_username = NULL) {
        $this->asu_username = $asu_username;
    }

    # Used to set 'agreed_to_terms' true after a user has already applied
    # TODO: Remove this function? There is no longer an 'agreed_to_terms' field in the hms_new_application table
    public function agreed_to_terms()
    {
        /*
        $db = new PHPWS_DB('hms_new_application');
        $db->addwhere('hms_student_id', $_SESSION['asu_username'], 'ILIKE');
        $db->addValue('agreed_to_terms', 1);
        $result = $db->update();

        PHPWS_Error::logIfError($result);
        */
        
        # Log the fact that the user agreed to the terms and agreemnts
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_AGREED_TO_TERMS, $_SESSION['asu_username'], NULL);

        return;
    }
   
    /******************
     * Static Methods *
     ******************/
     
    public function main()
    {
        # Check to make sure the 'op' variable is set, if not bail out here
        if(!isset($_REQUEST['op'])){
            //PHPWS_Core::killAllSessions();
            //PHPWS_Core::home();
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
                PHPWS_Core::initModClass('hms', 'HMS_Application.php');
                return HMS_Application::admin_report_to_banner($_REQUEST['username'], (isset($_REQUEST['term']) ? $_REQUEST['term'] : NULL));
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
                    return Application_UI::view_housing_application($_REQUEST['student'],HMS_SOAP::get_application_term($_REQUEST['student']));
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
                return HMS_Roommate::create_roommate_request(FALSE);
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
                return HMS_Roommate::confirm_accept($mate, NULL);
                break;
            case 'for_realz_accept_roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                $mate = &new HMS_Roommate($_REQUEST['id']);
                return HMS_Roommate::accept_for_realz($mate, $_SESSION['application_term']);
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
            case 'returning_menu':
                PHPWS_Core::initModclass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_returning_menu();
                break;
            case 'show_lottery_signup':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_lottery_signup();
            case 'lottery_signup_submit':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::lottery_signup_submit();
                break;
            case 'lottery_signup_special_needs':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::lottery_signup();
                break;
            case 'lottery_select_residence_hall':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_select_residence_hall();
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
            case 'lottery_show_roommate_request':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_lottery_roommate_request();
                break;
            case 'lottery_show_confirm_roommate_request':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_confirm_lottery_roommate_request();
                break;
            case 'lottery_confirm_roommate_request':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::handle_lottery_roommate_confirmation();
                break;
            case 'summer_application_begin':
                PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');
                return Application_UI::showTermsAndAgreement(false, 'summer_application_form', $_REQUEST['term']);
            case 'summer_application_form':
                # Check to see if the user hit 'do not agree' on the terms/agreement page
                if(isset($_REQUEST['quit'])) {
                    PHPWS_Core::killAllSessions();
                    PHPWS_Core::reroute('https://weblogin.appstate.edu/cosign-bin/logout');
                }

                PHPWS_Core::initModClass('hms', 'UI/SummerApplicationUI.php');
                $summerUI = new SummerApplicationUI(NULL, $_REQUEST['term']);
                return $summerUI->showForm();
                break;
            case 'summer_application_submit':
                PHPWS_Core::initModClass('hms', 'SummerApplicationControl.php');
                return SummerApplicationControl::summer_application_submit();
                break;
            case 'summer_application_confirmation':
                PHPWS_Core::initModClass('hms', 'SummerApplicationControl.php');
                return SummerApplicationControl::summer_application_confirmation();
                break;
            case 'summer_application_save':
                PHPWS_Core::initModClass('hms', 'SummerApplicationControl.php');
                return SummerApplicationControl::summer_application_save();
                break;
            case 'logout':
                PHPWS_Core::killAllSessions();
                PHPWS_Core::reroute('https://weblogin.appstate.edu/cosign-bin/logout');
                break;
            case 'main':
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_welcome_screen();
                break;
            default:
                PHPWS_Core::initModClass('hms', 'UI/Student_UI.php');
                return HMS_Student_UI::show_welcome_screen();
                break;
        }
    }

    /*********************
     * Static UI Methods *
     *********************/

    public function get_link($username, $show_user = FALSE)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $name = HMS_SOAP::get_full_name($username);

        $vars = array('type'     => 'student',
                      'op'       => 'get_matching_students',
                      'username' => $username);
        $link = PHPWS_Text::secureLink($name, 'hms', $vars, NULL, NULL, 'username');

        if($show_user) {
            return $link . " (<em>$username</em>)";
        }

        return $link;
    }
};
?>
