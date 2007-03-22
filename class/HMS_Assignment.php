<?php

error_reporting(E_ALL);

/**
 * Provides functionality to actually assign students to a room
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */
 
class HMS_Assignment
{
    var $id;
    var $asu_username;
    var $bed_id;

    /**
     * Return the id for the current assignment object
     */
    function get_id()
    {
        return $this->id;
    }

    /**
     * Sets the id for the current assignment object
     */
    function set_id($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the ASU username associated with the current assignment
     */
    function get_asu_username()
    {
        return $this->asu_username;
    }

    /**
     * Sets the ASU username associated with the current assignment
     */
    function set_asu_username($user)
    {
        $this->asu_username = $user;
    }

    /**
     * Returns the bed associated with the current assignment
     */
    function get_bed_id()
    {
        return $this->bed_id;
    }

    /**
     * Sets the bed associated with the current assignment
     */
    function set_bed_id($bnumber)
    {
        $this->bed_id = $bnumber;
    }

    /**
     * Creates the actual assignment object
     */
    function &create_assignment()
    {
        $db = new PHPWS_DB('hms_room');
        $db->addColumn('id');
        $db->addWhere('building_id', $_REQUEST['hall']);
        $db->addWhere('floor_number', $_REQUEST['floor']);
        $db->addWhere('room_number', $_REQUEST['floor'] . str_pad($_REQUEST['room'], 2, "0", STR_PAD_LEFT));
        $db->addWhere('deleted', '1', '!=');
        $rid = $db->select('one');

        $db = new PHPWS_DB('hms_bedrooms');
        $db->addColumn('id');
        $db->addWhere('room_id', $rid);
        $db->addWhere('bedroom_letter', $_REQUEST['bedroom_letter']);
        $br_id = $db->select('one');

        $db = new PHPWS_DB('hms_beds');
        $db->addColumn('id');
        $db->addWhere('bedroom_id', $br_id);
        $db->addWhere('bed_letter', $_REQUEST['bed_letter']);
        $bed_id = $db->select('one');

        $assignment = new HMS_Assignment;
        $assignment->set_asu_username($_REQUEST['username']);
        $assignment->set_bed_id($bed_id);

        return $assignment;
    }

    /**
     * Returns the id of the room based on the building id, floor number and room number
     * Belongs in HMS_Room
     */
    function get_room_id_by_request()
    {
        $db = new PHPWS_DB('hms_room');
        $db->addColumn('id');
        $db->addWhere('building_id', $_REQUEST['halls']);
        $db->addWhere('floor_number', $_REQUEST['floors']);
        $db->addWhere('room_number', $_REQUEST['floors'] . str_pad($_REQUEST['rooms'], 2, "0", STR_PAD_LEFT));
        $db->addWhere('deleted', '1', '!=');
        $results = $db->select('one');
        return $results;
    }

    /**
     * Saves the assignment object to the database.
     */
    function save_assignment()
    {
        if($this->id == NULL) {
            
            $delete_first = HMS_Assignment::delete_assignment($this->asu_username);

            $db = new PHPWS_DB('hms_assignment');
            $result = $db->saveObject($this);
            $msg = $this->get_asu_username() . " has been successfully assigned.<br />";
            return HMS_Assignment::get_username_for_assignment($msg);
        }
    }

    /**
     * Delete the room assignment, not just mark it deleted.
     * Calls delete_assignment and prompts the user for another student to unassign
     */
    function perform_delete_assignment()
    {
        $success = HMS_Assignment::delete_assignment();
        $msg =  "You have removed " . $_REQUEST['asu_username'];
        $msg .= " from " . $_REQUEST['hall_name'];
        $msg .= ", room " . $_REQUEST['room_number'] . ".";
        return HMS_Assignment::get_username_for_deletion($msg);
    }

    /**
     * Allows static deletion of room assignments
     */
    function delete_assignment($username = NULL)
    {
        $db = new PHPWS_DB('hms_assignment');
        if($username == NULL) {
            $db->addWhere('id', $_REQUEST['assignment_id']);
        } else {
            $db->addWhere('asu_username', $username, 'ILIKE');
        }
        $result = $db->delete();
        return $result;
    }

    /**
     * Returns a HMS_Form that has the user input an ASU username to assign to a room
     */
    function get_username_for_assignment($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_username_for_assignment($error); 
    }

    /**
     * Returns a HMS_Form that uses HMS_XML to populate a halls, floor and room list
     */
    function get_hall_floor_room()
    {
        /*
        PHPWS_Core::initModClass('hms', 'HMS_XML.php');
        $_REQUEST['op'] = 'get_halls';
        HMS_XML::main();
        */
        
        $msg = "";

        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        $completed_application = HMS_Application::check_for_application($_REQUEST['username']);
        if(!$completed_application) {
            $msg .= '<font color="red"><b>';
            $msg .= $_REQUEST['username'] . " did not fill out an Housing Application.<br /><br />";
            $msg .= '</b></font>';
            
            PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            $valid_student = HMS_SOAP::is_valid_student($_REQUEST['username']);
            if(!$valid_student){
                $msg .= $_REQUEST['username'] . " is not listed as a valid student. Please contact Electronic Student Services with this error.<br /><br />";
                $msg .= '</b></font>';
                return HMS_Assignment::get_username_for_assignment($msg);
            }
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_hall_floor_room($msg);
    }

    /**
     * Returns a HMS_Form that gives the user the choice to confirm a housing assignment
     */
    function verify_assignment()
    {
        $room_id = HMS_Assignment::get_room_id_by_request();
        $bed_id = HMS_Assignment::get_bed_id_by_request_and_room_id($room_id);

        if(!HMS_Assignment::room_user_gender_compatible($room_id)) {
            $msg = '<font color="red"><b>The gender of the student and the room gender are incompatible.</b></font>';
            return HMS_Assignment::get_username_for_assignment($msg);
        }

        PHPWS_Core::initModClass('hms', 'HMS_Room.php');

        //$assigned = HMS_Assignment::number_assigned_to_room($id);
        //$assignable = HMS_Room::get_bedrooms_per_room($id);
      
        $assigned = HMS_Assignment::is_bed_assigned($bed_id);

        //if($assigned == $assignable) {
        if($assigned) {
            $msg = '<font color="red"><b>This room is full. Please assign to another room or remove a student from this room.<b></font>';
            return HMS_Assignment::get_username_for_assignment($msg);
        }

        $msg = '<br />';

        if(HMS_Room::get_is_reserved($id)) {
            $msg .= '<font color="red"><b>WARNING!! This room is marked as reserved.</b></font><br />';
        }

        if(HMS_Room::get_is_medical($id)) {
            $msg .= '<font color="red"><b>WARNING!! This room is marked as medical.</b></font><br />';
        }

        if($suite = HMS_Room::is_in_suite($id)) {
            $msg .= '<font color="red"><b>WARNING!! The following rooms make up this suite: <br />';
            $msg .= "&nbsp;&nbsp;&nbsp;&nbsp;" . HMS_Room::get_room_number($suite['room_id_zero']) . "<br />";
            $msg .= "&nbsp;&nbsp;&nbsp;&nbsp;" . HMS_Room::get_room_number($suite['room_id_one']) . "<br />";
            if($suite['room_id_two']) {
                $msg .= "&nbsp;&nbsp;&nbsp;&nbsp;" . HMS_Room::get_room_number($suite['room_id_two']) . "<br />";
            }
            if($suite['room_id_three']) {
                $msg .= "&nbsp;&nbsp;&nbsp;&nbsp;" . HMS_Room::get_room_number($suite['room_id_three']) . "<br />";
            }
            $msg .= "</b></font>";
        }

        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::verify_assignment($msg);
    }

    /**
     * Returns the bed_id based on $_REQUEST values
     */
    function get_bed_id_by_request_and_room_id($room_id)
    {
        $db = new PHPWS_DB('hms_bedrooms');
        $db->addColumn('id');
        $db->addWhere('room_id', $room_id);
        $db->addWhere('bedroom_letter', $_REQUEST['bedroom_letter']);
        $br_id = $db->select('one');

        $db = new PHPWS_DB('hms_beds');
        $db->addColumn('id');
        $db->addWhere('bedroom_id', $br_id);
        $db->addWhere('bed_letter', $_REQUEST['bed_letter']);
        $bed_id = $db->select('one');

        return $bed_id;
    }

    /**
     * Checks the bed indicated has not been assigned
     */
    function is_bed_assigned($id)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('id');
        $db->addWhere('bed_id', $id);
        $assigned = $db->select('one');
        if($assigned == NULL || $assigned == FALSE) return false;
        else return true;
    }

    /**
     * Checks room gender matches user gender
     */
    function room_user_gender_compatible($id)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $room_gender = HMS_Room::get_gender_type($id);

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $user_gender = HMS_SOAP::get_gender($_REQUEST['username'], true);
        
        if($room_gender != $user_gender) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns a HMS_Form that gives the user the choice to confirm a room assignment deletion
     */
    function verify_deletion()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::verify_deletion();
    }

