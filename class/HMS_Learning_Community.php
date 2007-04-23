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
    function show_rlc_application_form()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        if(HMS_RLC_Application::check_for_application() !== FALSE){
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
            $template['MESSAGE'] = "Sorry, the RLC application deadline has already passed. Please contact Housing & Residence life if you are interested in applying for a RLC.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }    
        
        PHPWS_Core::initModClass('hms','HMS_Forms.php');
        return HMS_Form::show_rlc_application_form_page1();
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
            case 'rlc_assignments_submit':
                return HMS_Learning_Community::rlc_assignments_submit();
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
    function rlc_application_page1_submit()
    {
        PHPWS_Core::initModClass('hms','HMS_Forms.php');
        
        # Check for invalid input on page 1
        $message = HMS_Form::validate_rlc_application_page1();
        if($message !== TRUE){
            # Show page one again with error message
            return HMS_Form::show_rlc_application_form_page1($message);
        }else{
            return HMS_Form::show_rlc_application_form_page2();
        }
    }

    function rlc_application_page2_submit()
    {
        PHPWS_Core::initModClass('hms','HMS_Forms.php');
        
        $template = array();
        $template['PAGE_TITLE'] = "Residential Learning Community Application";

        # Check for invalid input on page 2
        $message = HMS_Form::validate_rlc_application_page2();
        if($message !== TRUE){
            # Show page two again with error message
            return HMS_Form::show_rlc_application_form_page2($message);
        }else{

            # Save the data to the database
            PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
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
        $tags['SUMMARY']           = HMS_Learning_Community::display_rlc_assignment_summary();
        $tags['ASSIGNMENTS_PAGER'] = HMS_RLC_Application::rlc_application_admin_pager();

        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
    }

    function display_rlc_assignment_summary()
    {
        $template = array();

        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addColumn('capacity');
        $communities = $db->select();

        if(!$communities) {
            $template['no_communities'] = _('No communities have been enterred.');
            return PHPWS_Template::process($template, 'hms',
                    'admin/make_new_rlc_assignments_summary.tpl');
        }

        

        $count = 0;
        $total_assignments = 0;
        $total_available = 0;
        $total_remaining = 0;

        // TODO: Sane values in place of the zeroes below:
        foreach($communities as $community) {
            $template['headings'][$count]['HEADING']       = $community['community_name'];
            
            $template['assignments'][$count]['ASSIGNMENT'] = 0; // HERE
            
            $template['available'][$count]['AVAILABLE']    = $community['capacity'];    // THIS is correct.
            $total_available += $community['capacity'];
            
            $template['remaining'][$count]['REMAINING']    = 0; // and HERE
            $count++;
        }

        $template['TOTAL_ASSIGNMENTS'] = $total_assignments;
        $template['TOTAL_AVAILABLE'] = $total_available;
        $template['TOTAL_REMAINING'] = $total_remaining;

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
                $ass->addValue('asu_username',         $application['user_id']);
                $ass->addValue('rlc_id',               $_REQUEST['final_rlc'][$id]);
                $ass->addValue('assigned_by_user',     0); //TODO: Current_User?
                $ass->addValue('assigned_by_initials', "asd"); //TODO: This may be entirely unnecessary.
                test($ass_id = $ass->insert());

                $app->addValue('hms_assignment_id', $ass_id);
                $update = true;
            }

            if($update) {
                $app->update();
            }
        }
        
        return HMS_Learning_Community::assign_applicants_to_rlcs();
    }
}
?>
