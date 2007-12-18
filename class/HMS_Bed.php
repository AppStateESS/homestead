<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Bed extends HMS_Item {
    var $bedroom_id       = 0;
    var $bed_letter       = 0;
    var $banner_id        = null;
    var $phone_number     = null;
    var $_curr_assignment = null;
    /**
     * Previous assignments (ie deleted) will be here after loading
     * the current assignment
     * @var array
     */
    var $_prev_assignment = array();

    /**
     * Holds the parent bedroom object of this bed.
     */
    var $_bedroom;

    function HMS_Bed($id = 0)
    {
        $this->construct($id, 'hms_bed');
    }

    function copy($to_term, $bedroom_id, $assignments)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_beds, making a copy of this bed<br>";
        
        $new_bed = clone($this);
        $new_bed->reset();
        $new_bed->term    = $to_term;
        $new_bed->bedroom_id = $bedroom_id;
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

    function loadBedroom()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bedroom.php');
        $result = new HMS_Bedroom($this->bedroom_id);
        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        $this->_bedroom = & $result;
        return true;
    }

    function get_parent()
    {
        if(!$this->loadBedroom()){
            return false;
        }

        return $this->_bedroom;
    }

    function get_number_of_assignees()
    {
        $this->loadAssignment();
        return (bool)$this->_curr_assignment ? 1 : 0;
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

    function get_all_empty_beds($init = FALSE)
    {
        /*$db = new PHPWS_DB('hms_bed');
        $db->addJoin('left', 'hms_bed', 'hms_bedroom', 'bedroom_id', 'id');
        $db->addJoin('left', 'hms_bedroom', 'hms_room', 'room_id', 'id');
        $db->addJoin('left', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('left', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');
        $db->addJoin('left outer', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addWhere('hms_bed.deleted', 0);
        $db->addWhere('hms_bedroom.deleted', 0);
        $db->addWhere('hms_room.deleted', 0);
        $db->addWhere('hms_room.is_online', 1);
        $db->addWhere('hms_room.is_reserved', 0);
        $db->addWhere('hms_room.is_medical', 0);
        $db->addWhere('hms_room.ra_room', 0);
        $db->addWhere('hms_room.private_room', 0);
        $db->addWhere('hms_floor.deleted', 0);
        $db->addWhere('hms_floor.is_online', 1);
        $db->addWhere('hms_residence_hall.deleted', 0);
        $db->addWhere('hms_residence_hall.is_online', 1);
        $db->addWhere('hms_assignment.asu_username', null);
        $db->addColumn('hms_bed.*');
        $db->addColumn('hms_room.gender_type');
        $results = $db->select();*/

        $sql = "
            SELECT
                hms_bed.*,
                hms_room.gender_type,
                hms_residence_hall.banner_building_code
            FROM 
                hms_bed
                JOIN hms_bedroom        ON hms_bed.bedroom_id          = hms_bedroom.id
                JOIN hms_room           ON hms_bedroom.room_id         = hms_room.id
                JOIN hms_floor          ON hms_room.floor_id           = hms_floor.id
                JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                LEFT OUTER JOIN (
                    SELECT * FROM hms_assignment WHERE deleted = 0
                ) AS a_prime ON hms_bed.id = a_prime.bed_id
            WHERE
                hms_bed.deleted              = 0 AND
                hms_bedroom.deleted          = 0 AND
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
