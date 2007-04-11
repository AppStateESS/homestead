<?php

class HMS_Bed
{

    var $id;
    var $bedroom_id;
    var $occupant;
    var $bed_letter;
    var $banner_id;
    /*
    var $added_by;
    var $added_on;
    var $deleted_by;
    var $deleted_on;
    */
    var $deleted;

    function get_id()
    {
        return $this->id;
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_bedroom_id()
    {
        return $this->bedroom_id;
    }

    function set_bedroom_id($bedroom_id)
    {
        $this->bedroom_id = $bedroom_id;
    }

    function get_occupant()
    {
        return $this->occupant;
    }

    function set_occupant($occupant)
    {
        $this->occupant = $occupant;
    }

    function get_bed_letter()
    {
        return $this->bed_letter;
    }

    function set_bed_letter($bed_letter)
    {
        $this->bed_letter = $bed_letter;
    }

    function get_banner_id()
    {
        return $this->banner_id;
    }

    function set_banner_id($banner_id)
    {
        $this->banner_id = $banner_id;
    }

    function set_deleted($deleted = 0)
    {
        $this->deleted = $deleted;
    }

    function get_deleted()
    {
        return $this->deleted;
    }

    function save_bed($object = NULL)
    {
        $db = &new PHPWS_DB('hms_beds');
        if($object == NULL) {

        } else {
            $success = $db->saveObject($object);
            if(PEAR::isError($success)) {
                test($success);
            }
            return $success;
        }
    }

    function delete_beds_by_building($building_id)
    {
        $sql = "UPDATE hms_beds ";
        $sql .= "SET deleted = 1 ";
        $sql .= "WHERE bedroom_id = hms_bedrooms.id ";
        $sql .= "AND hms_bedrooms.room_id = hms_room.id ";
        $sql .= "AND hms_room.building_id = $building_id;";

        $db = new PHPWS_DB;
        $result = $db->query($sql);
        return $result;
    }

    function get_room_id($id)
    {
        $db = &new PHPWS_DB('hms_room');
        $db->addColumn('id');
        $db->addWhere('hms_beds.id', $id);
        $db->addWhere('hms_beds.bedroom_id', 'hms_bedrooms.id');
        $db->addWhere('hms_bedrooms.room_id', 'hms_room.id');
        $room_id = $db->select('one');
        return $room_id;
    }

}
?>
