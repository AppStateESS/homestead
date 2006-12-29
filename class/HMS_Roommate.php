<?php

class HMS_Roommate
{

    var $id;
    var $roommate_zero;
    var $roommate_one;
    var $roommate_two;
    var $roommate_three;

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_roommate_zero($rz)
    {
        $this->roommate_zero = $rz;
    }

    function get_roommate_zero()
    {
        return $this->roommate_zero;
    }

    function set_roommate_one($ro)
    {
        $this->roommate_one = $ro;
    }

    function get_roommate_one()
    {
        return $this->roommate_one;
    }

    function set_roommate_two($rt)
    {
        $this->roommate_two = $rt;
    }

    function get_roommate_two() 
    {
        return $this->roommate_two;
    }

    function set_roommate_three($rt)
    {
        $this->roommate_three = $rt;
    }

    function get_roommate_three()
    {
        return $this->roommate_three;
    }

    function HMS_Roommate($id = NULL)
    {
        if($id == NULL) {
            $this->set_values_null();
        } else {

        }

        return $this;
    }

    function set_values_null()
    {
        $this->set_id(NULL);
        $this->set_roommate_zero(NULL);
        $this->set_roommate_one(NULL);
        $this->set_roommate_two(NULL);
        $this->set_roommate_three(NULL);
    }

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

    function get_usernames_for_new_grouping($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_usernames_for_new_grouping($error);
    }

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
            default:
                $final =  "Op is: " . $op;
                break;
        }

        return $final;
    }
};

?>
