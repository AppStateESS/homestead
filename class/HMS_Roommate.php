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
     * Creates a new Roommate object, sets the values pulled from the username input form
     *   and saves the object.
     */
    function save_grouping()
    {
        require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

        if($_REQUEST['first_roommate'] == NULL || $_REQUEST['second_roommate'] == NULL) {
            $error = "You must provide a first and second roommate to save this group.";
            return HMS_Roommate::get_usernames_for_new_grouping($error);
        }

        $grouping = new HMS_Roommate();
        $grouping->set_values();

        $db = new PHPWS_DB('hms_roommates');
        $result = $db->saveObject($grouping);

        if(PEAR::isError($result)) {
            PHPWS_Core::log($result);
            return "There was an error. Please check the logs.";
        } else {
            return "This group was successfully saved.";
        }

    }

    /**
     * 
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
            case 'edit_grouping':
                $final = HMS_Roommate::edit_grouping();
                break;
            default:
                $final =  "Op is: " . $op;
                break;
        }

        return $final;
    }
};

?>
