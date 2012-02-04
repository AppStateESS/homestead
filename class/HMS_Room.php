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

    public $term;
    public $floor_id               = 0;
    public $room_number            = 0;

    // Gender
    public $gender_type            = 0;
    public $default_gender         = 0;

    // Reservations
    public $reserved               = false;
    public $offline                = false;
    public $ra                     = false;
    public $private                = false;
    public $overflow               = false;
    public $parlor                 = false;

    // Medical flags
    public $ada                    = false;
    public $hearing_impaired       = false;
    public $bath_en_suite          = false;

    /****************************************************
     * Following fields are not present in the database *
    ****************************************************/
    public $banner_building_code;

    /**
     * Listing of beds associated with this room
     * @var array
     */
    public $_beds                  = null;

    /**
     * Parent HMS_Floor object of this room
     * @public object
     */
    public $_floor                 = null;

    /* Hack for the javascript DO NOT TOUCH */
    public $message = '';
    public $value   = false;

    /**
     * Constructor
     */
    public function HMS_Room($id = 0)
    {
        $this->construct($id, 'hms_room');
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_room');
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
    public function save()
    {
        $this->stamp();
        $db = new PHPWS_DB('hms_room');
        $result = $db->saveObject($this);

        if(!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        return true;
    }

    public function delete()
    {
        if(is_null($this->id) || !isset($this->id)){
            throw new InvalidArgumentException('Invalid room id.');
        }

        $db = new PHPWS_DB('hms_room');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if(!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    /*
     * Copies this room object to a new term, then calls copy on all
    * 'this' room's beds.
    *
    * Setting $assignments to TRUE causes the copy public function to copy
    * the c<urrent assignments as well as the hall structure.
    *
    * @return bool False if unsuccessful.
    */
    public function copy($to_term, $floor_id, $suite_id=NULL, $assignments = FALSE)
    {
        if(!$this->id) {
            return false;
        }

        //echo "in hms_room, cloning room with id: $this->id <br>";

        // Create clone of current room object
        // Set id to 0, set term, and save
        $new_room = clone($this);
        $new_room->reset();
        $new_room->term     = $to_term;
        $new_room->floor_id = $floor_id;

        //Set the default gender to the floor's gender if the floor isn't
        //coed and the genders don't match.
        $new_room->loadFloor();
        if($new_room->_floor->gender_type != COED
        && $new_room->default_gender != $new_room->_floor->gender_type)
        {
            $new_room->default_gender = $new_room->_floor->gender_type;
        }

        //If we're not coyping assignments, then set the gender of the room to the room's default gender
        // Resetting the gender when copying the assignemnt can result in students assigned to rooms of a different gender
        // Because this manipulates the database directly, the genders don't get checked
        if(!$assignments){
            $new_room->gender_type = $new_room->default_gender;
        }
        else if($assignments) {
            $new_room->gender_type = $this->gender_type;
        }

        try{
            $new_room->save();
        }catch(Exception $e){
            throw $e;
        }

        // Save successful, create new beds

        // Load all beds for this room
        if(empty($this->_beds)) {
            try{
                $this->loadBeds();
            }catch(Exception $e){
                throw $e;
            }
        }

        /**
         * Beds exist. Start making copies.
         * Further copying is needed at the bed level.
         * The bed class will work much like this class. If assignments is true then
         * beds will load beds and assignments, foreach the bed list, and
         * $bed->copy($to_term, $assignments) once again with username copied, etc.
         *
         **/

        if(!empty($this->_beds)) {
            foreach ($this->_beds as $bed) {
                try{
                    $bed->copy($to_term, $new_room->id, $assignments);
                }catch(Exception $e){
                    throw $e;
                }
            }
        }
    }

    public function getLink($prependText = NULL)
    {
        $roomCmd = CommandFactory::getCommand('EditRoomView');
        $roomCmd->setRoomId($this->id);
        if(!is_null($prependText)){
            $text = $prependText . ' ' . $this->room_number;
        }else{
            $text = $this->room_number;
        }
        return $roomCmd->getLink($text);
    }

    /**
     * Loads the parent floor object of this room
     */
    public function loadFloor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        $result = new HMS_Floor($this->floor_id);
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        $this->_floor = & $result;
        return true;
    }

    /**
     * Pulls all beds associated with this room and stores them in
     * the _beds variable.
     *
     */
    public function loadBeds()
    {
        $db = new PHPWS_DB('hms_bed');
        $db->addWhere('room_id', $this->id);
        $db->addOrder('bedroom_label', 'ASC');
        $db->addOrder('bed_letter', 'ASC');

        $db->loadClass('hms', 'HMS_Bed.php');
        $result = $db->getObjects('HMS_Bed');
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        } else {
            $this->_beds = & $result;
            return true;
        }
    }

    /*
     * Creates beds for a new room
    * Initial values for beds should be set in the declaration.
    * Assuming gender_type is carried over.
    * added and updated variables need to be set in the bed save public function.
    */
    public function create_child_objects($beds_per_room)
    {
        for ($i = 0; $i < $bedroooms_per_room; $i++) {
            $bed = new HMS_Bed;

            $bed->room_id     = $this->id;
            $bed->term        = $this->term;
            $bed->gender_type = $this->gender_type;
        }
    }

    /*
     * Returns TRUE or FALSE.
    *
    * This public function uses the following logic:
    *
    * When ignore_upper = TRUE (a floor is trying to see if this room could be changed to a target gender):
    *      If the target gender is COED: then we can always return true (even though a room can never be COED).
    *      If the target gender is MALE: then return false if the room is female AND not empty
    *      If the target gender is FEMALE: then return false if the room is male AND not empty
    *      If all those checks pass, then return true
    *
    * When ignore_upper = FALSE (we're trying to change *this* room to a target gender):
    *      If the target gender is COED: always return false (rooms can't be COED)
    *      If the target gender is MALE: return false if the floor is female
    *      If the target gender is FEMALE: return false if the floor is male
    *
    * @param int  target_gender
    * @param bool ignore_upper In the case that we're attempting to change
    *                          the gender of just 'this' room, set $ignore_upper
    *                          to TRUE to avoid checking the parent hall's gender.
    * @return bool
    */
    public function can_change_gender($target_gender, $ignore_upper = FALSE)
    {
        // Ignore upper is true, we're trying to change a hall/floor
        if($ignore_upper){
            // If ignore upper is true and the target gender coed, then we
            // can always return true.
            if($target_gender == COED){
                return true;
            }

            // If the target gender is not the same, and someone is assigned
            // here, then the gender can't be changed (i.e. return false)
            if(($target_gender != $this->gender_type) && ($this->get_number_of_assignees() != 0)){
                return false;
            }

            return true;
        }else{
            // Ignore upper is FALSE, load the floor and compare

            // If the target gender is not the same, and someone is assigned
            // here, then the gender can't be changed (i.e. return false)
            if(($target_gender != $this->gender_type) && ($this->get_number_of_assignees() != 0)){
                return false;
            }

            if(!$this->loadFloor()) {
                // an error occurred loading the floor, check logs
                return false;
            }

            // If the floor is not coed and the gt is not the target, return false
            if($this->_floor->gender_type != COED &&
            $this->_floor->gender_type != $target_gender
            ) {
                return false;
            }

            return true;
        }
    }

    /*
     * Returns the number of beds within the current room
    */
    public function get_number_of_beds()
    {
        $db = new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');

        $db->addWhere('hms_room.id', $this->id);

        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the number of students assigned to the current room
    */
    public function get_number_of_assignees()
    {
        $db = new PHPWS_DB('hms_assignment');

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id'  );
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id' );

        $db->addWhere('hms_room.id', $this->id);


        $result = $db->select('count');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /*
     * Returns the parent floor object of this room
    */
    public function get_parent()
    {
        $this->loadFloor();
        return $this->_floor;
    }

    /*
     * Returns an array of beds within the current room
    */
    public function get_beds()
    {
        if(!$this->loadBeds()) {
            return false;
        }

        return $this->_beds;
    }

    /**
     * Returns an associative array where the keys are bed ID's
     * and the values are the bed letter.
     */
    public function get_beds_array()
    {
        if(!$this->loadBeds()) {
            return FALSE;
        }

        $beds = array();

        foreach($this->_beds as $bed){
            $beds[$bed->id] = $bed->bed_letter;
        }

        return $beds;
    }

    /*
     * Returns an array of HMS_Student objects which are currently
    * assigned to 'this' room.
    */
    public function get_assignees()
    {
        if(!$this->loadBeds()) {
            return false;
        }

        $assignees = array();

        foreach ($this->_beds as $bed) {
            $assignee = $bed->get_assignee();
            if(!is_null($assignee)){
                $assignees[] = $assignee;
            }
        }

        return $assignees;
    }

    /**
     * Returns TRUE if the hall has vacant beds, false otherwise
     */
    public function has_vacancy()
    {
        $num_assigned = $this->get_number_of_assignees();

        // If this is a private room, then this room is full if one person is assigned
        if($this->isPrivate() && $num_assigned >= 1){
            return FALSE;
        }

        if($num_assigned < $this->get_number_of_beds()){
            //make sure the rooms aren't all reserved
            $this->loadBeds();
            $vacant = FALSE;
            foreach($this->_beds as $bed){
                $bed->loadAssignment();
                if(is_null($bed->_curr_assignment) && $bed->room_change_reserved == 0){
                    $vacant = TRUE;
                }
            }
            return $vacant;
        }

        return FALSE;
    }

    /**
     * Returns an array of bed objects in this room that have vacancies
     */
    public function getBedsWithVacancies()
    {
        if(!$this->loadBeds()) {
            return FALSE;
        }

        $vacant_beds = array();

        // Search for vacant beds in this room's set of beds, only if this room
        // has a vacancy according to 'has_vacancy()'. This accounts for private rooms.
        if($this->has_vacancy()){

            foreach($this->_beds as $bed){
                if($bed->has_vacancy()){
                    $vacant_beds[] = $bed;
                }
            }
        }

        return $vacant_beds;
    }

    public function where_am_i($link = FALSE)
    {
        $floor = $this->get_parent();
        $building = $floor->get_parent();

        $text = $building->hall_name . ' Room ' . $this->room_number;

        if($link){
            return PHPWS_Text::secureLink($text, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$this->id));
        }else{
            return $text;
        }
    }

    public function count_avail_lottery_beds()
    {
        $now = mktime();

        // Count the number of beds which are free in this room
        $query =   "SELECT DISTINCT COUNT(hms_bed.id) FROM hms_bed
                    JOIN hms_room ON hms_bed.room_id = hms_room.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                    AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_room.id = {$this->id}
                    AND hms_room.reserved = 0
                    AND hms_room.offline = 0
                    AND hms_room.private = 0
                    AND hms_room.ra = 0
                    AND hms_room.overflow = 0";

        $avail_rooms = PHPWS_DB::getOne($query);
        if(PHPWS_Error::logIfError($avail_rooms)){
            throw new DatabaseException($result->toString());
        }

        return $avail_rooms;
    }

    public function get_row_tags()
    {
        //$tpl = $this->item_tags();
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl['ID']             = $this->id;
        $tpl['ROOM_NUMBER']    = $this->getLink();
        $tpl['GENDER_TYPE']    = HMS_Util::formatGender($this->gender_type);
        $tpl['DEFAULT_GENDER'] = HMS_Util::formatGender($this->default_gender);
        $tpl['RA_ROOM']        = $this->ra        ? 'Yes' : 'No';
        $tpl['PRIVATE_ROOM']   = $this->private   ? 'Yes' : 'No';
        $tpl['IS_OVERFLOW']    = $this->overflow  ? 'Yes' : 'No';
        $tpl['IS_RESERVED']    = $this->reserved  ? 'Yes' : 'No';
        $tpl['OFFLINE']        = $this->offline   ? 'Yes' : 'No';

        if(Current_User::allow('hms','room_structure') && $this->get_number_of_assignees() == 0) {
            $deleteRoomCmd = CommandFactory::getCommand('DeleteRoom');
            $deleteRoomCmd->setRoomId($this->id);
            $deleteRoomCmd->setFloorId($this->floor_id);

            $confirm             = array();
            $confirm['QUESTION'] = 'Are you sure want to delete room ' .  $this->room_number . '?';
            $confirm['ADDRESS']  = $deleteRoomCmd->getURI();
            $confirm['LINK']     = 'Delete';
            $tpl['DELETE']       = Layout::getJavascript('confirm', $confirm);
        }

        return $tpl;
    }

    // TODO: move this
    public function get_row_edit(){
        javascript('jquery');
        $tpl = array();
        $tpl['ID']           = $this->id;
        $tpl['ROOM_NUMBER']  = PHPWS_Text::secureLink($this->room_number, 'hms', array('action'=>'EditRoomView', 'room'=>$this->id));

        if(Current_User::allow('hms','room_structure') && $this->get_number_of_assignees() == 0) {
            $deleteRoomCmd = CommandFactory::getCommand('DeleteRoom');
            $deleteRoomCmd->setRoomId($this->id);
            $deleteRoomCmd->setFloorId($this->floor_id);

            $confirm             = array();
            $confirm['QUESTION'] = 'Are you sure want to delete room ' .  $this->room_number . '?';
            $confirm['ADDRESS']  = $deleteRoomCmd->getURI();
            $confirm['LINK']     = 'Delete';
            $tpl['DELETE']       = Layout::getJavascript('confirm', $confirm);
        }

        $form = new PHPWS_Form($this->id);
        $form->addSelect('gender_type', array(FEMALE => FEMALE_DESC,
        MALE   => MALE_DESC,
        COED   => COED_DESC
        ));

        $form->setMatch('gender_type', $this->gender_type);
        $form->setExtra('gender_type', 'onChange="submit_form(this, true)"');

        $form->addSelect('default_gender', array(FEMALE => FEMALE_DESC,
        MALE   => MALE_DESC,
        COED   => COED_DESC
        ));
        $form->setMatch('default_gender', $this->default_gender);
        $form->setExtra('default_gender', 'onChange="submit_form(this, true)"');

        $form->addCheck('offline', 'yes');
        $form->setLabel('offline', 'Offline');
        $form->setMatch('offline', $this->offline == 1 ? 'yes' : 0);
        $form->setExtra('offline', 'onChange="submit_form(this, false)"');
        
        $form->addCheck('reserved', 'yes');
        $form->setLabel('reserved', 'Reserved');
        $form->setMatch('reserved', $this->reserved == 1 ? 'yes' : 0);
        $form->setExtra('reserved', 'onChange="submit_form(this, false)"');
        
        $form->addCheck('ra', 'yes');
        $form->setLabel('ra', 'Reserved for RA:');
        $form->setMatch('ra', $this->ra == 1 ? 'yes' : 0);
        $form->setExtra('ra', 'onChange="submit_form(this, false)"');

        $form->addCheck('private', 'yes');
        $form->setLabel('private', 'Private');
        $form->setMatch('private', $this->private == 1 ? 'yes' : 0);
        $form->setExtra('private', 'onChange="submit_form(this, false)"');

        $form->addCheck('overflow', 'yes');
        $form->setLabel('overflow', '');
        $form->setMatch('overflow', $this->overflow == 1 ? 'yes' : 0);
        $form->setExtra('overflow', 'onChange="submit_form(this, false)"');

        $form->addHidden('action', 'UpdateRoomField');
        $form->addHidden('room', $this->id);

        $form->mergeTemplate($tpl);

        return $form->getTemplate();
    }

    //TODO: move this
    public static function update_row($id, $element, $value){
        if(!Current_User::allow('hms', 'room_attributes')){
            return 'bad permissions';
        }

        if($element == 'gender_type'){
            $r = new HMS_Room($id);
            if($r->get_number_of_assignees() > 0){
                $r->value   = false;
                $r->message = 'Cannot change the gender of a room while it contains students.';
                return $r;
            }
        }

        if(in_array($element, array_keys(get_class_vars('HMS_Room')))){
            if(!is_numeric($value)){
                $value = $value == 'yes' ? 1 : 0;
            }

            //Update the database by hand instead of loading and saving an
            //object to avoid possible race conditions.
            $db = new PHPWS_DB('hms_room');
            $db->addWhere('id', $id);
            $db->addValue($element, $value);
            $result = $db->update();

            $room = new HMS_Room($id);
            $room->value = true;
            return $room;
        }
        $room = new HMS_Room($id);
        $room->value = false;
        return $room;
    }

    /******************************
     * Accessor / Mutator Methods * 
     ******************************/
    
    public function getRoomNumber()
    {
        return $this->room_number;
    }
    
    public function isOffline()
    {
        return $this->offline == 1 ? true : false;
    }
    
    public function isReserved()
    {
        return $this->reserved == 1 ? true : false;
    }
    
    public function isRa()
    {
        return $this->ra == 1 ? true : false;
    }
    
    public function isPrivate()
    {
        return $this->private == 1 ? true : false;
    }
    
    public function isOverflow()
    {
        return $this->overflow == 1 ? true : false;
    }
    
    public function isADA()
    {
        return $this->ada == 1 ? true : false;
    }
    
    public function isHearingImpaired()
    {
        return $this->hearing_impaired == 1 ? true : false;
    }
    
    public function bathEnSuite()
    {
        return $this->bath_en_suite == 1 ? true : false;
    }
    
    public function isParlor()
    {
        return $this->parlor == 1 ? true : false;
    }
    /******************
     * Static Methods *
    *****************/

    public static function room_pager_by_floor($floor_id, $editable=false)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        javascript('jquery');

        $pager = new DBPager('hms_room', 'HMS_Room');
        $pager->addWhere('hms_room.floor_id', $floor_id);
        $pager->db->addOrder('hms_room.room_number');

        $page_tags['TABLE_TITLE']          = 'Rooms on this floor';
        $page_tags['ROOM_NUM_LABEL']       = 'Room Number';
        $page_tags['GENDER_TYPE_LABEL']    = 'Gender';
        $page_tags['DEFAULT_GENDER_LABEL'] = 'Default Gender';
        $page_tags['RA_LABEL']             = 'RA';
        $page_tags['PRIVATE_LABEL']        = 'Private';
        $page_tags['OVERFLOW_LABEL']       = 'Overflow';
        $page_tags['MEDICAL_LABEL']        = 'Medical';
        $page_tags['RESERVED_LABEL']       = 'Reserved';
        $page_tags['ONLINE_LABEL']         = 'Offline';
        $page_tags['DELETE_LABEL']         = 'Delete';

        if(Current_User::allow('hms', 'room_structure')){
            $addRoomCmd = CommandFactory::getCommand('ShowAddRoom');
            $addRoomCmd->setFloorId($floor_id);
            $page_tags['ADD_ROOM_LINK'] = $addRoomCmd->getLink('Add room');
        }

        $pager->setModule('hms');
        $pager->setTemplate('admin/room_pager_by_floor.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage('No rooms found.');

        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        if($editable){
            $pager->addRowTags('get_row_edit');
            $page_tags['FORM'] = 'form=true';
        } else {
            $page_tags['FORM'] = 'form=false';
            $pager->addRowTags('get_row_tags');
        }
        $pager->addPageTags($page_tags);

        return $pager->get();
    }

    /*
     * Deletes a room and any beds in it.  Returns true
    * if we're successful, false if not (or if there
    * is an assignment)
    */
    public static function deleteRoom($roomId)
    {

        if(!Current_User::allow('hms', 'room_structure')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to delete a room.');
        }

        PHPWS_Core::initModClass('hms','HMS_Bed.php');

        // check that we're not about to do something stupid
        if(!isset($roomId)){
            throw new InvalidArgumentException('Invalid room id.');
        }

        $room = new HMS_Room($roomId);

        // make sure there isn't an assignment
        if($room->get_number_of_assignees() != 0) {
            PHPWS_Core::initModClass('hms', 'exception/HallStructureException.php');
            throw new HallStructureException('One or more students are currently assigned to that room and therefore it cannot deleted.');
        }

        // delete any beds
        try{
            if($room->loadBeds()) {
                if(!empty($room->_beds)) {
                    foreach($room->_beds as $bed) {
                        HMS_Bed::deleteBed($bed->id);
                    }
                }
            }

            $result = $room->delete();
        }catch(Exception $e){
            throw $e;
        }

        return true;
    }

    /**
     * Returns the ID of an empty room (which can be auto-assigned)
     * Returns FALSE if there are no more free rooms
     */
    // TODO: finish this, see Trac #156
    public static function get_free_room($term, $gender, $randomize = FALSE)
    {
        $db = new PHPWS_DB('hms_room');

        // Only get free rooms
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_bed', 'id', 'room_id');
        $db->addJOIN('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');

    }

    public static function getAllFreeRooms($term)
    {
        $db = new PHPWS_DB('hms_room');

        $db->addColumn('id');
        $db->setDistinct();

        // Join other tables so we can do the other 'assignable' checks
        $db->addJoin('LEFT', 'hms_room', 'hms_bed', 'id', 'room_id');
        $db->addJoin('LEFT', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        // Term
        $db->addWhere('hms_room.term', $term);

        // Only get rooms with free beds
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addWhere('hms_assignment.asu_username', NULL);

        // Order by gender preference (0=>female, 1=>male, 2=>coed), rooms in a single gender hall will be first
        $db->addOrder('hms_residence_hall.gender_type ASC');

        // Make sure everything is online
        $db->addWhere('hms_room.offline', 0);
        $db->addWhere('hms_floor.is_online', 1);
        $db->addWhere('hms_residence_hall.is_online', 1);

        // Make sure nothing is reserved
        $db->addWhere('hms_room.reserved', 0);

        // Don't get RA beds
        $db->addWhere('hms_room.ra', 0);

        // Don't get lobbies
        $db->addWhere('hms_room.overflow', 0);

        // Don't get private rooms
        $db->addWhere('hms_room.private', 0);

        // Don't get rooms on floors reserved for an RLC
        $db->addWhere('hms_floor.rlc_id', NULL);

        $result = $db->select('col');

        // In case of an error, log it and return FALSE
        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }

        // Make sure each room is empty and has only two beds
        $ret = array_values(array_filter($result,
        array('HMS_Room', 'check_two_bed_and_empty_by_id')));

        if($randomize) {
            shuffle($ret);
        }

        return $ret;
    }

    public static function check_two_bed_and_empty_by_id($room)
    {
        $db = new PHPWS_DB('hms_bed');
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addColumn('hms_assignment.id', NULL, 'ass_id');
        $db->addWhere('room_id', $room);
        $db->addWhere('hms_bed.term', Term::getSelectedTerm());
        $result = $db->select('col');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        // If not two-bedroom, toss it out
        if(count($result) != 2) {
            return FALSE;
        }

        foreach($result as $r) {
            // If anyone is assigned, toss it out
            if($r != NULL) {
                return FALSE;
            }
        }

        // Looks like we're good.
        return TRUE;
    }

    public function __tostring()
    {
        return ($this->banner_building_code ? $this->banner_building_code . ' ' : '') . $this->room_number;
    }
}

?>
