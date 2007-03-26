<?php

/**
 * Floor objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Floor
{
    var $id;
    var $floor_number;
    var $building;
    var $is_online;
    var $number_rooms;
    var $bedrooms_per_room;
    var $beds_per_bedroom;
    var $gender_type;
    var $deleted;
    var $error;
    var $is_new_floor;
    var $added_by;
    var $added_on;
    var $updated_by;
    var $updated_on;
    var $deleted_by;
    var $deleted_on;

    function HMS_Floor()
    {
        $this->id = NULL;
        $this->is_online = NULL;
        $this->is_new_floor = FALSE;
        $this->error = "";
    }
    
    function set_error_msg($msg)
    {
        $this->error .= $msg;
    }

    function get_error_msg()
    {
        return $this->error;
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_floor_number($number)
    {
        $this->floor_number = $number;
    }

    function get_floor_number()
    {
        return $this->floor_number;
    }

    function set_building($building)
    {
        $this->building = $building;
    }

    function get_building()
    {
        return $this->building;
    }

    function set_number_rooms($number_rooms, $bid = NULL)
    {
        if($bid != NULL) {
            $floor_db = &new PHPWS_DB('hms_floor');
            $floor_db->addWhere('building', $bid);
            $floor_db->addValue('number_rooms', $number_rooms);
            $floor_db->addWhere('deleted', '0');
            $success = $floor_db->update();

            if(PEAR::isError($success)) {
                PHPWS_Error::log($success, 'hms', 'HMS_Floor::set_number_rooms');
            }
        
            return $success;
        } else {
            $this->number_rooms = $number_rooms;
        }
    }

    function get_number_rooms()
    {
        return $this->number_rooms;
    }

    function set_bedrooms_per_room($beds, $bid = NULL)
    {
        if($bid != NULL) {
            $floor_db = &new PHPWS_DB('hms_floor');
            $floor_db->addWhere('building', $bid);
            $floor_db->addValue('bedrooms_per_room', $beds);
            $floor_db->addWhere('deleted', 0);
            $success = $floor_db->update();

            if(PEAR::isError($success)) {
                PHPWS_Error::log($success, 'hms', 'HMS_Floor::set_bedrooms_per_room');
            }

            return $success;
        } else {
            $this->bedrooms_per_room = $beds;
        }
    }

    function get_bedrooms_per_room()
    {
        return $this->bedrooms_per_room;
    }

    function set_beds_per_bedroom($beds)
    {
        $this->beds_per_bedroom = $beds;
    }

    function get_beds_per_bedroom()
    {
        return $this->beds_per_bedroom;
    }

    function set_gender_type($gender, $id = NULL, $building = NULL)
    {
        if($building != NULL) {
            $db = &new PHPWS_DB('hms_floor');
            $db->addWhere('building', $building);
            $db->addWhere('deleted', '0');
            $db->addValue('gender_type', $gender);
            $success = $db->update();
            if(PEAR::isError($success)) {
                PHPWS_Error::log($success, 'hms', 'HMS_Floor::set_gender');
            } else {
                PHPWS_Core::initModClass('hms', 'HMS_Room.php');
                $success = HMS_Room::set_gender_type($gender, $id, $building);
            }
            return $success;
        } else {
            $this->gender_type = $gender;
        }
    }

    function get_gender_type()
    {
        return $this->gender_type;
    }

    function set_is_online($online, $id = NULL, $building = NULL)
    {
        if($building != NULL) {
            $db = &new PHPWS_DB('hms_floor');
            $db->addWhere('building', $building);
            $db->addWhere('deleted', '0');
            $db->addValue('is_online', $online);
            $success = $db->update();
            if(PEAR::isError($success)) {
                PHPWS_Error::log($success, 'hms', 'HMS_Floor::set_is_online');
            } else {
                PHPWS_Core::initModClass('hms', 'HMS_Room.php');
                $success = HMS_Room::set_is_online($online, $id, $building);
            }
            return $success;
        } else {
            $this->is_online = $online;
        }
    }

    function get_is_online()
    {
        return $this->is_online;
    }

    function set_is_new_floor($new_floor)
    {
        $this->is_new_floor = $new_floor;
    }

    function get_is_new_floor()
    {
        return $this->is_new_floor;
    }

    function set_deleted($deleted)
    {
        $this->deleted = $deleted;
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

    function set_variables()
    {
        if(!Current_User::authorized('hms', 'add_floors') ||
           !Current_User::authorized('hms', 'edit_floors') ||
           !Current_User::authorized('hms', 'delete_floors')) {
            $content = "You are an unauthorized user!<br />";
            $content .= "Your IP address has been logged and an email has been sent<br />";
            $content .= "to the System Administrator notifying that individual of a crack attempt!<br />";
            die($content);
        }

        if($_REQUEST['id']) $this->set_id($_REQUEST['id']);
        
        if($_REQUEST['is_new_floor'])
            $this->set_is_new_floor($_REQUEST['is_new_floor']);
        else 
            $this->set_is_new_floor(FALSE);

        if($_REQUEST['floor_number'] == 0 && !$_REQUEST['id']) {
            $db = &new PHPWS_DB('hms_residence_hall');
            $db->addWhere('id', $_REQUEST['building']);
            $db->addValue('number_floors');
            $num_floors  = $db->select('one');
            $this->set_floor_number($num_floors);
        } else {
            $this->set_floor_number($_REQUEST['floor_number']);
        }

        $this->set_is_online($_REQUEST['is_online']);
        $this->set_building($_REQUEST['building']);
        $this->set_number_rooms($_REQUEST['number_rooms']);
        $this->set_gender_type($_REQUEST['gender_type']);
        $this->set_bedrooms_per_room($_REQUEST['bedrooms_per_room']);
        $this->set_beds_per_bedroom($_REQUEST['beds_per_bedroom']);
        $this->set_deleted('0');
    }

    function save_floor_object($object, $create_rooms = FALSE)
    {
        $db = &new PHPWS_DB('hms_floor');
        if(!isset($object['id'])) {
            $object->set_added_by_on();
        }
        $object->set_updated_by_on();
        $floor_id = $db->saveObject($object);
        if (PEAR::isError($floor_id)) {
            PHPWS_Error::log($floor_id);
            return $floor_id;
        }

        if($create_rooms == TRUE) {
            PHPWS_Core::initModClass('hms', 'HMS_Room.php');
            PHPWS_Core::initModClass('hms', 'HMS_Bedroom.php');
            PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
            for($i = 1; $i <= $object->get_number_rooms(); $i++) {
                $room = &new HMS_Room;
                $room->set_room_number($object->get_floor_number() . str_pad($i, 2, "0",STR_PAD_LEFT));
                $room->set_building_id($object->get_building());
                $room->set_floor_number($object->get_floor_number());
                $room->set_floor_id($floor_id);
                $room->set_bedrooms_per_room($object->get_bedrooms_per_room());
                $room->set_beds_per_bedroom($object->get_beds_per_bedroom());
                $room->set_gender_type($object->get_gender_type());
                $room->set_is_online($object->get_is_online());
                $room->set_is_reserved('0');
                $room->set_is_medical('0');
                //$room->set_number_occupants('0');
                $success = HMS_Room::save_room_object($room);
                if(PEAR::isError($success)) {
                    test($success);
                    return $success;
                }
            
                $br_letter = 'a';
                for($j = 1; $j <= $object->get_bedrooms_per_room(); $j++) {
                    $bedroom = new HMS_Bedroom;
                    $bedroom->set_room_id($success);
                    $bedroom->set_is_online($object->get_is_online());
                    $bedroom->set_gender_type($object->get_gender_type());
                    $bedroom->set_number_beds($object->get_beds_per_bedroom());
                    $bedroom->set_is_reserved(0);
                    $bedroom->set_is_medical(0);
                    $bedroom->set_added_by();
                    $bedroom->set_added_on();
                    $bedroom->set_updated_by();
                    $bedroom->set_updated_on();
                    $bedroom->set_bedroom_letter($br_letter);
                    $saved_br = HMS_Bedroom::save_bedroom($bedroom);
                   
                    if($br_letter == 'a') $br_letter = 'b';
                    else if($br_letter == 'b') $br_letter = 'c';
                    else if($br_letter == 'c') $br_letter = 'd';
                    
                    if(PEAR::isError($saved_br)) {
                        test($saved_br);
                        return $saved_br;
                    }
                    
                    $bed_letter = 'a';
                    for($k = 1; $k <= $object->get_beds_per_bedroom(); $k++) {
                        $bed = new HMS_Bed;
                        $bed->set_bedroom_id($saved_br);
                        $bed->set_bed_letter($bed_letter);
                        $bed->set_deleted();
                        $saved_bed = HMS_Bed::save_bed($bed);

                        if($bed_letter == 'a') $bed_letter = 'b';
                        else if($bed_letter == 'b') $bed_letter = 'c';
                        else if($bed_letter == 'c') $bed_letter = 'd';

                        if(PEAR::isError($saved_bed)) {
                            test($saved_bed);
                            return $saved_bed;
                        }
                    } // end bed creation
                } // end bedroom creation
            } // end room creation
        } else {
            $db = &new PHPWS_DB('hms_room');
            $db->addValue('gender_type', $object->get_gender_type());
            $db->addValue('is_online', $object->get_is_online());
            $db->addWhere('building_id', $object->get_building());
            $db->addWhere('floor_number', $object->get_floor_number());
            $db->addWhere('deleted', 0);
            $result = $db->update();
            if(PEAR::isError($result)) {
                test($result);
                return $result;
            }
        }

        $final = "Floor saved successfully!<br />";
        return $final;
    }

    function save_floor()
    {
        if(!Current_User::authorized('hms', 'add_floor') ||
           !Current_User::authorized('hms', 'edit_floor') ||
           !Current_User::authorized('hms', 'delete_floor')) {
            $final = "You are a <b><font color=\"red\">BAD BAD PERSON!<font></b><br />";
            $final .= "This event and your IP address has been logged with an email sent to the System Administrator.<br />";
            return $final;
        }

        $db = &new PHPWS_DB('hms_floor');
        $db->addWhere('building', $this->building);
        $db->addWhere('floor_number', $this->floor_number);
        $db->addWhere('deleted', '0');
        $exists = $db->select();
        unset($db);

        if($this->is_new_floor == TRUE && ($exists == FALSE || $exists == NULL)) {
            $db = &new PHPWS_DB('hms_floor');
            $this->set_added_by_on();
            $this->set_updated_by_on();
            $floor_id = $db->saveObject($this);
            if($floor_id) $final = "Floor saved successfully!<br />";
            else $final = "Problem saving the floor.<br />";
            // here I need to add logic to add the appropriate number of rooms for this floor
        } else if($this->is_new_floor == TRUE && $exists == TRUE) {
            $tpl['TITLE'] = "Problem saving Floor";
            $tpl['CONTENT'] = "You tried to add a floor that already exists!";
            $final = PHPWS_Template::process($tpl, 'hms', 'admin/title_and_message.tpl');
        } else {
            // save $this 
            $db = &new PHPWS_DB('hms_floor');
            $this->set_updated_by_on();
            $floor_id = $db->saveObject($this);
            $final = "Floor was saved successfully.<br />";
        }
        return $final;
    }

    function delete_floors($bid, $floor = NULL, $one = FALSE)
    {
        $floor_db = &new PHPWS_DB('hms_floor');
        $floor_db->addWhere('building', $bid);
        if($floor != NULL && $one == FALSE) {
            $floor_db->addWhere('floor_number', $floor, '>');
        } else if ($floor != NULL && $one == TRUE) {
            $floor_db->addWhere('floor_number', $floor);
        }
        $floor_db->addValue('deleted', 1);
        
        $floor_result = $floor_db->update();
        
        if(PEAR::isError($result)) {
            PHPWS_Error::log($result, 'hms', 'HMS_Floor::delete_floors');
        }
        
        $db = &new PHPWS_DB;
        $sql  = "UPDATE hms_bedrooms ";
        $sql .= "SET deleted = 1 ";
        $sql .= "WHERE room_id = hms_room.id ";
        if($floor != NULL) {
            $sql .= "AND hms_room.floor_id = hms_floor.id ";
            $sql .= "AND hms_floor.floor_number = $floor ";
        }
        $sql .= "AND hms_room.building_id = $bid ";
        $result = $db->query($sql);

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result, 'hms', 'HMS_Floor::delete_floors');
        }
       
        $db = &new PHPWS_DB;
        $sql  = "UPDATE hms_beds ";
        $sql .= "SET deleted = 1 ";
        $sql .= "WHERE bedroom_id = hms_bedrooms.id ";
        $sql .= "AND hms_bedrooms.room_id = hms_room.id ";
        if($floor != NULL) {
            $sql .= "AND hms_room.floor_id = hms_floor.id ";
            $sql .= "AND hms_floor.floor_number = $floor ";
        }
        $sql .= "AND hms_room.building_id = $bid;";
        $result = $db->query($sql);

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result, 'hms', 'HMS_Floor::delete_floors');
        }
        
        return $floor_result;
        
    }

    function select_floor_for_edit()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        return $form->select_floor_for_edit();
    }

    function select_residence_hall_for_edit_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        return $form->select_residence_hall_for_edit_floor();
    }

    function edit_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        return $form->edit_floor();
    }

    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'edit_floor':
                return HMS_Floor::edit_floor();
                break;
            case 'save_floor':
                $floor = &new HMS_Floor;
                $floor->set_variables();
                return HMS_Floor::save_floor_object($floor);
                break;
            case 'select_hall_for_edit_floor':
                return HMS_Floor::select_residence_hall_for_edit_floor();
                break;
            case 'select_floor_for_edit':
                return HMS_Floor::select_floor_for_edit();
                break;
            default:
                return "you're using a floor function.";
                break;
        }
    }
};
?>
