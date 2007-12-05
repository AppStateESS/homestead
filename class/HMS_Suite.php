<?php

class HMS_Suite extends HMS_Item {
    var $floor_id = 0;
    var $_rooms   = array();
    var $_floor   = null;

    function HMS_Suite($id = 0)
    {
        $this->construct($id, 'hms_suite');
    }

    function save()
    {
        $db = new PHPWS_DB('hms_suite');

        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    function copy($to_term, $floor_id, $assignments=false)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_suite, copying suite id $this->id <br>";

        // Create a clone of the current suite object
        // Set id to 0, set term, and save
        $new_suite = clone($this);
        $new_suite->reset();
        $new_suite->term = $to_term;
        $new_suite->floor_id = $floor_id;

        if(!$new_suite->save()) {
            // There was an error saving the new suite
            echo "error saving the new suite<br>";
            test($new_suite->save(),1);
            return false;
        }

        if(empty($this->_rooms)) {
            if(!$this->loadRooms()) {
                // There was an error loading the rooms
                echo "Error loading the rooms<br>";
                return false;
            }
        }

        if(!empty($this->_rooms)) {
            foreach ($this->_rooms as $room) {
                //echo "copying room id: $room->id<br>";
                $result = $room->copy($to_term, $floor_id, $new_suite->id, $assignments);
                if(!$result){
                    echo "error copying rooms inside suites<br>";
                    return false;
                }
            }
        }else{
            //echo "rooms empty!<br>";
        }

        return true;
    }

    /**
     * Pulls all the rooms associated with this floor and stores
     * them in the _room variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     */
    function loadRooms($deleted = 0)
    {
        $db = new PHPWS_DB('hms_room');
        $db->addWhere('floor_id', $this->floor_id);
        $db->addWhere('suite_id', $this->id);

        switch ($deleted) {
            case -1:
                $db->addWhere('deleted', 1);
                break;
            case 0:
                $db->addWhere('deleted', 0);
                break;
        }

        $db->loadClass('hms', 'HMS_Room.php');
        $result = $db->getObjects('HMS_Room');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_rooms = & $result;
            return true;
        }
    }

    /**
     * Loads the parent floor object of this room
     */
    function loadFloor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        $result = new HMS_Floor($this->floor_id);
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }
        $this->_floor = & $result;
        return true;
    }

    function get_rooms()
    {
        if(!$this->loadRooms()){
            return false;
        }

        return $this->_rooms;
    }
}

?>
