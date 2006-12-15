<?php

class HMS_Suite
{

    var $id;
    var $room_id_zero;
    var $room_id_one;
    var $room_id_two;
    var $room_id_three;

    function HMS_Suite($id = NULL)
    {
        if($id == NULL) {
            $this->set_rooms_null();
            $this->set_id(NULL);
        } else {
            $this->set_id($id);
            $db = &new PHPWS_DB('hms_suite');
            $db->loadObject($this);
            return $this;
        }
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_room_id_zero($id)
    {
        $this->room_id_zero = $id;
    }

    function get_room_id_zero()
    {
        return $this->room_id_zero;
    }

    function set_room_id_one($id)
    {
        $this->room_id_one = $id;
    }

    function get_room_id_one()
    {
        return $this->room_id_one;
    }

    function set_room_id_two($id)
    {
        $this->room_id_two = $id;
    }

    function get_room_id_two()
    {
        return $this->room_id_two;
    }

    function set_room_id_three($id)
    {
        $this->room_id_three = $id;
    }

    function get_room_id_three()
    {
        return $this->room_id_three;
    }

    function set_rooms_null()
    {
        $this->set_room_id_zero(NULL);
        $this->set_room_id_one(NULL);
        $this->set_room_id_two(NULL);
        $this->set_room_id_three(NULL);
    }

    function edit_suite($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $final = HMS_Form::edit_suite($error);
        return $final;
    }

    function check_room_ids_numeric()
    {
        if(!is_numeric($_REQUEST['room_id_zero']) || !is_numeric($_REQUEST['room_id_one']) ||
           !is_numeric($_REQUEST['room_id_two']) || !is_numeric($_REQUEST['room_id_three'])) {
            return false;
        } else {
            return true;
        }
    }

    function check_valid_room_ids()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        return (HMS_Room::is_valid_room($_REQUEST['room_id_zero']) &&
                HMS_Room::is_valid_room($_REQUEST['room_id_one']) &&
                HMS_Room::is_valid_room($_REQUEST['room_id_two']) &&
                HMS_Room::is_valid_room($_REQUEST['room_id_three']));
    }
    
    function rooms_in_suite()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        if(HMS_Room::is_in_suite($_REQUEST['room_id_zero']) ||
           HMS_Room::is_in_suite($_REQUEST['room_id_one']) ||
           HMS_Room::is_in_suite($_REQUEST['room_id_two']) ||
           HMS_Room::is_in_suite($_REQUEST['room_id_three'])) {
            return true;
        } else {
            return false;
        }
    }

    function set_room_ids()
    {
        $this->set_room_id_zero($_REQUEST['room_id_zero']);
        $this->set_room_id_one($_REQUEST['room_id_one']);
        
        if($_REQUEST['room_id_two'] != NULL) {
            $this->set_room_id_two($_REQUEST['room_id_two']);
        }
        
        if($_REQUEST['room_id_three'] != NULL) {
            $this->set_room_id_three($_REQUEST['room_id_three']);
        }
    }

    function save_suite($new = NULL)
    {
        $msg = NULL;

        if(!HMS_Suite::check_room_ids_numeric()) {
            PHPWS_Core::log('ROOM IDS NOT NUMERIC!', 'hms');
            return "You are a BAD PERSON and your Mischief has been logged!";
        }

        if(!HMS_Suite::check_valid_room_ids()) {
            PHPWS_Core::log('BAD ROOM IDS!', 'hms');
            return "Those are not valid room IDs!";
        }

        // are the rooms already in a suite?
        if($new && HMS_Suite::rooms_in_suite()) {
            $msg = "One or more of the rooms you chose are already in a suite!"; 
        } else if ($new == NULL) {
            $suite = &new HMS_Suite($_REQUEST['suite']);
            if(!$suite->rooms_eligible_for_this_suite()) {
                $msg = "One of the rooms you selected is not eligible for this suite.";
            }
        }
   
        if($msg == NULL) {
            $suite = new HMS_Suite($_REQUEST['suite']);
            $suite->set_room_ids();
            $db = &new PHPWS_DB('hms_suite');
            $success = $db->saveObject($suite);
           
            if(PEAR::isError($success)) {
                PHPWS_Error::log($success, 'hms', 'HMS_Suite::save_new_suite');
                $msg = "There was an error saving this suite. Please contact Electronic Student Services.";
            } else {
                $msg = "Suite was successfully saved!";
            }
        }
        
        return HMS_Suite::edit_suite($msg);
    }

    function rooms_eligible_for_this_suite()
    {
        $db = &new PHPWS_DB('hms_suite');
        $db->addColumn('id');
        $db->addWhere('room_id_zero', $this->get_room_id_zero(), '=');
        $db->addWhere('room_id_one', $this->get_room_id_zero(), '=', 'OR');
        $db->addWhere('room_id_two', $this->get_room_id_zero(), '=', 'OR');
        $db->addWhere('room_id_three', $this->get_room_id_zero(), '=', 'OR');
      
        if($this->get_room_id_one() != NULL) {  
            $db->addWhere('room_id_zero', $this->get_room_id_one(), '=', 'OR');
            $db->addWhere('room_id_one', $this->get_room_id_one(), '=', 'OR');
            $db->addWhere('room_id_two', $this->get_room_id_one(), '=', 'OR');
            $db->addWhere('room_id_three', $this->get_room_id_one(), '=', 'OR');
        }

        if($this->get_room_id_two() != NULL) {
            $db->addWhere('room_id_zero', $this->get_room_id_two(), '=', 'OR');
            $db->addWhere('room_id_one', $this->get_room_id_two(), '=', 'OR');
            $db->addWhere('room_id_two', $this->get_room_id_two(), '=', 'OR');
            $db->addWhere('room_id_three', $this->get_room_id_two(), '=', 'OR');
        }
        
        if($this->get_room_id_three() != NULL) {
            $db->addWhere('room_id_zero', $this->get_room_id_three(), '=', 'OR');
            $db->addWhere('room_id_one', $this->get_room_id_three(), '=', 'OR');
            $db->addWhere('room_id_two', $this->get_room_id_three(), '=', 'OR');
            $db->addWhere('room_id_three', $this->get_room_id_three(), '=', 'OR');
        }
       
        $results = $db->select();
        if(PEAR::isError($results)) {
            PHPWS_Error::log($results, 'hms', 'HMS_Suite::rooms_eligible_for_this_suite');
            return "-1";
        }

        if(sizeof($results) == 1 && $results[0]['id'] == $this->id) {
            return true;
        } else {
            return false;
        }
    }

    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'edit_suite':
                $final = HMS_Suite::edit_suite();
                break;
            case 'save_suite':
                $final = HMS_Suite::save_suite();
                break;
            case 'save_new_suite':
                $final = HMS_Suite::save_suite(true);
                break;
            default:
                $final = "Operation is: " . $_REQUEST['op'];
                break;
        }
        return $final;
    }
};
?>
