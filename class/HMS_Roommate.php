<?php

class HMS_Roommate
{

    var $id;
    var $roommate_zero;
    var $roommate_one;
    var $roommate_two;
    var $roommate_three;

    /**
     * Sets the id of the grouping
     */
    function set_id($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the grouping's id
     */
    function get_id()
    {
        return $this->id;
    }

    /**
     * Sets the username for the first roommate
     */
    function set_roommate_zero($rz)
    {
        $this->roommate_zero = $rz;
    }

    /**
     * Gets the username for the first roommate
     */
    function get_roommate_zero()
    {
        return $this->roommate_zero;
    }

    /**
     * Sets the username for the second roommate
     */
    function set_roommate_one($ro)
    {
        $this->roommate_one = $ro;
    }

    /**
     * Returns the username for the second roommate
     */
    function get_roommate_one()
    {
        return $this->roommate_one;
    }

    /**
     * Sets the username for the third roommate
     */
    function set_roommate_two($rt)
    {
        $this->roommate_two = $rt;
    }

    /**
     * Returns the username for the third roommate
     */
    function get_roommate_two() 
    {
        return $this->roommate_two;
    }

    /**
     * Sets the username for the fourth roommate
     */
    function set_roommate_three($rt)
    {
        $this->roommate_three = $rt;
    }

    /**
     * Returns the username for the fourth roommate
     */ 
    function get_roommate_three()
    {
        return $this->roommate_three;
    }

    /**
     * Constructor for the Roommate class
     * Can be passed the id of a grouping already in the database to
     *   create a new instance of that roommate grouping
     */
    function HMS_Roommate($id = NULL)
    {
        if($id == NULL) {
            $this->set_values_null();
        } else {

        }

        return $this;
    }

    /**
     * Sets all member variables to NULL
     */ 
    function set_values_null()
    {
        $this->set_id(NULL);
        $this->set_roommate_zero(NULL);
        $this->set_roommate_one(NULL);
        $this->set_roommate_two(NULL);
        $this->set_roommate_three(NULL);
    }

    /**
     * Sets the usernames for each roommate
     */
    function set_values()
    {
        if(isset($_REQUEST['id']) && $_REQUEST['id'] != NULL) {
            $this->set_id($_REQUEST['id']);
        }

        $this->set_roommate_zero($_REQUEST['first_roommate']);
        $this->set_roommate_one($_REQUEST['second_roommate']);

        if($_REQUEST['third_roommate'] != NULL) {
            $this->set_roommate_two($_REQUEST['third_roommate']);
        } else {
            $this->set_roommate_two(NULL);
        }

        if($_REQUEST['fourth_roommate'] != NULL) {
            $this->set_roommate_three($_REQUEST['fourth_roommate']);
        } else {
            $this->set_roommate_three(NULL);
        }
    }

    /**
     * Calls a method in the Forms class of the same name
     * Displays a form with four text boxes
     */
    function get_usernames_for_new_grouping($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_usernames_for_new_grouping($error);
    }

    /**
     * Checks all listed users are valid students
     */
    function check_valid_students()
    {
        $error = '';

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        if($_REQUEST['first_roommate'] != NULL && !HMS_SOAP::is_valid_student($_REQUEST['first_roommate'])) {
            $error .= $_REQUEST['first_roommate'] . " is not a valid student for this Housing term.<br />";
        }

        if($_REQUEST['second_roommate'] != NULL && !HMS_SOAP::is_valid_student($_REQUEST['second_roommate'])) {
            $error .= $_REQUEST['second_roommate'] . " is not a valid student for this Housing term.<br />";
        }

        if($_REQUEST['third_roommate'] != NULL && !HMS_SOAP::is_valid_student($_REQUEST['third_roommate'])) {
            $error .= $_REQUEST['third_roommate'] . " is not a valid student for this Housing term.<br />";
        }

        if($_REQUEST['fourth_roommate'] != NULL && !HMS_SOAP::is_valid_student($_REQUEST['fourth_roommate'])) {
            $error .= $_REQUEST['fourth_roommate'] . " is not a valid student for this Housing term.<br />";
        }
    
        return $error;
    }

    /**
     * Returns an error if the user didn't specify at least two roommates
     */
    function check_two_roommates()
    {
        $error = '';
        if($_REQUEST['first_roommate'] == NULL || $_REQUEST['second_roommate'] == NULL) {
            $error .= "You must provide a first and second roommate to save this group.<br />";
        }
        return $error;
    }

    /**
     * Returns an error if the genders of the specified users are different
     */
    function check_consistent_genders()
    {
        $error = '';
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $g1 = HMS_SOAP::get_gender($_REQUEST['first_roommate']);
        $g2 = HMS_SOAP::get_gender($_REQUEST['second_roommate']);
       
        if($g1 != $g2) $error = $_REQUEST['first_roommate'] . " and " . $_REQUEST['second_roommate'] . " must have the same gender.<br />";

        if($_REQUEST['third_roommate'] != NULL) {
            $g3 = HMS_SOAP::get_gender($_REQUEST['third_roommate']);
            if($g1 != $g3) $error = $_REQUEST['first_roommate'] . " and " . $_REQUEST['third_roommate'] . " must have the same gender.<br />";
            else if($g2 != $g3) $error = $_REQUEST['second_roommate'] . " and " . $_REQUEST['third_roommate'] . " must have the same gender.<br />";
        }

        if($_REQUEST['fourth_roommate'] != NULL) {
            $g4 = HMS_SOAP::get_gender($_REQUEST['fourth_roommate']);
            if($g1 != $g4) $error = $_REQUEST['first_roommate'] . " and " . $_REQUEST['fourth_roommate'] . " must have the same gender.<br />";
            else if($g2 != $g4) $error = $_REQUEST['second_roommate'] . " and " . $_REQUEST['fourth_roommate'] . " must have the same gender.<br />";
            else if($g3 != $g4) $error = $_REQUEST['third_roommate'] . " and " . $_REQUEST['fourth_roommate'] . " must have the same gender.<br />";
        }
        return $error;
    }

    /**
     * Creates a new Roommate object, sets the values pulled from the username input form
     *   and saves the object.
     */
    function save_grouping()
    {
        $error = HMS_Roommate::check_two_roommates();
        if($error != '') {
            return HMS_Roommate::get_usernames_for_new_grouping($error);
        }

        $error = HMS_Roommate::check_valid_students();
        if($error != '') {
            return HMS_Roommate::get_usernames_for_new_grouping($error);
        }

        $error = HMS_Roommate::check_consistent_genders();
        if($error != '') {
            return HMS_Roommate::get_usernames_for_new_grouping($error);
        }

        $grouping = new HMS_Roommate();
        $grouping->set_values();

        $db = new PHPWS_DB('hms_roommates');
        $result = $db->saveObject($grouping);

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return "There was an error. Please check the logs.";
        } else {
            $msg = "This group was successfully saved. <br /><br />";
            $msg .= PHPWS_Text::secureLink(_('Create new roommate group'), 'hms', array('type'=>'roommate', 'op'=>'get_usernames_for_new_grouping')) . "<br /><br />";
            $msg .= PHPWS_Text::secureLink(_('Edit roommate group'), 'hms', array('type'=>'roommate', 'op'=>'get_username_for_edit_grouping'));
            return $msg;
        }
    }

