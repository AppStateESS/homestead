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
        if(!HMS_Forms::validate_rlc_application_page1()){
            # Show page one again with error message
            $message = "There was a problem with your submission. Please complete all fields.";
            return HMS_Forms::show_rlc_application_form_page1($message);
        }
    }
};
?>
