<?php

#error_reporting(E_ALL);

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
    var $meal_option;
    var $deleted = 0;
    var $timestamp;

    /**
     * Return the id for the current assignment object
     */
    function get_id($type = NULL, $value = NULL)
    {
        if($type == NULL) {
            return $this->id;
        } else {
            $db = &new PHPWS_DB('hms_assignment');
            $db->addColumn('id');
            $db->addWhere($type, $value);
            $db->addWhere('deleted', 0);
            $id = $db->select('one');
            return $id;
        }
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
    function get_asu_username($bid = NULL)
    {
        if($bid != NULL) {
            $db = &new PHPWS_DB('hms_assignment');
            $db->addColumn('asu_username');
            $db->addWhere('bed_id', $bid);
            $db->addWhere('deleted', 0);
            $username = $db->select('one');
            return $username;
        } else {
            return $this->asu_username;
        }
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
    function get_bed_id($type = NULL, $value = NULL)
    {
        if($type == NULL) {
            return $this->bed_id;
        } else {
            $db = &new PHPWS_DB('hms_assignment');
            $db->addColumn('bed_id');
            $db->addWhere($type, $value);
            $db->addWhere('deleted', 0);
            $bed_id = $db->select('one');
            return $bed_id;
        }
    }

    /**
     * Sets the bed associated with the current assignment
     */
    function set_bed_id($bnumber)
    {
        $this->bed_id = $bnumber;
    }

    function set_meal_option($meal_option)
    {
        $this->meal_option = $meal_option;
    }

    function get_meal_option($type = NULL, $value = NULL)
    {
        $db = &new PHPWS_DB('hms_assignment');
        $db->addColumn('meal_option');
        $db->addWhere($type, $value);
        $db->addWhere('deleted', 0);
        $meal_option = $db->select('one');
        return $meal_option;
    }

    function set_deleted($deleted)
    {
        $this->deleted = $deleted;
    }

    function get_deleted()
    {
        return $this->deleted;
    }

    function set_timestamp($ts)
    {
        $this->timestamp = $ts;
    }

    function get_timestamp()
    {
        return $this->timestamp;
    }

    /**
     * Creates the actual assignment object
     */
    function &create_assignment($bid = NULL, $un = NULL, $meal = NULL)
    {
        $assignment = new HMS_Assignment;
        
        if($bid == NULL) {
            $db = new PHPWS_DB('hms_room');
            $db->addColumn('id');
            $db->addWhere('building_id', $_REQUEST['hall']);
            $db->addWhere('floor_number', $_REQUEST['floor']);
            $db->addWhere('room_number', $_REQUEST['floor'] . str_pad($_REQUEST['room'], 2, "0", STR_PAD_LEFT));
            $db->addWhere('deleted', '0');
            $rid = $db->select('one');

            $db = new PHPWS_DB('hms_bedrooms');
            $db->addColumn('id');
            $db->addWhere('room_id', $rid);
            $db->addWhere('bedroom_letter', $_REQUEST['bedroom_letter']);
            $db->addWhere('deleted', '0');
            $br_id = $db->select('one');

            $db = new PHPWS_DB('hms_beds');
            $db->addColumn('id');
            $db->addWhere('bedroom_id', $br_id);
            $db->addWhere('bed_letter', $_REQUEST['bed_letter']);
            $db->addWhere('deleted', '0');
            $bed_id = $db->select('one');

            $assignment->set_asu_username($_REQUEST['username']);
            $meal_option = '1';
        } else {
            $bed_id = $bid;
            $meal_option = $meal;
            $assignment->set_asu_username($un);
        }

        $assignment->set_timestamp(mktime());
        $assignment->set_deleted('0');
        $assignment->set_bed_id($bed_id);
        $assignment->set_meal_option($meal_option);

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
     * Save the created assignment.
     * Calls save_assignment, reports the created assignment information, and
     * prompts the user for another student to assign.
     */
    function perform_save_assignment()
    {
        $success = $this->save_assignment();
        if($success) {
            $msg  = "You have placed " . $_REQUEST['username'];
            $msg .= " into " . $_REQUEST['hall_name'];
            $msg .= ", room " . $_REQUEST['room_number'];
            $msg .= ", bedroom " . $_REQUEST['bedroom_letter'];
            $msg .= ", bed " . $_REQUEST['bed_letter'];
            return HMS_Assignment::get_username_for_assignment($msg);
        } else {
            return "Assignment failed -- unknown error.";
        }
    }

    /**
     * Saves the current assignment object to the database.
     */
    function save_assignment()
    {
        // If we get here and they're already assigned, someone had to click
        // "that's okay; do it anyway".  So delete the old assignment.
        // NOTE: THIS SHOULD CHANGE BECAUSE IT IS AN INCREDIBLY DIRTY HIPPIE OF A HACK
        $delete_first = HMS_Assignment::delete_assignment('asu_username', $this->get_asu_username());

        if(PEAR::isError($delete_first))
            PHPWS_Error::log($delete_first, 'hms', 'HMS_Assignment::save_assignment');

        $db = new PHPWS_DB('hms_assignment');
        $result = $db->saveObject($this);
        return $result;
    }

    /**
     * Delete the room assignment.
     * Calls delete_assignment and prompts the user for another student to unassign
     */
    function perform_delete_assignment()
    {
        $success = HMS_Assignment::delete_assignment('asu_username', $_REQUEST['asu_username']);
        $msg =  "You have removed " . $_REQUEST['asu_username'];
        $msg .= " from " . $_REQUEST['hall_name'];
        $msg .= ", room " . $_REQUEST['room_number'] . ".";
        return HMS_Assignment::get_username_for_deletion($msg);
    }

    /**
     * Allows static deletion of room assignments
     */
    function delete_assignment($type = NULL, $arg = NULL )
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addValue('deleted', 1);
        $db->addWhere($type, $arg);
        $result = $db->update();

        // needs call to HMS_SOAP to delete a student's room assignment

        return $result;
    }

    /**
     * Moves the student from one room to another
     */
    function move_student($username, $old_bed_id, $new_bed_id, $meal_option)
    {
        $db = &new PHPWS_DB('hms_assignment');
        $db->addValue('deleted', 1);
        $db->addWhere('asu_username', $username);
        $db->addWhere('bed_id', $old_bed_id);
        $results = $db->update();

        $db = &new PHPWS_DB('hms_assignment');
        $db->addValue('asu_username', $username);
        $db->addValue('bed_id', $new_bed_id);
        $db->addValue('deleted', 0);
        $db->addValue('timestamp', mktime());
        $db->addValue('meal_option', $meal_option);
        $results = $db->insert();

        // needs call to HMS_SOAP to move student
        
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

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        if(HMS_Assignment::check_for_assignment($_REQUEST['username'])) {
//            return HMS_Assignment::get_username_for_assignment($_REQUEST['username'] . " is already assigned.");
            $msg .= '<font color="red">Warning: <b>' . HMS_SOAP::get_name($_REQUEST['username']) . " (" . $_REQUEST['username'] . ")</b> is already assigned.  Continuing will cause this student's assignment to be moved.</font><br /><br />";
        } else {
            PHPWS_Core::initModClass('hms', 'HMS_Application.php');
            $completed_application = HMS_Application::check_for_application($_REQUEST['username']);
            if(!$completed_application) {
                $msg .= '<font color="red"><b>';
                $msg .= $_REQUEST['username'] . " did not fill out an Housing Application.<br /><br />";
                $msg .= '</b></font>';
                
                $valid_student = HMS_SOAP::is_valid_student($_REQUEST['username']);
                if(!$valid_student){
                    $msg .= $_REQUEST['username'] . " is not listed as a valid student. Please contact Electronic Student Services with this error.<br /><br />";
                    $msg .= '</b></font>';
                    return HMS_Assignment::get_username_for_assignment($msg);
                }
            }
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_hall_floor_room($msg);
    }

    /**
     * Returns TRUE if the username exists in hms_assignment and is not deleted,
     * FALSE if the username either is not in hms_assignment or is deleted.
     */

    function check_for_assignment($asu_username)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('deleted', 0);
        $db->addWhere('asu_username', $asu_username, 'ILIKE');

        return !is_null($db->select('row'));
    }

    /**
     * Returns a HMS_Form that gives the user the choice to confirm a housing assignment
     */
    function verify_assignment()
    {
        $room_id = HMS_Assignment::get_room_id_by_request();
        $bed_id = HMS_Assignment::get_bed_id_by_request_and_room_id($room_id);

        if(!$room_id || !$bed_id) {
            PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
            return HMS_Form::get_hall_floor_room('<font color="red"><b>The selected floor, room, bedroom, or bed does not exist in this building.</b></font><br /><br />');
        }

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
            PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
            $msg = '<font color="red"><b>This bed has already been assigned to another student. Please assign to another bed.<b></font><br /><br />';
            return HMS_Form::get_hall_floor_room($msg);
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
     * Returns a HMS_Form that has the user input an ASU username to assign to a room
     */
    function get_username_for_move($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::get_username_for_move($error); 
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
        $db->addWhere('deleted', 0);
        $br_id = $db->select('one');

        $db = new PHPWS_DB('hms_beds');
        $db->addColumn('id');
        $db->addWhere('bedroom_id', $br_id);
        $db->addWhere('bed_letter', $_REQUEST['bed_letter']);
        $db->addWhere('deleted', 0);
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
        $db->addWhere('deleted', 0);
        $assigned = $db->select('one');
        if($assigned == NULL || $assigned == FALSE) return false;
        else return true;
    }

    /**
     * Checks the specified user to see if they're already assigned
     */
    function is_user_assigned($uid)
    {
        $db = &new PHPWS_DB('hms_assignment');
        $db->addColumn('id');
        $db->addWhere('asu_username', $uid);
        $db->addWhere('deleted', 0);
        $assigned = $db->select('one');
        if($assigned == NULL || $assigned == FALSE) return false;
        else return true;
    }

    /**
     * Checks room gender matches user gender
     */
    function room_user_gender_compatible($id, $user_id = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $room_gender = HMS_Room::get_gender_type($id);

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        if($user_id == NULL) {
            $user_gender = HMS_SOAP::get_gender($_REQUEST['username'], true);
        } else {
            $user_gender = HMS_SOAP::get_gender($user_id, true);
        }
        
        if($room_gender != $user_gender) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Handles issues with requested roommates, if any
     */
    function create_assignment_handle_roommates()
    {
    }

    /**
     * Returns a HMS_Form that gives the user the choice to confirm a room assignment deletion
     */
    function verify_deletion()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $content = HMS_Form::verify_deletion();
        if($content === false) {
            return HMS_Assignment::get_username_for_deletion("The username you enterred is not currently assigned.");
        }
        return $content;
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

    function show_assignments_by_floor($msg = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::show_assignments_by_floor($msg);
    }

    function verify_assign_floor($msg = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::verify_assign_floor($msg);
    }

    function assign_floor()
    {
        if(isset($_REQUEST['cancel'])) {
            return HMS_Assignment::get_hall_floor();
        } else if (isset($_REQUEST['edit'])) {
            return HMS_Assignment::show_assignments_by_floor();
        } else if (isset($_REQUEST['submit'])) {
            reset($_REQUEST);
            while(list($key, $uid) = each($_REQUEST))
            {
                if(substr($key, 0, 4) == "bed_") {
                    if(substr($key, 4, 1) == "_") {
                        $bed = substr($key, 5);
                    } else {
                        $bed = substr($key, 4);
                    }
           
                    $meal_option = $_REQUEST['meal_option_' . $bed];

                    $assignment = HMS_Assignment::create_assignment($bed, $uid, $meal_option);
                    $saved = $assignment->save_assignment();
                    if(PEAR::isError($saved)) {
                        test($saved);
                        return "There was an error placing $uid in their bed.";
                    }
                }
            }
            $success = "<font color='green'>All assignments completed successfully!</font><br />";
            return HMS_Assignment::show_assignments_by_floor($success);
        }
    }

    function generate_student_assignment_data()
    {
        $sql = "
SELECT hms_assignment.asu_username,
       hms_beds.phone_number,
       hms_room.displayed_room_number,
       hms_room.id as room_id,
       hms_floor.ft_movein,
       hms_floor.c_movein,
       hms_residence_hall.hall_name

FROM hms_room,
     hms_residence_hall,
     hms_beds,
     hms_bedrooms,
     hms_floor,
     hms_assignment

WHERE hms_assignment.bed_id = hms_beds.id           AND
      hms_beds.bedroom_id   = hms_bedrooms.id       AND
      hms_bedrooms.room_id  = hms_room.id           AND
      hms_room.floor_id     = hms_floor.id          AND
      hms_floor.building    = hms_residence_hall.id AND

      hms_assignment.deleted     = 0 AND
      hms_beds.deleted           = 0 AND
      hms_bedrooms.deleted       = 0 AND
      hms_room.deleted           = 0 AND
      hms_floor.deleted          = 0 AND
      hms_residence_hall.deleted = 0 AND

      hms_bedrooms.is_online       = 1 AND
      hms_room.is_online           = 1 AND
      hms_floor.is_online          = 1 AND
      hms_residence_hall.is_online = 1 LIMIT 10";

        $results = PHPWS_DB::getAll($sql);

        if(PHPWS_Error::isError($results)) {
            test($results, 1);
        }

        $db = &new PHPWS_DB('hms_cached_student_info');
        $err = $db->delete();

        if(PHPWS_Error::isError($err)) {
            test($err, 1);
        }

        echo "Crude Progress Bar:<br />";

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $total = count($results);
        $count = 0;
        $whole = 1;
        foreach($results as $row) {
            $db = &new PHPWS_DB('hms_cached_student_info');
            $db->addValue('asu_username', $row['asu_username']);
            $db->addValue('room_number', $row['displayed_room_number']);
            $db->addValue('hall_name', $row['hall_name']);
            
            $student = HMS_SOAP::get_student_info($row['asu_username']);

            $address = HMS_SOAP::get_for_realz_address($student);
            
            $db->addValue('first_name', $student->first_name);
            $db->addValue('middle_name', $student->middle_name);
            $db->addValue('last_name', $student->last_name);
            $db->addValue('address1', $address->line1);
            $db->addValue('address2', $address->line2);
            $db->addValue('address3', $address->line3);
            $db->addValue('city', $address->city);
            $db->addValue('state', $address->state);
            $db->addValue('zip', $address->zip);
            if(isset($student->phone) && !empty($student->phone)) {
                $number = $student->phone->area_code;
                $number .= '-';
                $number .= $student->phone->number;
                if(!empty($student->phone->ext)) {
                    $number .= ' x'.$student->phone->ext;
                }
                $db->addValue('phone_number', $number);
            }

            // Roommates
            $sql = "
                SELECT asu_username
                FROM hms_bedrooms,
                     hms_beds
                LEFT OUTER JOIN hms_assignment
                ON hms_assignment.bed_id = hms_beds.id
                WHERE hms_beds.bedroom_id  = hms_bedrooms.id AND
                      hms_bedrooms.room_id = {$row['room_id']}
            ";
            $mates = PHPWS_DB::getAll($sql);
            if(PHPWS_Error::isError($mates)) {
                test($mates,1);
            }

            foreach($mates as $mate) {
                if(empty($mate['asu_username']))
                    continue;
                if(strtolower($mate['asu_username']) ==
                   strtolower($row['asu_username']))
                    continue;

                $roommate = HMS_SOAP::get_student_info($mate['asu_username']);
                $db->addValue('roommate_name',
                    $roommate->last_name . ', ' .
                    $roommate->first_name . ' ' .
                    $roommate->middle_name);
                $db->addValue('roommate_user',
                    $mate['asu_username']);
                break;
            }

            // Room Phone Number
            if(!empty($row['phone_number'])) {
                $db->addValue('room_phone', '828-262-' . $row['phone_number']);
            }

            // Banner Crap
            $db->addValue('gender', $student->gender);
            $db->addValue('student_type', $student->student_type);
            $db->addValue('class', $student->projected_class);
            $db->addValue('credit_hours', $student->credhrs_completed);
            $db->addValue('deposit_date', $student->deposit_date);
            $db->addValue('deposit_waived', $student->deposit_waived);

            if($student->student_type == 'T' || ($student->student_type == 'F' && $student->credhrs_completed == 0)) {
                $db->addValue('movein_time',
                    $row['ft_movein'] . '   Freshmen and Transfer ONLY');
            } else {
                $db->addValue('movein_time',
                    $row['c_movein'] . '   Upperclassmen ONLY');
            }

            $err = $db->insert();
            if(PHPWS_Error::isError($err)) {
                test($err, 1);
            }

            $percent = ((++$count / $total) * 100);
            if($percent >= $whole) {
                echo $whole++ . '%... ';
                ob_flush();
                flush();
            }
        }
    }

    function main()
    {
        $op = $_REQUEST['op'];
        switch($op)
        {
            case 'create_assignment':
                Layout::addPageTitle("Assignment Created");
                $assignment = HMS_Assignment::create_assignment();
                return $assignment->perform_save_assignment();
                break;
            case 'begin_create_assignment':
                Layout::addPageTitle("Select Student - Create Assignment");
                return HMS_Assignment::get_username_for_assignment();
                break;
            case 'begin_delete_assignment':
                Layout::addPageTitle("Select Student - Delete Assignment");
                return HMS_Assignment::get_username_for_deletion();
                break;
            case 'begin_move_assignment':
                Layout::addPageTitle("Select Student - Change Assignment");
                return HMS_Assignment::get_username_for_move();
                break;
            case 'get_move_hall_floor_room':
                Layout::addPageTitle("Select Room - Change Assignment");
                return HMS_Assignment::get_move_hall_floor_room();
                break;
            case 'delete_assignment':
                Layout::addPageTitle("Assignment Deleted");
                return HMS_Assignment::perform_delete_assignment();
                break;
            case 'get_hall_floor_room':
                Layout::addPageTitle("Select Room - Create Assignment");
                return HMS_Assignment::get_hall_floor_room();
                break;
            case 'show_assignments_by_floor':
                Layout::addPageTitle("Show Assignments By Floor");
                return HMS_Assignment::show_assignments_by_floor();
                break;
            case 'verify_assignment':
                Layout::addPageTitle("Verify - Create Assignment");
                return HMS_Assignment::verify_assignment();
                break;
            case 'verify_deletion':
                Layout::addPageTitle("Verify - Delete Assignment");
                return HMS_Assignment::verify_deletion();
                break;
            case 'begin_by_floor':
                Layout::addPageTitle("Select Floor - Create Assignments");
                return HMS_Assignment::get_hall_floor();
                break;
            case 'assign_floor':
                Layout::addPageTitle("Floor Assigned - Create Assignments");
                return HMS_Assignment::assign_floor();
                break;
            case 'verify_assign_floor':
                Layout::addPageTitle("Verify Floor - Create Assignments");
                return HMS_Assignment::verify_assign_floor();
                break;
            case 'create_assignment_handle_roommates':
                Layour::addPageTitle("Handle Roommates - Create Assignments");
                return HMS_Assignment::create_assignment_handle_roommates();
                break;
            default:
                test($op);
                test($_REQUEST, 1);
                break;
        }
    }
}
?>
