<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Bed extends HMS_Item {
    
    var $room_id            = 0;
    var $bed_letter         = null;
    var $banner_id          = null;
    var $phone_number       = null;
    var $bedroom_label      = null;
    var $ra_bed             = null;
    var $_curr_assignment   = null;

    /**
     * Previous assignments (ie deleted) will be here after loading
     * the current assignment
     * @var array
     */
    var $_prev_assignment = array();

    /**
     * Holds the parent room object of this bed.
     */
    var $_room;

    function HMS_Bed($id = 0)
    {
        $this->construct($id, 'hms_bed');
        //test($this);
    }

    function copy($to_term, $room_id, $assignments)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_beds, making a copy of this bed<br>";
        
        $new_bed = clone($this);
        $new_bed->reset();
        $new_bed->term    = $to_term;
        $new_bed->room_id = $room_id;
        if (!$new_bed->save()) {
            // There was an error saving the new room
            // Error will be logged.
            //echo "error saving a copy of this bed<br>";
            return false;
        }

        if ($assignments) {
            //echo "loading assignments for this bed<br>";
            $result = $this->loadAssignment();
            if(PEAR::isError($result)){
                //echo "error loading assignments<br>";
                test($result);
                return false;
            }
            
            test($this->_curr_assignment);
            if (isset($this->_curr_assignment)) {
                return $this->_curr_assignment->copy($to_term, $new_bed->id);
            }
        }
    
        //echo "bed copied<br>";
        
        return true;
    }

    function get_row_tags()
    {
        $tpl = $this->item_tags();

        $tpl['BED_LETTER']   = $this->bed_letter;
        $tpl['BANNER_ID']    = $this->banner_id;
        $tpl['PHONE_NUMBER'] = $this->phone_number;

        return $tpl;
    }


    function loadAssignment()
    {
        $assignment_found = false;
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('bed_id', $this->id);
        $db->loadClass('hms', 'HMS_Assignment.php');
        $result = $db->getObjects('HMS_Assignment');

        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            foreach ($result as $ass) {
                if ($ass->deleted == 1) {
                    $this->_prev_assignment[] = $ass;
                } else {
                    if ($assignment_found) {
                        PHPWS_Error::log(HMS_MULTIPLE_ASSIGNMENTS, 'hms', 'HMS_Bed::loadAssignment', 
                                         sprintf('A=%s,B=%s', $ass->id, $this->id));
                    } else {
                        $this->_curr_assignment = $ass;
                        $assignment_found = true;
                    }
                }
            }
        }
    }

    function loadRoom()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $result = new HMS_Room($this->Room_id);
        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        $this->_room = & $result;
        return true;
    }

    function get_parent()
    {
        if(!$this->loadRoom()){
            return false;
        }

        return $this->_room;
    }

    function get_number_of_assignees()
    {
        $this->loadAssignment();
        return (bool)$this->_curr_assignment ? 1 : 0;
    }

    function get_assignee()
    {
        if(!$this->loadAssignment()){
            return false;
        }

        return new HMS_Student($this->_curr_assignment->asu_username);
    }
    
    function save()
    {
        $this->stamp();

        $db = new PHPWS_DB('hms_bed');
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    function has_vacancy()
    {
        if($this->get_number_of_assignees() == 0){
            return TRUE;
        }

        return FALSE;
    }

    /******************
     * Static Methods *
     ******************/
     
    function get_all_empty_beds($init = FALSE)
    {
        $sql = "
            SELECT
                hms_bed.*,
                hms_room.gender_type,
                hms_residence_hall.banner_building_code
            FROM 
                hms_bed
                JOIN hms_room           ON hms_bed.room_id         = hms_room.id
                JOIN hms_floor          ON hms_room.floor_id           = hms_floor.id
                JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                LEFT OUTER JOIN (
                    SELECT * FROM hms_assignment WHERE deleted = 0
                ) AS a_prime ON hms_bed.id = a_prime.bed_id
            WHERE
                hms_bed.deleted              = 0 AND
                hms_room.deleted             = 0 AND
                hms_room.is_online           = 1 AND
                hms_room.is_reserved         = 0 AND
                hms_room.is_medical          = 0 AND
                hms_room.ra_room             = 0 AND
                hms_room.private_room        = 0 AND
                hms_floor.deleted            = 0 AND
                hms_floor.is_online          = 1 AND
                hms_residence_hall.deleted   = 0 AND
                hms_residence_hall.is_online = 1 AND
                a_prime.asu_username IS NULL
            ";

        $results = PHPWS_DB::getAll($sql);

        $beds = array();
        $beds['0'] = array();
        $beds['1'] = array();
        foreach($results as $result) {
            $bed = new HMS_Bed();
            $bed->id = $result['id'];
            $bed->banner_id = $result['banner_id'];

            // Is there a better way to do this?  Hell yes.  Unfortunately the only
            // one I can think of is WAY THE HELL less efficient.  We MUST work on
            // our object-relational mapping sometime.  TODO!!!
            $hack['bed'] = $bed;
            $hack['hall'] = $result['banner_building_code'];

            $beds[$result['gender_type']][] = $hack;
        }

        return $beds;
    }
        
}

?>
