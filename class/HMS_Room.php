<?php

/**
 * HMS Room class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Room extends HMS_Item
{

    var $floor_id              = 0;
    var $room_number           = 0;
    
    var $gender_type           = 0;
    var $ra_room               = false;
    var $private_room          = false;
    var $is_lobby              = false;
    var $learning_community_id = 0;
    var $pricing_tier          = 0;
    var $is_medical            = false;
    var $is_reserved           = false;
    var $is_online             = false;
    var $suite_id              = NULL;


    /**
     * Listing of bedrooms associated with this room
     * @var array
     */
    var $_bedrooms             = null;

    /**
     * Parent HMS_Floor object of this room
     * @var object
     */
    var $_floor                = null;

    /**
     * Constructor
     */
    function HMS_Room($id = 0)
    {
        $this->construct($id, 'hms_room');
    }

    /********************
     * Instance Methods *
     *******************/

    /*
     * Saves a new or updated floor hall object
     * New room ids are inserted into the id variable.
     * Save errors are logged.
     *
     * @return bool True is successful, false otherwise.
     */
    function save()
    {
        $this->stamp();
        $db = new PHPWS_DB('hms_room');
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    /*
     * Copies this room object to a new term, then calls copy on all
     * 'this' room's bedrooms.
     *
     * Setting $assignments to TRUE causes the copy function to copy
     * the c<urrent assignments as well as the hall structure.
     * 
     * @return bool False if unsuccessful.
     */
    function copy($to_term, $floor_id, $suite_id=NULL, $assignments = FALSE)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_room, cloning room with id: $this->id <br>";

        // Create clone of current room object
        // Set id to 0, set term, and save
        $new_room = clone($this);
        $new_room->reset();
        $new_room->term     = $to_term;
        $new_room->floor_id = $floor_id;
        $new_room->suite_id = $suite_id;

        if (!$new_room->save()) {
            // There was an error saving the new room
            // Error will be logged.
            //echo "could not save a copy of this room<br>";
            return false;
        }
       
        // Save successful, create new bedrooms

        // Load all bedrooms for this room
        if (empty($this->_bedrooms)) {
            if (!$this->loadBedrooms()) {
                // There was an error loading the bedrooms
                // Delete new room?
                // $new_room->delete();
                //echo "error loading bedrooms<br>";
                return false;
            }
        }

        /**
         * Bedrooms exist. Start makin copies.
         * Further copying is needed at the bedroom level.
         * The bedroom class will work much like this class. If assignments is true then
         * bedrooms will load beds and assignments, foreach the bed list, and
         * $bed->copy($to_term, $assignments) once again with username copied, etc.
         * 
         **/ 

        if (!empty($this->_bedrooms)) {
            foreach ($this->_bedrooms as $bedroom) {
                $result = $bedroom->copy($to_term, $new_room->id, $assignments);
                if(!$result){
                    //echo "error copying bedroom";
                    //test($result);
                    return false;
                }
                // What if bad result?
            }
        }

        return true;
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

    /**
     * Pulls all bedrooms associated with this room and stores them in 
     * the _bedrooms variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     *
     */
    function loadBedrooms($deleted=0)
    {
        $db = new PHPWS_DB('hms_bedroom');
        $db->addWhere('room_id', $this->id);

        switch ($deleted) {
        case -1:
            $db->addWhere('deleted', 1);
            break;

        case 0:
            $db->addWhere('deleted', 0);
            break;
        }

        $db->loadClass('hms', 'HMS_Bedroom.php');
        $result = $db->getObjects('HMS_Bedroom');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_bedrooms = & $result;
            return true;
        }
    }

    /*
     * Creates the bedrooms, and beds for a new room
     * Initial values for bedrooms should be set in the declaration.
     * Assuming gender_type is carried over.
     * added and updated variables need to be set in the bedroom save function.
     */
    function create_child_objects($bedrooms_per_room, $beds_per_bedroom)
    {
        for ($i = 0; $i < $bedroooms_per_room; $i++) {
            $bedroom = new HMS_Bedroom;

            $bedroom->room_id     = $this->id;
            $bedroom->term        = $this->term;
            $bedroom->gender_type = $this->gender_type;

            if ($bedroom->save()) {
                $bedroom->create_child_objects($beds_per_bedroom);
            } else {
                // Decide on bad result.
            }
        }
    }

    /*
     * Returns TRUE or FALSE. The gender of a room can only be changed
     * to the target gender if all bedrooms can be changed to the
     * target gender.
     * 
     * Addditionally, the room's gender can only be changed if the
     * gender will be consistent with the gender of the floor of which
     * this room is a part.
     * 
     * This function checks to make sure all bedrooms can be changed,
     * those bedrooms in tern check all their beds, and so on.
     * 
     *
     * Also assuming a coed change will ALWAYS be true regardless
     *
     * @param int  target_gender
     * @param bool ignore_upper In the case that we're attempting to change 
     *                          the gender of just 'this' room, set $ignore_upper
     *                          to TRUE to avoid checking the parent hall's gender.
     * @return bool
     */
    function can_change_gender($target_gender, $ignore_upper = FALSE)
    {
        // If the target gender is coed, the gender of the bedrooms
        // is irrelevant so we skip the check in that case
        if ($target_gender != COED) {
            $this->loadBedrooms();
            if ($this->_bedrooms) {
                foreach ($this->_bedrooms as $br) {
                    // If the bedroom gender type is not coed and the bedroom gt
                    // does not equal the target gender, we return false
                    if ($br->gender_type != COED && $br->gender_type != $target_gender) {
                        return false;
                    }
                }
            }
        }

        // If we aren't ignoring the floor, load it and compare
        if (!$ignore_upper) {
            if (!$this->loadFloor()) {
                // an error occurred loading the floor, check logs
                return false;
            }
            // If the floor is not coed and the gt is not the target, return false
            if ($this->_floor->gender_type != COED && $this->_floor->gender_type != $target_gender) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return int The number of bedrooms within the current room
     */
    function get_number_of_bedrooms()
    {
        $db = &new PHPWS_DB('hms_bedroom');

        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room', 'room_id', 'id');
 
        $db->addWhere('hms_bedroom.deleted', 0);
        $db->addWhere('hms_room.deleted', 0);

        $db->addWhere('hms_room.id', $this->id);
        
        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of beds within the current room
     */
    function get_number_of_beds()
    {
        $db = &new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_bedroom', 'bedroom_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room', 'room_id', 'id');
 
        $db->addWhere('hms_bed.deleted',     0);
        $db->addWhere('hms_bedroom.deleted', 0);
        $db->addWhere('hms_room.deleted',    0);

        $db->addWhere('hms_room.id', $this->id);
        
        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;

    }

    /*
     * Returns the number of students assigned to the current room
     * Each bedroom should have a duplicate function to count its beds and
     * assignees.
     */
    function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id'  );
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_bedroom', 'bedroom_id', 'id' );
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room', 'room_id', 'id'   );
 
        $db->addWhere('hms_assignment.deleted', 0);
        $db->addWhere('hms_bed.deleted',        0);
        $db->addWhere('hms_bedroom.deleted',    0);
        $db->addWhere('hms_room.deleted',       0);

        $db->addWhere('hms_room.id', $this->id);
        
        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;

    }
 
    /*
     * Returns the parent floor object of this room
     */
    function get_parent()
    {
        $this->loadFloor();
        return $this->_floor;
    }

    /*
     * Returns an array of the bedrooms within the current room
     */
    function get_bedrooms()
    {
        if (!$this->loadBedrooms()) {
            return false;
        }

        return $this->_bedrooms;
    }

    /*
     * Returns an array of beds within the current room
     * Bedroom class needs a get_beds function.
     */
    function get_beds()
    {
        if (!$this->loadBedrooms()) {
            return false;
        }

        $all_beds = array();

        foreach ($this->_bedrooms as $br) {
            $beds = $br->get_beds();
            $all_beds = array_merge($all_beds, $beds);
        }
        return $all_beds;
    }

    /*
     * Returns an array of HMS_Student objects which are currently
     * assigned to 'this' room.
     * Bedroom class needs a get_assignees function that collects results
     * from a get_assignees function in HMS_Beds
     */
    function get_assignees()
    {
        if (!$this->loadBedrooms()) {
            return false;
        }

        $all_assignees = array();

        foreach ($this->_bedrooms as $br) {
            $assignees = $br->get_assignees();
            $all_assignees = array_merge($all_assignees, $assignees);
        }
        return $all_assignees;
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
     * Returns an array of bedroom objects in this hall that havae vacancies
     */
    function get_bedrooms_with_vacancies()
    {
        if(!$this->loadBedrooms()) {
            return FALSE;
        }

        #test($this->_bedrooms, 1);

        $vacant_bedrooms = array();

        foreach($this->_bedrooms as $bedroom){
            if($bedroom->has_vacancy()){
                $vacant_bedrooms[] = $bedroom;
            }
        }

        return $vacant_bedrooms;
    }

    /******************
     * Static Methods *
     *****************/

    function main()
    {

    }

    function room_pager()
    {

    }

    function get_row_tags()
    {
        $tpl = $this->item_tags();

        $tpl['TERM']         = HMS_Term::term_to_text($this->term, true);
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


}

?>
