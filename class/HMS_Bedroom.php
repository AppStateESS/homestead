<?php

/**
 * Bedroom objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Bedroom
{
    var $id;
    var $room_id;
    var $is_online;
    var $gender_type;
    var $phone_number;
    var $number_beds;
    var $bedroom_number;
    var $is_medical;
    var $is_reserved;
    var $added_by;
    var $added_on;
    var $updated_by;
    var $updated_on;
    var $deleted;
    var $error;

    function HMS_Bedroom()
    {
        $this->id = NULL;
        $this->is_online = NULL;
        $this->error = "";
    }
    
    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_room_id($room_id)
    {
        $this->room_id = $room_id;
    }

    function get_room_id()
    {
        return $this->room_id;
    }

    function get_is_online()
    {
        return $this->is_online;
    }

    function set_is_online($online)
    {
        $this->is_online = $online;
    }

    function set_gender_type($gender)
    {
        $this->gender_type = $gender;
    }

    function get_gender_type()
    {
        return $this->gender_type;
    }

    function get_number_beds()
    {
        return $this->number_beds;
    }

    function set_number_beds($number_beds)
    {
        $this->number_beds = $number_beds;
    }

    function set_is_reserved($reserved)
    {
        $this->is_reserved = $reserved;
    }

    function get_is_reserved($id = NULL)
    {
        return $this->is_reserved;
    }

    function set_is_medical($medical)
    {
        $this->is_medical = $medical;
    }

    function get_is_medical()
    {
        return $this->is_medical;
    }

    function get_bedroom_letter()
    {
        return $this->bedroom_letter;
    }

    function set_bedroom_letter($bedroom_letter)
    {
        $this->bedroom_letter = $bedroom_letter;
    }

    function set_phone_number($phone)
    {
        $this->phone_number = $phone;
    }

    function get_phone_number()
    {
        return $this->phone_number;
    }

    function set_added_by_on()
    {
        $this->set_added_by();
        $this->set_added_on();
    }

    function set_added_by()
    {
        $this->added_by = Current_User::getId();
    }

    function set_added_on()
    {
        $this->added_on = time();
    }

    function set_updated_by_on()
    {
        $this->set_updated_by();
        $this->set_updated_on();
    }

    function set_updated_by()
    {
        $this->updated_by = Current_User::getId();
    }

    function set_updated_on()
    {
        $this->updated_on = time();
    }

    function set_error($msg)
    {
        $this->error .= $msg;
    }

    function get_error()
    {
        return $this->error;
    }

    function set_deleted($deleted = "0")
    {
        $this->deleted = $deleted;
    }

    function set_variables()
    {
        if($_REQUEST['id']) $this->set_id($_REQUEST['id']);
        $this->set_is_online($_REQUEST['is_online']);
        $this->set_gender_type($_REQUEST['gender_type']);
        $this->set_number_beds($_REQUEST['number_beds']);
        $this->set_is_reserved($_REQUEST['is_reserved']);
        $this->set_is_medical($_REQUEST['is_medical']);
        $this->set_bedroom_letter($_REQUEST['bedroom_letter']);
        $this->set_phone_number($_REQUEST['phone_number']);
        $this->set_deleted();
    }

    function save_bedroom($object = NULL)
    {
        $db = &new PHPWS_DB('hms_bedrooms');
        if($object == NULL) {
            $db->addWhere('id', $_REQUEST['id']);
            $db->addValue('is_online', $_REQUEST['is_online']);
            $db->addValue('gender_type', $_REQUEST['gender_type']);
            $db->addValue('number_beds', $_REQUEST['number_beds']);
            $db->addValue('is_reserved', $_REQUEST['is_reserved']);
            $db->addValue('is_medical', $_REQUEST['is_medical']);
            $db->addValue('bedroom_letter', $_REQUEST['bedroom_letter']);

            if($_REQUEST['phone_number'] != NULL) {
                $db->addValue('phone_number', $_REQUEST['phone_number']);
            }

            $db->addValue('is_medical', $_REQUEST['is_medical']);
            $db->addValue('is_reserved', $_REQUEST['is_reserved']);
            $db->addValue('is_online', $_REQUEST['is_online']);
            $db->addValue('updated_by', Current_User::getId());
            $db->addValue('updated_on', time());
            $db->addValue('deleted', '0');
        } else {
            $success = $db->saveObject($object);
            if(PEAR::isError($success)) {
                test($success);
            }
            return $success;
        }
        $success = $db->update();
        if(PEAR::isError($success)) {
            PHPWS_Error::log($success, 'hms', 'HMS_Bedroom::save_bedroom');
            $final = "Error saving Room. Please consult Electronic Student Services.<br />";
            $final .= "Error: $success";
        } else {
            $final = "Bedroom successfully saved!";
        }
        return $final;
    }
    
    function delete_bedroom($id)
    {
        $room_db = &new PHPWS_DB('hms_room');
        $room_db->addValue('deleted', '1');
        $room_db->addWhere('building_id', $bid);
        $room_db->addWhere('room_number', $room_number);
        $room_db->addValue('deleted_by', Current_User::getId());
        $room_db->addValue('deleted_on', time());
        $success = $room_db->update();
        return $success;
    }

    function delete_bedrooms_by_building($building_id)
    {
        $sql = "UPDATE hms_bedrooms ";
        $sql .= "SET deleted = 1 ";
        $sql .= "WHERE room_id = hms_room.id ";
        $sql .= "AND hms_room.building_id = $building_id;";

        $db = new PHPWS_DB;
        $result = $db->query($sql);
        return $result;
    }

    function edit_room()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        return $form->edit_room();
    }

    function is_bedroom_empty($id)
    {
        $db = &new PHPWS_DB('hms_assignment');
        $db->addColumn('id');
        $db->addWhere('hms_assignment.bed_id', 'hms_beds.id');
        $db->addWhere('hms_beds.bedroom_id', 'hms_bedrooms.id');
        $db->addWhere('hms_bedrooms.id', $id);
        $db->addWhere('hms_assignment.deleted', 0);
        $db->addWhere('hms_beds.deleted', 0);
        $db->addWhere('hms_bedrooms.deleted', 0);
        $db->addWhere('hms_bedrooms.is_online', 1);
        $result = $db->select('one');
        return empty($result);
    }

    function static_get_number_beds($id)
    {
        $db = &new PHPWS_DB('hms_bedrooms');
        $db->addColumn('number_beds');
        $db->addWhere('id', $id);
        return $db->select('one');
    }

    function get_other_bed($bedroom, $bed)
    {
        $db = &new PHPWS_DB('hms_beds');
        $db->addColumn('id');
        $db->addWhere('hms_beds.bedroom_id', $bedroom);
        $db->addWhere('hms_beds.id', $bed, '!=');
        $db->addWhere('hms_beds.deleted', 0);
        return $db->select('one');
    }

    function determine_lifestyle_type($bedroom)
    {
        $db = new PHPWS_DB('hms_bedrooms');
        $db->addColumn('hms_residence_hall.gender_type');
        $db->addWhere('hms_bedrooms.id',$bedroom);
        $db->addWhere('hms_bedrooms.room_id','hms_room.id');
        $db->addWhere('hms_room.floor_id','hms_floor.id');
        $db->addWhere('hms_floor.building','hms_residence_hall.id');
        return $db->select('one') < 2 ? 1 : 2;
    }

    function determine_gender($bedroom)
    {
        $db = new PHPWS_DB('hms_bedrooms');
        $db->addColumn('hms_room.gender_type');
        $db->addWhere('hms_bedrooms.id',$bedroom);
        $db->addWhere('hms_bedrooms.room_id','hms_room.id');
        return $db->select('one');
    }

    function main()
    {
        switch($_REQUEST['op'])
        {
            default:
                return $_REQUEST['op'] . " is the operation<br />";
                break;
        }
    }
};
?>