    /**
     * Returns a HMS_Form that has the user input an ASU username to delete a room assignment
     */
    function get_username_for_deletion($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_username_for_deletion($error);
    }

    /**
     * Returns a HMS_Form that allows the user to select a Hall and Floor for mass floor assignments
     */
    function get_hall_floor($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_hall_floor($error);
    }

    function show_assignments_by_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::show_assignments_by_floor();
    }

    function main()
    {
        $op = $_REQUEST['op'];
        switch($op)
        {
            case 'create_assignment':
                $assignment = HMS_Assignment::create_assignment();
                return $assignment->save_assignment();
                break;
            case 'begin_create_assignment':
                return HMS_Assignment::get_username_for_assignment();
                break;
            case 'begin_delete_assignment':
                return HMS_Assignment::get_username_for_deletion();
                break;
            case 'delete_assignment':
                return HMS_Assignment::perform_delete_assignment();
                break;
            case 'get_hall_floor_room':
                return HMS_Assignment::get_hall_floor_room();
                break;
            case 'show_assignments_by_floor':
                return HMS_Assignment::show_assignments_by_floor();
                break;
            case 'verify_assignment':
                return HMS_Assignment::verify_assignment();
                break;
            case 'verify_deletion':
                return HMS_Assignment::verify_deletion();
                break;
            case 'begin_by_floor':
                return HMS_Assignment::get_hall_floor();
                break;
            default:
                test($op);
                test($_REQUEST, 1);
                break;
        }
    }
}
?>
