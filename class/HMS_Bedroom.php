<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Bedroom extends HMS_Item {
    var $room_id        = 0;
    var $is_online      = false;
    var $bedroom_letter = null;

    /**
     * @var array
     */
    var $_beds          = array();

    /**
     * Holds the parent room object
     */
    var $_room          = null;

    function HMS_Bedroom($id = 0)
    {
        $this->construct($id, 'hms_bedroom');
    }

    /**
     * Pulls all beds associated with this bedroom and stores them in 
     * the _beds variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     */
    function loadBeds($deleted=0)
    {
        $db = new PHPWS_DB('hms_bed');
        $db->addWhere('bedroom_id', $this->id);

        switch ($deleted) {
        case -1:
            $db->addWhere('deleted', 1);
            break;

        case 0:
            $db->addWhere('deleted', 0);
            break;
        }

        $db->loadClass('hms', 'HMS_Bed.php');
        $result = $db->getObjects('HMS_Bed');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_beds = & $result;
            return true;
        }
    }

    /**
     * Loads the parent room object of this bedroom
     */
    function loadRoom()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $result = new HMS_Room($this->room_id);
        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        $this->_room = & $result;
        return true;
    }

    /**
     * Returns the parent room object of this bedroom
     */
    function get_parent()
    {
        if(!$this->loadRoom()){
            return false;
        }

        return $this->_room;
    }

    function get_row_tags()
    {
        $tpl = $this->item_tags();

        $tpl['ROOM_NUMBER']  = $this->room_number;
        $tpl['GENDER_TYPE']  = HMS::formatGender($this->gender_type);
        $tpl['RA_ROOM']      = $this->ra_room      ? 'Yes' : 'No';
        $tpl['PRIVATE_ROOM'] = $this->private_room ? 'Yes' : 'No';
        $tpl['IS_LOBBY']     = $this->is_lobby     ? 'Yes' : 'No';
        $tpl['IS_MEDICAL']   = $this->is_medical   ? 'Yes' : 'No';
        $tpl['IS_RESERVED']  = $this->is_reserved  ? 'Yes' : 'No';
        $tpl['IS_ONLINE']    = $this->is_online    ? 'Yes' : 'No';

        return $tpl;
    }

    function copy($to_term, $room_id, $assignments)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_bedroom cloning this bedroom with id: $this->id <br>";
    
        $new_bedroom = clone($this);
        $new_bedroom->reset();
        $new_bedroom->term    = $to_term;
        $new_bedroom->room_id = $room_id;
        if (!$new_bedroom->save()) {
            // There was an error saving the new room
            // Error will be logged.
            //echo "error making a copy of this bedroom<br>";
            return false;
        }

        if(!$this->loadBeds()){
            //echo "error loading beds<br>";
            return false;
        }

        
        if (!empty($this->_beds)) {
            foreach ($this->_beds as $bed) {
                $result = $bed->copy($to_term, $new_bedroom->id, $assignments);
                if(!$result){
                    //echo "error copying beds<br>";
                    //test($result);
                    return false;
                }
            }
        }

        return true;
    }

    function save()
    {
        $this->stamp();

        $db = new PHPWS_DB('hms_bedroom');
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    function get_number_of_beds()
    {
        $db = &new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_bed', 'id', 'bedroom_id');

        $db->addWhere('hms_bed.deleted', 0);
        $db->addWhere('hms_bedroom.deleted', 0);

        $db->addWhere('hms_bedroom.id', $this->id);

        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_bedroom', 'bedroom_id', 'id');
        
        $db->addWhere('hms_assignment.deleted', 0);
        $db->addWhere('hms_bed.deleted', 0);
        $db->addWhere('hms_bedroom.deleted', 0);
        
        $db->addWhere('hms_bedroom.id', $this->id);

        $result = $db->select('count');

        #test($result, 1);

        if(PHPWS_Error::logIfError($result)){
            die("db error");
            
            return false;
        }

        return $result;
    }

    function create_child_objects($beds_per_bedroom)
    {
        for ($i = 0; $i < $beds_per_bedroom; $i++) {
            $bed = new HMS_Bed;

            $bed->bedroom_id  = $this->id;
            $bed->term        = $this->term;
            PHPWS_Error::logIfError($bed->save());
        }
    }

    /**
     * Returns TRUE if the hall has vacant beds, false otherwise
     */
    function has_vacancy()
    {
        if($this->get_number_of_assignees() < $this->get_number_of_beds()){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Returns an array of bed objects in this bedroom that have vacancies
     */
    function get_beds_with_vacancies()
    {
        if(!$this->loadBeds()) {
            return FALSE;
        }

        $vacant_beds = array();

        foreach($this->_beds as $bed){
            if($bed->has_vacancy()){
                $vacant_beds[] = $bed;
            }
        }

        return $vacant_beds;
    }
}

?>
