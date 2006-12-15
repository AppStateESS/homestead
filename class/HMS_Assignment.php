<?php

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
    var $floor_id;
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
     * Returns the floor id for the current assignment
     */
    function get_floor_id()
    {
        return $this->floor_id;
    }

    /**
     * Sets the floor id for the current assignment
     */
    function set_floor_id($fid)
    {
        $this->floor_id = $fid;
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
     * Creates the actual assignment object from an ASU username, building id, floor id and room id
     */
    function create_assignment($sid, $bid, $fid, $rid)
    {
        $assignment = new HMS_Assignment;
        $assignment->set_asu_username($sid);
        $assignment->set_building_id($bid);
        $assignment->set_floor_id($fid);
        $assignment->set_room_id($rid);
        test($assignment, 1);
    }

    /**
     * Saves the assignment object to the database.
     */
    function save_assignment()
    {

    }

    function main()
    {
        $op = $_REQUEST['op'];
        switch($op)
        {
            case 'create_assignment':
                return "create assignment";
                break;
            case 'begin_create_assignment':
                return "Time to start";
                break;
            default:
                return $op;
                break;
        }
    }
}
?>
