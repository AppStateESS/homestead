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

    function HMS_Floor()
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

    function get_community_name($name)
    {
        return $this->community_name;
    }

    function set_variables()
    {
        if($_REQUEST['id']) $this->set_id($_REQUEST['id']);
        $this->set_community_name($_REQUEST['community_name']);
    }

    function save_learning_community()
    {
        $db = & new PHPWS_DB('hms_learning_communities');
        
        if($this->id) {
            $db->addWhere('id', $this->id);
        }
        
        $success = $db->saveObject($this);
        unset($db);

        if(PEAR::isError($success)) {
            test($success);
            PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
            $this->error .= "There was a problem saving this Learning Community!<br />";
            $tpl = HMS_Form::fill_learning_community_data_display($this, 'save_learning_community');
            $tpl['TITLE'] = "Error Saving Learning Community";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_learning_community_data.tpl');
        } else {
            $tpl['TITLE'] = "Successful Save!";
            $tpl['CONTENT'] = "Learning Community was saved successfully!";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
        }

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
};
?>
