<?php

/**
 * Learning Community objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Learning_Community
{
    var $id;
    var $community_name;
    var $abbreviation;
    var $capacity;
    var $error;

    function HMS_Learning_Community()
    {
        $this->id = NULL;
        $this->community_name = NULL;
        $this->error = "";
    }
    
    function set_error_msg($msg)
    {
        $this->error .= $msg;
    }

    function get_error_msg()
    {
        return $this->error;
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_community_name($name)
    {
        $this->community_name = $name;
    }

    function get_community_name()
    {
        return $this->community_name;
    }

    function set_abbreviation($abb)
    {
        $this->abbreviation = $abb;
    }

    function get_abbreviation()
    {
        return $this->abbreviation;
    }

    function set_capacity($cap)
    {
        $this->capacity = $cap;
    }

    function get_capacity()
    {
        return $this->capacity;
    }

    function set_variables()
    {
        if(isset($_REQUEST['id']) && $_REQUEST['id'] != NULL) $this->set_id($_REQUEST['id']);
        $this->set_community_name($_REQUEST['community_name']);
        $this->set_abbreviation($_REQUEST['abbreviation']);
        $this->set_capacity($_REQUEST['capacity']);
    }

    function save_learning_community()
    {
        $rlc = new HMS_Learning_Community();
        $rlc->set_variables();

        $db = & new PHPWS_DB('hms_learning_communities');
        
        if($rlc->get_id() != NULL) {
            $db->addWhere('id', $rlc->get_id());
            $success = $db->saveObject($rlc);
        } else {
            $db->addValue('community_name', $rlc->get_community_name());
            $db->addValue('abbreviation', $rlc->get_abbreviation());
            $db->addValue('capacity', $rlc->get_capacity());
            $success = $db->insert();
        }
        
        if(PEAR::isError($success)) {
            $msg = '<font color="red"><b>There was a problem saving the ' . $rlc->get_community_name() . ' Learning Community</b></font>';
        } else {
            $msg    = "The Residential Learning Community " . $rlc->get_community_name() . " was saved successfully!";
        }
        
        $final  = HMS_Learning_Community::add_learning_community($msg);

        return $final;
    }
    
    /*
     * Uses the HMS_Forms class to display the student rlc signup form/application
     */
    //TODO: MOVE THIS TO HMS_RLC_Application
    function show_rlc_application_form()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        if(HMS_RLC_Application::check_for_application($_SESSION['asu_username'], $_SESSION['application_term']) !== FALSE){
            $template['MESSAGE'] = "Sorry, you can only submit one RLC application.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }

        # Check deadlines
        $db = &new PHPWS_DB('hms_deadlines');
        $deadlines = $db->select('row');

        if(PEAR::isError($deadlines)){
            PHPWS_Error::log($deadlines);
            $template['MESSAGE'] = "Sorry, there was an error communicating with the database.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }

        $curr_timestamp = mktime();

        // TODO: change this block so it uses the deadlines class.
        if($curr_timestamp < $deadlines['submit_application_begin_timestamp']){
            $template['MESSAGE'] = "Sorry, it is too soon to fill out an RLC application.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }else if($curr_timestamp > $deadlines['submit_rlc_application_end_timestamp']){
            $template['MESSAGE'] = "Sorry, the RLC application deadline has already passed. Please contact Housing & Residence Life if you are interested in applying for a RLC.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }    
        
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        return HMS_RLC_Application::show_rlc_application_form_page1();
    }

    /*
     * Returns a HMS_Form that prompts the user for the name of the RLC to add
     */
    function add_learning_community($msg = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::add_learning_community($msg);
    }
   
    /*
     * Returns a HMS_Form that allows the user to select a RLC to delete
     */
    function select_learning_community_for_delete()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::select_learning_community_for_delete();
    }

    /*
     * Returns a HMS_Form that allows the user to confirm deletion of a RLC
     */
    function confirm_delete_learning_community()
    {
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id', $_REQUEST['lcs']);
        $result = $db->select('one');
      
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'delete_learning_community');
        $form->addHidden('community_name', $result);
        $form->addHidden('id', $_REQUEST['lcs']);
        $form->addSubmit('delete', _('Delete Community'));
        $form->addSubmit('save', _('Keep this Community'));
        
        $tpl = $form->getTemplate();

        $tpl['RLC']     = $result;
        $tpl['TITLE']   = "Confirm Deletion";
        $final = PHPWS_Template::process($tpl, 'hms', 'admin/confirm_learning_community_delete.tpl');

        return $final;
    }

    /*
     * Actually deletes a learning community
     */
    function delete_learning_community()
    {
        if(!isset($_REQUEST['delete']) || $_REQUEST['delete'] != "Delete Community") {
            return HMS_Learning_Community::select_learning_community_for_delete();
        }

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addWhere('id', $_REQUEST['id']);
        $db->addWhere('community_name', $_REQUEST['community_name']);
        $result = $db->delete();

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $count = $db->select('count');
       
        if($count == NULL) {
            $msg = "You have deleted the last residential learning community.";
            return HMS_Learning_Community::add_learning_community($msg);
        }

        return HMS_Learning_Community::select_learning_community_for_delete();
    }
  
    /*
     * Let admins get a roster for a particular learning community
     */
    function search_by_rlc()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::search_by_rlc();
    } 

    /*
     * Actually display the roster for the rlc specified in search_by_rlc
     */
    function view_by_rlc()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addWhere('id', $_REQUEST['rlc']);
        $db->addColumn('community_name');
        $tpl['TITLE'] = $db->select('one');

        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('rlc_id', $_REQUEST['rlc']);
        $db->addColumn('asu_username');
        $usernames = $db->select();

        if($usernames != NULL && $usernames != FALSE) {
            foreach($usernames as $user) {
                $tags['FIRST_NAME']     = HMS_SOAP::get_first_name($user['asu_username']);
                $tags['MIDDLE_NAME']    = HMS_SOAP::get_middle_name($user['asu_username']);
                $tags['LAST_NAME']      = HMS_SOAP::get_last_name($user['asu_username']);
                $tags['GENDER']         = HMS_SOAP::get_gender($user['asu_username']);

                $tags['USERNAME']       = $user['asu_username'];
                $tags['EMAIL']          = $user['asu_username'] . '@appstate.edu';

                $tags['VIEW_APP']       = './index.php?module=hms&type=rlc&op=view_rlc_application&username='.$user['asu_username'];

                $tags['ACTIONS']        = '<a href="./index.php?module=hms&type=rlc&op=confirm_remove_from_rlc&username='.$user['asu_username'].'">Remove</a>';

                $new_tpl['ROWS'] .= PHPWS_Template::processTemplate($tags, 'hms', 'admin/full_name_gender_email.tpl');
            }
            $content = PHPWS_Template::processTemplate($new_tpl, 'hms', 'admin/rlc_roster_table.tpl');
        } else {
            $content = 'There are no students assigned to this Learning Community';
        }

        $tpl['MESSAGE'] = $content;
        $tpl['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to Maintenance'), 'hms', array('type'=>'maintenance', 'op'=>'show_maintenance_options'));
        return PHPWS_Template::processTemplate($tpl, 'hms', 'admin/rlc_roster.tpl');
    }

    /*
     * Verify that the user actually wants to remove this student from an RLC
     */
    function confirm_remove_from_rlc()
    {
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('hms_learning_communities.community_name');
        $db->addColumn('hms_learning_communities.id', NULL, 'rlc_id');
        $db->addColumn('hms_learning_community_assignment.id', NULL, 'ass_id');
        $db->addWhere('hms_learning_communities.id', 'hms_learning_community_assignment.rlc_id');
        $db->addWhere('hms_learning_community_assignment.asu_username', $_REQUEST['username'], 'ILIKE');
        $result = $db->select('row');

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'perform_remove_from_rlc');
        $form->addHidden('username', $_REQUEST['username']);
        $form->addHidden('assignment', $result['ass_id']);
        $form->addHidden('rlc', $result['rlc_id']);
        $form->addSubmit('remove', _('Remove from RLC and Re-Activate Application'));
        $form->addSubmit('cancel', _('Do Nothing'));

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $tpl = $form->getTemplate();
        $tpl['NAME'] = HMS_SOAP::get_name($_REQUEST['username']);
        $tpl['RLC'] = $result['community_name'];

        return PHPWS_Template::process($tpl, 'hms', 'admin/confirm_remove_from_rlc.tpl');
    }

    /*
     * Actually remove a user from an RLC
     */
    function perform_remove_from_rlc()
    {
        if(!isset($_REQUEST['remove']) || $_REQUEST['remove'] != "Remove from RLC and Re-Activate Application" || !isset($_REQUEST['assignment'])) {
            return HMS_Learning_Community::view_by_rlc();
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment');

        $db = &new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('hms_assignment_id', $_REQUEST['assignment']);
        $db->addValue('hms_assignment_id', null);
        $db->update();

        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('id', $_REQUEST['assignment']);
        $db->delete();

        return "Deleted.";
    }
    
    /**
     * Returns an associative array containing the list of RLC abbreviations keyed by their id.
     */
    function getRLCListAbbr()
    {
        $db = &new PHPWS_DB('hms_learning_communities');

        $db->addColumn('id');
        $db->addColumn('abbreviation');
        $result = $db->select('assoc');
        return $result;
    }

    /**
     * Returns an associative array containing the list of RLCs using their full names, keyed by their id.
     */
    function getRLCList()
    {
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        $rlc_choices = $db->select('assoc');

        if(PEAR::isError($rlc_choices)){
            #PHPWS_Error::log();
        }

        return $rlc_choices;
    }

    /*
     * Main function for RLC maintenance
     */
    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'add_learning_community':
                return HMS_Learning_Community::add_learning_community();
                break;
            case 'save_learning_community':
                return HMS_Learning_Community::save_learning_community();
                break;
            case 'select_learning_community_for_delete':
                return HMS_Learning_Community::select_learning_community_for_delete();
                break;
            case 'delete_learning_community':
                return HMS_Learning_Community::delete_learning_community();
                break;
            case 'confirm_delete_learning_community':
                return HMS_Learning_Community::confirm_delete_learning_community();
                break;
            case 'assign_applicants_to_rlcs':
                return HMS_Learning_Community::assign_applicants_to_rlcs();
                break;
            case 'view_rlc_assignments':
                return HMS_Learning_Community::view_rlc_assignments();
                break;
            case 'view_rlc_application':
                return HMS_Learning_Community::view_rlc_application($_REQUEST['username']);
                break;
            case 'rlc_assignments_submit':
                return HMS_Learning_Community::rlc_assignments_submit();
                break;
            case 'rlc_application_export':
                return HMS_Learning_Community::rlc_application_export();
                break;
            case 'search_by_rlc':
                return HMS_Learning_Community::search_by_rlc();
                break;
            case 'view_by_rlc':
                return HMS_Learning_Community::view_by_rlc();
                break;
            case 'confirm_remove_from_rlc':
                return HMS_Learning_Community::confirm_remove_from_rlc();
                break;
            case 'perform_remove_from_rlc':
                return HMS_Learning_Community::perform_remove_from_rlc();
                break;
            default:
                return "{$_REQUEST['op']} <br />";
                break;
        }
    }

    /*
     * Validates submission of the first page of the rlc application form.
     * If ok, shows the second page of the application form.
     * Otherwise, displays page one again with an error message.
     */
     //TODO: move this to HMS_RLC_Application
    function rlc_application_page1_submit()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');

        # Check for invalid input on page 1
        $message = HMS_RLC_Application::validate_rlc_application_page1();
        if($message !== TRUE){
            # Show page one again with error message
            return HMS_RLC_Application::show_rlc_application_form_page1($message);
        }else{
            return HMS_RLC_Application::show_rlc_application_form_page2();
        }
    }

    //TODO: add comments and move this to HMS_RLC_Application
    function rlc_application_page2_submit()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        
        $template = array();

        # Check for invalid input on page 2
        $message = HMS_RLC_Application::validate_rlc_application_page2();
        if($message !== TRUE){
            # Show page two again with error message
            return HMS_RLC_Application::show_rlc_application_form_page2($message);
        }else{

            # Save the data to the database
            $result = HMS_RLC_Application::check_for_application($_SESSION['asu_username']);

            # Check to make sure an RLC application doesn't already exist
            if(!(PEAR::isError($result)) && $result !== FALSE){
                $template['MESSAGE'] = "Sorry, you have already submitted an RLC Application.";
                return PHPWS_Template::process($template, 'hms','student/student_success_failure_message.tpl');
            }
            
            $result = HMS_RLC_Application::save_application();

            # Check for an error
            if(PEAR::isError($result)){
                $template['MESSAGE'] = "Sorry, there was an error working with the database. Your application could not be saved.";
            }else{
                $template['SUCCESS'] = "Your application was submitted successfully.";
                $template['SUCCESS'] .= "<br /><br />";
                $template['SUCCESS'] .= PHPWS_Text::secureLink(_('Back to Main Menu'), 'hms', array('type'=>'student','op'=>'main'));
            }
            
            return PHPWS_Template::process($template, 'hms', 'student/rlc_signup_confirmation.tpl');
        }

    }

    function assign_applicants_to_rlcs()
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        $tags = array();
        $tags['TITLE']             = 'RLC Applications';
        $tags['SUMMARY']           = HMS_Learning_Community::display_rlc_assignment_summary();
        $tags['ASSIGNMENTS_PAGER'] = HMS_RLC_Application::rlc_application_admin_pager();

        # Set a bookmark so we can come back after submitting the assignments_pager
        PHPWS_Core::bookmark();
        
        $export_form = &new PHPWS_Form('export_form');
        $export_form->addHidden('type','rlc');
        $export_form->addHIdden('op','rlc_application_export');
        
        $export_form->addDropBox('rlc_list',HMS_Learning_Community::getRLCListAbbr());
        $export_form->addSubmit('submit');
        
        $export_form->mergeTemplate($tags);
        $tags = $export_form->getTemplate();
        
        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
    }

    function display_rlc_assignment_summary()
    {
        $template = array();

        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addColumn('capacity');
        $db->addColumn('id');
        $communities = $db->select();

        if(!$communities) {
            $template['no_communities'] = _('No communities have been enterred.');
            return PHPWS_Template::process($template, 'hms',
                    'admin/make_new_rlc_assignments_summary.tpl');
        }

        $count = 0;
        $total_assignments = 0;
        $total_available = 0;

        foreach($communities as $community) {
            $db = &new PHPWS_DB('hms_learning_community_assignment');
            $db->addWhere('rlc_id', $community['id']);
            $db->addWhere('gender', 'M');
            $male = $db->select('count');
            
            $db->resetWhere();
            $db->addWhere('rlc_id', $community['id']);
            $db->addWhere('gender', 'F');
            $female = $db->select('count');

            if($male   == NULL) $male   = 0;
            if($female == NULL) $female = 0;
            $assigned = $male + $female;
            
            $template['headings'][$count]['HEADING']       = $community['community_name'];
           
            $template['assignments'][$count]['ASSIGNMENT'] = "$assigned ($male/$female)";
            $total_assignments += $assigned;
            
            $template['available'][$count]['AVAILABLE']    = $community['capacity'];
            $total_available += $community['capacity'];
            
            $template['remaining'][$count]['REMAINING']    = $community['capacity'] - $assigned;
            $count++;
        }

        $template['TOTAL_ASSIGNMENTS'] = $total_assignments;
        $template['TOTAL_AVAILABLE'] = $total_available;
        $template['TOTAL_REMAINING'] = $total_available - $total_assignments;

        return PHPWS_Template::process($template, 'hms',
                'admin/make_new_rlc_assignments_summary.tpl');
    }

    function assign_rlc_members_to_rooms()
    {
        PHPWS_Core::initModClass('hms','HMS_Forms.php');

        return HMS_Form::show_assign_rlc_members_to_rooms();
    }

    function view_rlc_assignments()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Assignment.php');

        return HMS_RLC_Assignment::rlc_assignment_admin_pager();
    }

    function rlc_assignments_submit()
    {
        $errors = array();

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');

        PHPWS_Core::initModClass('hms','HMS_RLC_Application');
        $app = &new PHPWS_DB('hms_learning_community_applications');
        $app->addColumn('id');
        $app->addColumn('user_id');
        $app->addColumn('required_course');
        $app->addWhere('id',array_keys($_REQUEST['course_ok']));
        $applications = $app->select('assoc');
        
        $ass = &new PHPWS_DB('hms_learning_community_assignment');
        $app = &new PHPWS_DB('hms_learning_community_applications');

        foreach($applications as $id => $application) {
            $update = false;
            $app->reset();
            $ass->reset();

            $app->addWhere('id', $id);
            
            if(isset($_REQUEST['course_ok'][$id])) {
                $okay = ($_REQUEST['course_ok'][$id] == "Y" ? 1 : 0);
                if($application['required_course'] != $okay) {
                    $app->addValue('required_course', $okay);
                    $update = true;
                }
            }

            if(isset($_REQUEST['final_rlc'][$id]) && $_REQUEST['final_rlc'][$id] > -1) {
                $ass->addValue('asu_username',      $application['user_id']);
                $ass->addValue('rlc_id',            $_REQUEST['final_rlc'][$id]);
                $ass->addValue('assigned_by',  Current_User::getUsername());
                $ass->addValue('gender',            HMS_SOAP::get_gender($application['user_id']));
                $ass_id = $ass->insert();

                $app->addValue('hms_assignment_id', $ass_id);
                $update = true;
            }

            if($update) {
                $app->update();
            }
        }
        
        #return HMS_Learning_Community::assign_applicants_to_rlcs();
        PHPWS_Core::returnToBookmark();
        return;
    }

    
    /**
     * Exports the pending RLC applications into a CSV file.
     * Looks in $_REQUEST for which RLC to export.
     */
    function rlc_application_export()
    {
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id',$_REQUEST['rlc_list']);
        $title = $db->select('one');

        $filename = $title . '-applications-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"last_name","first_name","middle_name","gender","roommate","email","second_choice","third_choice","major","application_date"' . "\n";

        // get the userlist
        $db = &new PHPWS_DB('hms_learning_community_applications');
        $db->addColumn('user_id');
        $db->addColumn('rlc_second_choice_id');
        $db->addColumn('rlc_third_choice_id');
        $db->addColumn('date_submitted');
        $db->addWhere('rlc_first_choice_id', $_REQUEST['rlc_list']);
        $users = $db->select();

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        foreach($users as $user) {
            $roomie = NULL;
            $db = &new PHPWS_DB('hms_roommates');
            $db->addColumn('roommate_zero');
            $db->addColumn('roommate_one');
            $db->addWhere('roommate_zero', $user['user_id'], 'ILIKE');
            $db->addWhere('roommate_one', $user['user_id'], 'ILIKE', 'OR');
            $roommates = $db->select();
            foreach($roommates as $pair) {
                if(strtolower($pair['roommate_zero']) == strtolower($user['user_id'])) $roomie = $pair['roommate_one'];
                else $roomie = $pair['roommate_zero'];
            }

            if($roomie == NULL) {
                $db = &new PHPWS_DB('hms_roommate_approval');
                $db->addColumn('roommate_zero');
                $db->addColumn('roommate_one');
                $db->addWhere('roommate_zero', $user['user_id'], 'ILIKE');
                $db->addWhere('roommate_one', $user['user_id'], 'ILIKE', 'OR');
                $hopeful_roommates = $db->select();
                foreach($hopeful_roommates as $pair) {
                    if(strtolower($pair['roommate_zero']) == strtolower($user['user_id'])) $roomie = $pair['roommate_one'];
                    else $roomie = $pair['roommate_zero'] . " *pending* ";
                }
            }

            $sinfo = HMS_SOAP::get_student_info($user['user_id']);
            $buffer .= '"' . $sinfo->last_name . '",';
            $buffer .= '"' . $sinfo->first_name . '",';
            $buffer .= '"' . $sinfo->middle_name . '",';
            $buffer .= '"' . $sinfo->gender . '",';
            if($roomie != NULL) {
                $buffer .= '"' . HMS_SOAP::get_full_name($roomie) . '",';
            } else {
                $buffer .= '"",';
            }
            $buffer .= '"' . $user['user_id'] . '@appstate.edu' . '",';
            
            if(isset($user['rlc_second_choice_id'])) {
                $db = new PHPWS_DB('hms_learning_communities');
                $db->addColumn('community_name');
                $db->addWhere('id', $user['rlc_second_choice_id']);
                $result = $db->select('one');
                if(!PHPWS_Error::logIfError($result)) {
                    $buffer .= '"' . $result . '",';
                }
            } else {
                $buffer .= '"",';
            }
            
            if(isset($user['rlc_third_choice_id'])) {
                $db = new PHPWS_DB('hms_learning_communities');
                $db->addColumn('community_name');
                $db->addWhere('id', $user['rlc_third_choice_id']);
                $result = $db->select();
                if(!PHPWS_Error::logIfError($result)) {
                    $buffer .= '"' . $result . '",';
                }
            } else {
                $buffer .= '"",';
            }

            //Major for this user, N/A for now
            $buffer .= '"N/A",';

            //Application Date
            if(isset($user['date_submitted'])){
                PHPWS_Core::initModClass('hms', 'HMS_Util.php');
                $buffer .= '"' . HMS_Util::get_long_date($user['date_submitted']) . '"';
            } else {
                $buffer .= '"Error with the submission Date"';
            }
            $buffer .= "\n";
        }

        //HERES THE QUERY:
        //select hms_learning_community_applications.user_id, date_submitted, rlc_first_choice.abbreviation as first_choice, rlc_second_choice.abbreviation as second_choice, rlc_third_choice.abbreviation as third_choice FROM (SELECT hms_learning_community_applications.user_id, hms_learning_communities.abbreviation FROM hms_learning_communities,hms_learning_community_applications WHERE hms_learning_communities.id = hms_learning_community_applications.rlc_first_choice_id) as rlc_first_choice, (SELECT hms_learning_community_applications.user_id, hms_learning_communities.abbreviation FROM hms_learning_communities,hms_learning_community_applications WHERE hms_learning_communities.id = hms_learning_community_applications.rlc_second_choice_id) as rlc_second_choice, (SELECT hms_learning_community_applications.user_id, hms_learning_communities.abbreviation FROM hms_learning_communities,hms_learning_community_applications WHERE hms_learning_communities.id = hms_learning_community_applications.rlc_third_choice_id) as rlc_third_choice, hms_learning_community_applications WHERE rlc_first_choice.user_id = hms_learning_community_applications.user_id AND rlc_second_choice.user_id = hms_learning_community_applications.user_id AND rlc_third_choice.user_id = hms_learning_community_applications.user_id;
       
        //Download file
        if(ob_get_contents())
            print('Some data has already been output, can\'t send file');
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
            header('Content-Type: application/force-download');
        else
            header('Content-Type: application/octet-stream');
        if(headers_sent())
            print('Some data has already been output to browser, can\'t send file');
        header('Content-Length: '.strlen($buffer));
        header('Content-disposition: attachment; filename="'.$filename.'"');
        echo $buffer;
        die();
    }

    /**
     * Exports the completed RLC assignments.
     */
    function rlc_assignment_export()
    {
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id',$_REQUEST['rlc_list']);
        $title = $db->select('one');

        $filename = $title . '-assignments-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"last_name","first_name","middle_name","gender","email"' . "\n";
        
        // get the list of assignments
        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->addColumn('user_id');
        $db->addWhere('hms_learning_community_assignment.rlc_id',$_REQUEST['rlc_list']); # select assignments only for the given RLC
        $users = $db->select();

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        foreach($users as $user){
            $sinfo = HMS_SOAP::get_student_info($user['user_id']);
            $buffer .= '"' . $sinfo->last_name . '",';
            $buffer .= '"' . $sinfo->first_name . '",';
            $buffer .= '"' . $sinfo->middle_name . '",';
            $buffer .= '"' . $sinfo->gender . '",';
            $buffer .= '"' . $user['user_id'] . '@appstate.edu' . '"' . "\n";
        }
        
        //Download file
        if(ob_get_contents())
            print('Some data has already been output, can\'t send file');
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
            header('Content-Type: application/force-download');
        else
            header('Content-Type: application/octet-stream');
        if(headers_sent())
            print('Some data has already been output to browser, can\'t send file');
        header('Content-Length: '.strlen($buffer));
        header('Content-disposition: attachment; filename="'.$filename.'"');
        echo $buffer;
        die();
    }
}
?>