    /**
     * Breaks up the selected roommate group.
     * Selected roommates will be emailed about the disbanding.
     */
    function break_grouping()
    {
        $db = new PHPWS_DB('hms_roommates');
        $db->addWhere('id', $_REQUEST['id']);
        $result = $db->delete();

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return "There was an error. Please check the logs.";
        } else {
            $msg = "This group was successfully broken. <br /><br />";
            $msg .= PHPWS_Text::secureLink(_('Create new roommate group'), 'hms', array('type'=>'roommate', 'op'=>'get_usernames_for_new_grouping')) . "<br /><br />";
            $msg .= PHPWS_Text::secureLink(_('Edit roommate group'), 'hms', array('type'=>'roommate', 'op'=>'get_username_for_edit_grouping'));
            return $msg;
        }
    }

    /**
     * Calls HMS_Form method that allows the user to input an
     *   ASU username. This searches the hms_roommate table to see
     *   if that username is in a grouping.
     */
    function get_username_for_edit_grouping($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_username_for_edit_grouping($error);
    }

    /**
     * Allows for editing of the members in a roommate grouping
     */
    function edit_grouping()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::edit_grouping();
    }


    /**
     * DB Pager screen displayed after an username is entered.
     */
    function select_username_for_edit_grouping()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::select_username_for_edit_grouping();
    }

    function get_row_pager_tags()
    {
        $row['ACTIONS'] = HMS_Roommate::get_row_actions();
        return $row;
    }

    function get_row_actions()
    {
        $link['type']   = 'roommate';
        $link['id']     = $this->get_id();
        
        $link['op']     = 'edit_grouping';
        $list[]         = PHPWS_Text::secureLink(_('Edit'), 'hms', $link);
        
        $link['op']     = 'verify_break_grouping';
        $list[]         = PHPWS_Text::secureLink(_('Break'), 'hms', $link);

        return implode(' | ', $list);
    }

    /**
     * Calls HMS_Form function to display a verification screen with an
     *   option to email the group members they've been disbanded
     */
    function verify_break_grouping()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::verify_break_grouping();
    }

    /**
     * checks to see if the username specified has a roommate
     * returns true or false
     */
    function has_roommates($username)
    {
        $db = &new PHPWS_DB('hms_roommates');
        $db->addColumn('id');
        $db->addWhere('roommate_id_zero', $username, 'ILIKE');
        $db->addWhere('roommate_id_one', $username, 'ILIKE', 'OR');
        $db->addWhere('roommate_id_two', $username, 'ILIKE', 'OR');
        $db->addWhere('roommate_id_three', $username, 'ILIKE', 'OR');
        $id = $db->select('one');
        if($id == NULL || $id == FALSE) {
            return false;
        } else if (is_numeric($id)) {
            return true;
        }
    }
     
    /**
     * "main" function for the Roommate class
     * Checks the desired operation and calls the necessary functions
     */
    function main()
    {
        $op = $_REQUEST['op'];

        switch($op)
        {
            case 'get_usernames_for_new_grouping':
                $final = HMS_Roommate::get_usernames_for_new_grouping();
                break;
            case 'save_grouping':
                $final = HMS_Roommate::save_grouping();
                break;
            case 'get_username_for_edit_grouping':
                $final = HMS_Roommate::get_username_for_edit_grouping();
                break;
            case 'select_username_for_edit_grouping':
                $final = HMS_Roommate::select_username_for_edit_grouping();
                break;
            case 'edit_grouping':
                $final = HMS_Roommate::edit_grouping();
                break;
            case 'verify_break_grouping':
                $final = HMS_Roommate::verify_break_grouping();
                break;
            case 'break_grouping':
                $final = HMS_Roommate::break_grouping();
                break;
            default:
                $final =  "Op is: " . $op;
                break;
        }

        return $final;
    }
};

?>
