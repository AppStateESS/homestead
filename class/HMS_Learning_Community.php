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

    function set_variables()
    {
        if($_REQUEST['id'] != NULL) $this->set_id($_REQUEST['id']);
        $this->set_community_name($_REQUEST['community_name']);
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

            test($result);

            # Check for an error
            if(PEAR::isError($result)){
                $template['MESSAGE'] = "Sorry, there was an error working with the database. Your application could not be saved.";
            }else{
                $template['SUCCESS'] = "Your application was submitted successfully.";
            }
            
            return PHPWS_Template::process($template, 'hms', 'student/rlc_signup_confirmation.tpl');
        }

    }

    function assign_applicants_to_rlcs()
    {
        $tags = array();
        $tags['SUMMARY'] = display_rlc_assignment_summary();

        $tags['headings'][0]['HEADING'] = "Heading1";
        $tags['headings'][1]['HEADING'] = "HEading2";

        $tags['listrows'][0]['STATISTIC'] = "Statistic";
        $tags['listrows'][1]['STATISTIC'] = "Something Else";
        $tags['listrows'][2]['STATISTIC'] = "Yo Momma";
        
        $tags['listrows'][0]['columns'][0]['COLUMN'] = "0,0";
        $tags['listrows'][0]['columns'][1]['COLUMN'] = "0,1";
        $tags['listrows'][1]['columns'][0]['COLUMN'] = "1,0";
        $tags['listrows'][1]['columns'][1]['COLUMN'] = "1,1";
        $tags['listrows'][2]['columns'][0]['COLUMN'] = "2,0";
        $tags['listrows'][2]['columns'][1]['COLUMN'] = "2,1";

        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments_summary.tpl');
    }

    function display_rlc_assignment_summary()
    {
        $db = &new PHPWS_DB('');
        $tags = array();

    }

    function assign_rlc_members_to_rooms()
    {
        PHPWS_Core::initModClass('hms','HMS_Forms.php');

        return HMS_Form::show_assign_rlc_members_to_rooms();
    }

    function view_rlc_assignments()
    {
        return "";
    }
};
?>
