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
    var $building_id;
    var $room_id;
    var $bed;

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
     * Returns the building ID for the current assignment
     */
    function get_building_id()
    {
        return $this->building_id;
    }

    /**
     * Sets the building ID for the current assignment
     */
    function set_building_id($bid)
    {
        $this->building_id = $bid;
    }

    /**
     * Returns the room id of the current assignment
     */
    function get_room_id()
    {
        return $this->room_id;
    }

    /**
     * Sets the room id of the current assignment
     */
    function set_room_id($rid)
    {
        $this->room_id = $rid;
    }

    /**
     * Returns the bed associated with the current assignment
     */
    function get_bed()
    {
        return $this->bed;
    }

    /**
     * Sets the bed associated with the current assignment
     */
    function set_bed($bnumber)
    {
        $this->bed = $bnumber;
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
        $results = $db->select('row');

        $assignment = new HMS_Assignment;
        $assignment->set_asu_username($_REQUEST['username']);
        $assignment->set_building_id($_REQUEST['hall']);
        $assignment->set_room_id($results['id']);

        return $assignment;
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

        PHPWS_Core::initModClass('hms', 'HMS_Questionnaire.php');
        $completed_application = HMS_Questionnaire::check_for_questionnaire($_REQUEST['username']);
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
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::verify_assignment();
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
            case 'verify_assignment':
                return HMS_Assignment::verify_assignment();
                break;
            case 'verify_deletion':
                return HMS_Assignment::verify_deletion();
                break;
            default:
                test($op);
                test($_REQUEST, 1);
                break;
        }
    }
}
?>
