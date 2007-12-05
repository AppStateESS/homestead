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

    function room_listed_twice()
    {
        $r0 = $_REQUEST['room_id_zero'];
        $r1 = $_REQUEST['room_id_one'];
        $r2 = $_REQUEST['room_id_two'];
        $r3 = $_REQUEST['room_id_three'];

        if($r0 == $r1 || $r0 == $r2 || $r0 == $r3 ||
           $r1 == $r2 || $r1 == $r3 || $r2 == $r3) {
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
        
        $suite = new HMS_Suite($_REQUEST['suite']);
        $suite->set_room_ids();
        $db = &new PHPWS_DB('hms_suite');
        
        if($suite->get_id()) {
            $db->addWhere('id', $suite->get_id());
            $db->addValue('room_id_zero', $_REQUEST['room_id_zero']);
            $db->addValue('room_id_one', $_REQUEST['room_id_one']);
            $db->addValue('room_id_two', $_REQUEST['room_id_two']);
            $db->addValue('room_id_three', $_REQUEST['room_id_three']);
            $success = $db->update();
        } else {
            $success = $db->saveObject($suite);
        }
       
        if(PEAR::isError($success)) {
            PHPWS_Error::log($success, 'hms', 'HMS_Suite::save_new_suite');
            $msg = "There was an error saving this suite. Please contact Electronic Student Services.";
        } else {
            $msg = "Suite was successfully saved!";
        }
       
        $_REQUEST['new'] = 'false';
        $_REQUEST['suite'] = $success;
        return HMS_Suite::edit_suite($msg);
    }

    function rooms_same_gender()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $g0 = HMS_Room::get_gender_type($_REQUEST['room_id_zero']);
        $g1 = HMS_Room::get_gender_type($_REQUEST['room_id_one']);
        
        if($_REQUEST['room_id_two'] != 0) {
            $g2 = HMS_Room::get_gender_type($_REQUEST['room_id_two']);
        } else {
            $g2 = $g0;
        }
        
        if($_REQUEST['room_id_three'] != 0) {
            $g3 = HMS_Room::get_gender_type($_REQUEST['room_id_three']);
        } else {
            $g3 = $g0;
        }

        if ($g0 == $g1 && $g0 == $g2 && $g0 == $g3) return true;
        else return false;
    }

    function check_if_rooms_are_medical()
    {
        $med = false;
        $rooms = array();

        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        
        if(HMS_Room::get_is_medical($_REQUEST['room_id_zero'])) {
            $med = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_zero']);
        }

        if(HMS_Room::get_is_medical($_REQUEST['room_id_one'])) {
            $med = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_one']);
        }

        if(HMS_Room::get_is_medical($_REQUEST['room_id_two'])) {
            $med = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_two']);
        }

        if(HMS_Room::get_is_medical($_REQUEST['room_id_three'])) {
            $med = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_three']);
        }

        if($med == true) return $rooms;
        else return false;
    }

    function check_if_rooms_are_reserved()
    {
        $res = false;
        $rooms = array();

        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        
        if(HMS_Room::get_is_reserved($_REQUEST['room_id_zero'])) {
            $res = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_zero']);
        }

        if(HMS_Room::get_is_reserved($_REQUEST['room_id_one'])) {
            $res = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_one']);
        }

        if(HMS_Room::get_is_reserved($_REQUEST['room_id_two'])) {
            $res = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_two']);
        }

        if(HMS_Room::get_is_reserved($_REQUEST['room_id_three'])) {
            $res = true;
            $rooms[] = HMS_Room::get_room_number($_REQUEST['room_id_three']);
        }

        if($res == true) return $rooms;
        else return false;
    }

    function rooms_not_in_another_suite()
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

    function verify_save_suite()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::verify_save_suite();
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
            case 'verify_save_suite':
                $final = HMS_Suite::verify_save_suite();
                break;
            default:
                $final = "Operation is: " . $_REQUEST['op'];
                break;
        }
        return $final;
    }
};
?>
