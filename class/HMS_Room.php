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

    var $floor_id               = 0;
    var $room_number            = 0;
    
    var $gender_type            = 0;
    var $default_gender         = 0;
    var $ra_room                = false;
    var $private_room           = false;
    var $is_overflow            = false;
    var $pricing_tier           = 0;
    var $is_medical             = false;
    var $is_reserved            = false;
    var $is_online              = false;
    var $suite_id               = NULL;


    /**
     * Listing of beds associated with this room
     * @var array
     */
    var $_beds                  = null;

    /**
     * Parent HMS_Floor object of this room
     * @var object
     */
    var $_floor                 = null;

    /* Hack for the javascript DO NOT TOUCH */
    var $message = '';
    var $value   = false; 

    /**
     * Constructor
     */
    public function HMS_Room($id = 0)
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
    public function save()
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
     * Deletes a room and any beds in it.  Returns true
     * if we're successful, false if not (or if there 
     * is an assignment)
     */
    public function delete_room($room_id)
    {
       // check that we're not about to do something stupid
       if(!isset($room_id)) return false;

       $room = new HMS_Room($room_id);

       // make sure there isn't an assignment
       if($room->get_number_of_assignees() != 0) {
           return false;
       }
       
       // delete any beds
       if($room->loadBeds()) {
           PHPWS_Core::initModClass('hms','HMS_Bed.php');
           // remove any beds the room may have.
           if(!empty($room->_beds)) { 
               foreach($room->_beds as $bed) {
                 if(!HMS_Bed::delete_bed($bed->id)) {
                     return false;
                 }
               }
           }
       }

       $result = $room->delete();

       if(PEAR::isError($result)){
           PHPWS_Error::log($result);
           return false;
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

        //Set the default gender to the floor's gender if the floor isn't
        //coed and the genders don't match.
        $new_room->loadFloor();
        if($new_room->_floor->gender_type != COED 
           && $new_room->default_gender != $new_room->_floor->gender_type)
        {
            $new_room->default_gender = $new_room->_floor->gender_type;
        }

        //Set the gender of the room to the default gender
        $new_room->gender_type = $new_room->default_gender;

        if (!$new_room->save()) {
            // There was an error saving the new room
            // Error will be logged.
            //echo "could not save a copy of this room<br>";
            return false;
        }
       
        // Save successful, create new beds

        // Load all beds for this room
        if (empty($this->_beds)) {
            if (!$this->loadBeds()) {
                // There was an error loading the beds
                // Delete new room?
                // $new_room->delete();
                //echo "error loading beds<br>";
                return false;
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

        if (!empty($this->_beds)) {
            foreach ($this->_beds as $bed) {
                $result = $bed->copy($to_term, $new_room->id, $assignments);
                if(!$result){
                    //echo "error copying bed";
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
    public function loadFloor()
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
        if (PHPWS_Error::logIfError($result)) {
            return false;
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
        # Ignore upper is true, we're trying to change a hall/floor
        if($ignore_upper){
            # If ignore upper is true and the target gender coed, then we
            # can always return true.
            if($target_gender == COED){
                return true;
            }

            # If the target gender is not the same, and someone is assigned
            # here, then the gender can't be changed (i.e. return false)
            if(($target_gender != $this->gender_type) && ($this->get_number_of_assignees() != 0)){
                return false;
            }
             
            return true;
        }else{
            # Ignore upper is FALSE, load the floor and compare

            # Since we can't have coed rooms, we can never change to a
            # target of COED.
            /*
             * Just kidding, we can have co-ed rooms
            if($target_gender == COED){
                return false;
            }
            */
            
            # If the target gender is not the same, and someone is assigned
            # here, then the gender can't be changed (i.e. return false)
            if(($target_gender != $this->gender_type) && ($this->get_number_of_assignees() != 0)){
                return false;
            }

            if (!$this->loadFloor()) {
                // an error occurred loading the floor, check logs
                return false;
            }
            
            // If the floor is not coed and the gt is not the target, return false
            if ($this->_floor->gender_type != COED && $this->_floor->gender_type != $target_gender) {
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
        $db = &new PHPWS_DB('hms_bed');

        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
 
        $db->addWhere('hms_room.id', $this->id);
        
        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;

    }

    /*
     * Returns the number of students assigned to the current room
     */
    public function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id'  );
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id' );
 
        $db->addWhere('hms_room.id', $this->id);

        
        $result = $db->select('count');
        
        if(PHPWS_Error::logIfError($result)){
            return false;
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
        if (!$this->loadBeds()) {
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
        if (!$this->loadBeds()) {
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

        if($this->get_number_of_assignees() < $this->get_number_of_beds()){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Returns an array of bed objects in this room that have vacancies
     */
    public function get_beds_with_vacancies()
    {
        if(!$this->loadBeds()) {
            return FALSE;
        }

        #test($this->_beds, 1);

        $vacant_beds = array();

        foreach($this->_beds as $bed){
            if($bed->has_vacancy()){
                $vacant_beds[] = $bed;
            }
        }

        return $vacant_beds;
    }

    /**
     * Returns TRUE if this room is part of a suite.
     */
    public function is_in_suite()
    {
        if(isset($this->suite_id)){
            return TRUE;
        }else{
            return FALSE;
        }
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
    
    public function getRoomPagerBySuiteTags()
    {
        $tpl['ROOM_NUMBER'] = PHPWS_Text::secureLink($this->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$this->id));
        $tpl['ACTION']      = "Remove";

        return $tpl;
    }

    public function count_avail_lottery_beds()
    {
        $now = mktime();

        # Count the number of beds which are free in this room
        $query =   "SELECT DISTINCT COUNT(hms_bed.id) FROM hms_bed
                    JOIN hms_room ON hms_bed.room_id = hms_room.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                    AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_room.id = {$this->id}
                    AND hms_room.is_medical = 0
                    AND hms_room.is_reserved = 0
                    AND hms_room.is_online = 1
                    AND hms_room.private_room = 0
                    AND hms_room.ra_room = 0
                    AND hms_room.is_overflow = 0";

        $avail_rooms = PHPWS_DB::getOne($query);
        if(PEAR::isError($avail_rooms)){
            PHPWS_Error::log($avail_rooms);
            return FALSE;
        }

        return $avail_rooms;
    }

    /******************
     * Static Methods *
     *****************/

    public function main()
    {
        if( !Current_User::allow('hms', 'room_structure') 
            && !Current_User::allow('hms', 'room_attributes')
            && !Current_User::allow('hms', 'room_view') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        switch($_REQUEST['op'])
        {
            case 'select_room_to_edit':
                return HMS_Room::show_select_room('Edit Room', 'room', 'show_edit_room');
                break;
            case 'show_edit_room':
                return HMS_Room::show_edit_room();
                break;
            case 'edit_room':
                return HMS_Room::edit_room();
                break;
            case 'add_room':
                return HMS_Room::add_room();
                break;
            case 'show_add_room':
                return HMS_Room::show_add_room();
                break;
            case 'get_row':
                echo HMS_Room::get_row_edit($_REQUEST['room']);
                die();
            case 'update_field':
                echo json_encode(HMS_Room::update_row($_REQUEST['id'], $_REQUEST['field'], $_REQUEST['value']));
                die();
            default:
                echo "undefied room op: {$_REQUEST['op']}";
                break;
        }
    }

    public function room_pager_by_floor($floor_id, $editable=false)
    {
       PHPWS_Core::initCoreClass('DBPager.php');
       javascript('/jquery/');
       
       $pager = & new DBPager('hms_room', 'HMS_Room');
       
       $pager->addWhere('hms_room.floor_id', $floor_id);
       $pager->db->addOrder('hms_room.room_number');

       $page_tags['TABLE_TITLE']        = 'Rooms on this floor'; 
       $page_tags['ROOM_NUM_LABEL']     = 'Room Number';
       $page_tags['GENDER_TYPE_LABEL']  = 'Gender';
       $page_tags['RA_LABEL']           = 'RA';
       $page_tags['PRIVATE_LABEL']      = 'Private';
       $page_tags['OVERFLOW_LABEL']     = 'Overflow';
       $page_tags['MEDICAL_LABEL']      = 'Medical';
       $page_tags['RESERVED_LABEL']     = 'Reserved';
       $page_tags['ONLINE_LABEL']       = 'Online';
       $page_tags['DELETE_LABEL']       = 'Delete';

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

    public function get_row_tags()
    {
        //$tpl = $this->item_tags();
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        $tpl['ID']           = $this->id;
        $tpl['ROOM_NUMBER']  = PHPWS_Text::secureLink($this->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$this->id));
        $tpl['GENDER_TYPE']  = HMS_Util::formatGender($this->gender_type);
        $tpl['RA_ROOM']      = $this->ra_room      ? 'Yes' : 'No';
        $tpl['PRIVATE_ROOM'] = $this->private_room ? 'Yes' : 'No';
        $tpl['IS_OVERFLOW']  = $this->is_overflow  ? 'Yes' : 'No';
        $tpl['IS_MEDICAL']   = $this->is_medical   ? 'Yes' : 'No';
        $tpl['IS_RESERVED']  = $this->is_reserved  ? 'Yes' : 'No';
        $tpl['IS_ONLINE']    = $this->is_online    ? 'Yes' : 'No';
        if(Current_User::allow('hms','room_structure')) {
            $tpl['DELETE']       = PHPWS_Text::secureLink('Delete', 'hms', array('type'=>'floor','op'=>'delete_room','room'=>$this->id,'floor'=>$this->floor_id));
        }

        return $tpl;
    }
    
    public function get_row_edit(){
        javascript('/jquery/');
        $tpl = array();
        $tpl['ID']           = $this->id;
        $tpl['ROOM_NUMBER']  = PHPWS_Text::secureLink($this->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$this->id));

        $form = new PHPWS_Form($this->id);
        $form->addSelect('gender_type', array(FEMALE => FEMALE_DESC,
                                         MALE   => MALE_DESC,
                                         COED   => COED_DESC)
                        );
        $form->setMatch('gender_type', $this->gender_type);
        $form->setExtra('gender_type', 'onChange="submit_form(this, true)"');

        $form->addCheck('ra_room', 'yes');
        $form->setMatch('ra_room', $this->ra_room == 1 ? 'yes' : 0);
        $form->setExtra('ra_room', 'onChange="submit_form(this, false)"');

        $form->addCheck('private_room', 'yes');
        $form->setMatch('private_room', $this->private_room == 1 ? 'yes' : 0);
        $form->setExtra('private_room', 'onChange="submit_form(this, false)"');

        $form->addCheck('is_overflow', 'yes');
        $form->setMatch('is_overflow', $this->is_overflow == 1 ? 'yes' : 0);
        $form->setExtra('is_overflow', 'onChange="submit_form(this, false)"');

        $form->addCheck('is_medical', 'yes');
        $form->setMatch('is_medical', $this->is_medical == 1 ? 'yes' : 0);
        $form->setExtra('is_medical', 'onChange="submit_form(this, false)"');

        $form->addCheck('is_reserved', 'yes');
        $form->setMatch('is_reserved', $this->is_reserved == 1 ? 'yes' : 0);
        $form->setExtra('is_reserved', 'onChange="submit_form(this, false)"');

        $form->addCheck('is_online', 'yes');
        $form->setMatch('is_online', $this->is_online == 1 ? 'yes' : 0);
        $form->setExtra('is_online', 'onChange="submit_form(this, false)"');

        $form->addHidden('type', 'room');
        $form->addHidden('op',   'edit_row');
        $form->addHidden('room', $this->id);

        $form->mergeTemplate($tpl);

        return $form->getTemplate();
    }

    function update_row($id, $element, $value){
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
    
    public function edit_room(){
        if( !Current_User::allow('hms', 'room_attributes') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        # Create the room object given the room_id
        $room = new HMS_Room($_REQUEST['room_id']);
        if(!$room){
            return show_select_room('Edit Room', 'room', 'show_edit_room', NULL, 'Error: The selected room does not exist!'); 
        }

        # Compare the room's gender and the gender the user selected
        # If they're not equal, call 'can_change_gender' public function
        if($room->gender_type != $_REQUEST['gender_type']){
            if(!$room->can_change_gender($_REQUEST['gender_type'])){
                return HMS_Room::show_edit_room($room->id,NULL, 'Error: Incompatible genders detected. No changes were made.');
           }
        }

        if($room->get_number_of_assignees() > 0 && $_REQUEST['is_online'] != 1){
            return HMS_Room::show_edit_room($room->id,NULL, 'Error: Cannot take room offline while students are assigned to the room.  No changes were made.');
        }

       # Grab all the input from the form and save the room
       //Changed from radio buttons to checkboxes, ternary 
       //prevents null since only 1 is defined as a return value
       //test($_REQUEST['room_number']);
       $room->room_number    = $_REQUEST['room_number'];
       $room->pricing_tier   = $_REQUEST['pricing_tier'];
       $room->gender_type    = $_REQUEST['gender_type'];
       $room->default_gender = $_REQUEST['default_gender'];
       $room->is_online      = $_REQUEST['is_online']    == 1 ? 1 : 0;
       $room->is_reserved    = $_REQUEST['is_reserved']  == 1 ? 1 : 0;
       $room->ra_room        = $_REQUEST['ra_room']      == 1 ? 1 : 0;
       $room->private_room   = $_REQUEST['private_room'] == 1 ? 1 : 0;
       $room->is_medical     = $_REQUEST['is_medical']   == 1 ? 1 : 0;
       $room->is_overflow    = $_REQUEST['is_overflow']  == 1 ? 1 : 0;

       $result = $room->save();

       if(!$result || PHPWS_Error::logIfError($result)){
           return HMS_Room::show_edit_room($room->id, NULL, 'Error: There was a problem saving the room. No changes were made. Please contact ESS.');
       }

       return HMS_Room::show_edit_room($room->id, 'Room updated successfully.');
    }
    
    public function add_room() {
        PHPWS_Core::initModClass('hms','HMS_Floor.php');
        PHPWS_Core::initModClass('hms','HMS_Residence_Hall.php');

       if(!Current_User::allow('hms','room_structure')){
           return HMS_Floor::show_edit_floor($_REQUEST['floor_id'], NULL, 'Error: You do not have permission to add rooms');
       }
       $floor = new HMS_Floor($_REQUEST['floor_id']);
       $room  = new HMS_Room();

       # Grab all the input from the form and save the room
       //Changed from radio buttons to checkboxes, ternary 
       //prevents null since only 1 is defined as a return value
       //test($_REQUEST['room_number']);
       $room->floor_id       = $_REQUEST['floor_id'];
       $room->hall_id        = $_REQUEST['hall_id'];
       $room->room_number    = $_REQUEST['room_number'];
       $room->pricing_tier   = $_REQUEST['pricing_tier'];
       $room->gender_type    = $_REQUEST['gender_type'];
       $room->default_gender = $_REQUEST['default_gender'];
       $room->is_online      = isset($_REQUEST['is_online'])    ? 1 : 0;
       $room->is_reserved    = isset($_REQUEST['is_reserved'])  ? 1 : 0;
       $room->ra_room        = isset($_REQUEST['ra_room'])      ? 1 : 0;
       $room->private_room   = isset($_REQUEST['private_room']) ? 1 : 0;
       $room->is_medical     = isset($_REQUEST['is_medical'])   ? 1 : 0;
       $room->is_overflow    = isset($_REQUEST['is_overflow'])  ? 1 : 0;
       $room->term           = $floor->term;

       $result = $room->save();

       if(!$result || PHPWS_Error::logIfError($result)){
           return HMS_Floor::show_edit_floor($room->floor_id, NULL, 'Error: There was a problem adding the room. No changes were made. Please contact ESS.');
       }

       return HMS_Floor::show_edit_floor($room->floor_id, 'Room added successfully.');


    }

    public function get_room_pager_by_suite($suite_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = & new DBPager('hms_room', 'HMS_Room');

        $pager->addWhere('hms_room.suite_id', $suite_id);

        $page_tags['ROOM_NUMBER_LABEL'] = "Room Number";
        $page_tags['ACTION_LABEL']      = "Action";
        $page_tags['TABLE_TITLE']       = "Rooms In Suite";

        $pager->setModule('hms');
        $pager->setTemplate('admin/room_pager_by_suite.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No rooms found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getRoomPagerBySuiteTags');
        $pager->addPageTags($page_tags);

        $pager->initialize();

        return $pager->get();
    }

    /**
     * Returns the ID of an empty room (which can be auto-assigned)
     * Returns FALSE if there are no more free rooms
     */
    # TODO: finish this, see Trac #156
    public function get_free_room($term, $gender, $randomize = FALSE)
    {
        $db = &new PHPWS_DB('hms_room');

        // Only get free rooms
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_bed', 'id', 'room_id');
        $db->addJOIN('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');

    }

    public function get_all_free_rooms($term, $gender, $randomize = FALSE)
    {
        $db = &new PHPWS_DB('hms_room');

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

        // Gender
        $db->addWhere('gender_type', $gender);

        // Make sure everything is online
        $db->addWhere('hms_room.is_online', 1);
        $db->addWhere('hms_floor.is_online', 1);
        $db->addWhere('hms_residence_hall.is_online', 1);

        // Make sure nothing is reserved
        $db->addWhere('hms_room.is_reserved', 0);
        $db->addWhere('hms_room.is_medical', 0);

        // Don't get RA beds
        $db->addWhere('hms_room.ra_room', 0);

        // Don't get lobbies
        $db->addWhere('hms_room.is_overflow', 0);

        // Don't get private rooms
        $db->addWhere('hms_room.private_room', 0);
        
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

    public function check_two_bed_and_empty_by_id($room)
    {
        $db = &new PHPWS_DB('hms_bed');
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addColumn('hms_assignment.id', NULL, 'ass_id');
        $db->addWhere('room_id', $room);
        $db->addWhere('hms_bed.term', HMS_Term::get_selected_term());
        $result = $db->select('col');

        // If not two-bedroom, toss it out
        if(count($result) != 2) { return FALSE; }

        foreach($result as $r) {
            // If anyone is assigned, toss it out
            if($r != NULL) { return FALSE; }
        }

        
        // Looks like we're good.
        return TRUE;
    }

    
    /*********************
     * Static UI Methods *
     ********************/

    public function show_select_room($title, $type, $op, $success = NULL, $error = NULL)
    {
        if(   !Current_User::allow('hms', 'room_view')
           && !Current_User::allow('hms', 'room_attributes')
           && !Current_User::allow('hms', 'room_structure'))
        {
            $tpl = array();
            echo(PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl'));
            exit();
        }


        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initCoreClass('Form.php');

        javascript('/modules/hms/select_room');
        
        $tpl = array();

        # Setup the title and color of the title bar
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl['TITLE'] = $title . ' - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Get the halls for the selected term
        $halls = HMS_Residence_Hall::get_halls_array(HMS_Term::get_selected_term());

        # Show an error if there are no halls for the current term
        if($halls == NULL){
            $tpl['ERROR_MSG'] = 'Error: No halls exist for the selected term. Please create a hall first.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/select_room.tpl');
        }

        $halls[0] = 'Select...';

        $tpl['MESSAGE'] = 'Please select a room: ';

        # Setup the form
        $form = &new PHPWS_Form;
        $form->setMethod('get');
        $form->addDropBox('residence_hall', $halls);
        $form->setLabel('residence_hall', 'Residence hall: ');
        $form->setMatch('residence_hall', 0);
        $form->setExtra('residence_hall', 'onChange="handle_hall_change()"');

        $form->addDropBox('floor', array(0 => ''));
        $form->setLabel('floor', 'Floor: ');
        $form->setExtra('floor', 'disabled onChange="handle_floor_change()"');

        $form->addDropBox('room', array(0 => ''));
        $form->setLabel('room', 'Room: ');
        $form->setExtra('room', 'disabled onChange="handle_room_change()"');

        $form->addSubmit('submit_button', 'Select');
        $form->setExtra('submit_button', 'disabled');

        # Use the type and op that was passed in
        $form->addHidden('module', 'hms');
        $form->addHidden('type', $type);
        $form->addHidden('op', $op);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/select_room.tpl');
    }

    public function show_edit_room($room_id = NULL, $success = null, $error = null)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Suite.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Pricing_Tier.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        # Determine the room id. If the passed in variable is NULL, use $_REQUEST
        if(!isset($room_id)){
            $room_id = $_REQUEST['room'];
        }
        

        # Setup the title and color of the title bar
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl['TITLE'] = 'Edit Room - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        # Create the room object given the room_id
        $room = new HMS_Room($room_id);
        if(!$room){
            return HMS_Room::show_select_room('Edit Room', 'room', 'show_edit_room', NULL, 'Error: The selected room does not exist!'); 
        }

        # Create the floor object
        $floor = $room->get_parent();
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_room.tpl');
        }

        $hall = $floor->get_parent();
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_room.tpl');
        }

        
        $number_of_assignees    = $room->get_number_of_assignees();
        $is_in_suite            = $room->is_in_suite();

        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$hall->id));
        $tpl['FLOOR_NUMBER']        = PHPWS_Text::secureLink($floor->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$floor->id));
        $tpl['NUMBER_OF_BEDS']      = $room->get_number_of_beds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $number_of_assignees;

        $form = &new PHPWS_Form;
        
        $form->addText('room_number', $room->room_number);
        
        $form->addDropBox('pricing_tier', HMS_Pricing_Tier::get_pricing_tiers_array());
        $form->setMatch('pricing_tier', $room->pricing_tier);

        if(($number_of_assignees == 0) && !$is_in_suite){
            # Room is empty and not in a suite, show the drop down so the user can change the gender
            $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED=>COED_DESC));
            $form->setMatch('gender_type', $room->gender_type);
        }else{
            # Room is not empty or in a suite so just show the gender (no drop down)
            if($room->gender_type == FEMALE){
                $tpl['GENDER_MESSAGE'] = "Female";
            }else if($room->gender_type == MALE){
                $tpl['GENDER_MESSAGE'] = "Male";
            }else if($room->gender_type == COED){
                $tpl['GENDER_MESSAGE'] = "Coed";
            }else{
                $tpl['GENDER_MESSAGE'] = "Error: Undefined gender";
            }
            # Add a hidden variable for 'gender_type' so it will be defined upon submission
            $form->addHidden('gender_type', $room->gender_type);
            # Show the reason the gender could not be changed.
            if($number_of_assignees != 0){
                $tpl['GENDER_REASON'] = 'Remove occupants to change room gender.';
            }else if($is_in_suite){
                $tpl['GENDER_REASON'] = PHPWS_Text::secureLink('Edit the suite', 'hms', array('type'=>'suite', 'op'=>'show_edit_suite', 'suite'=>$room->suite_id)) . ' to change room gender.';
            }
        }

        //Always show the option to set the default gender
        $form->addDropBox('default_gender', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('default_gender', $room->default_gender);
        
        $form->addCheck('is_online', 1);
        //$form->setLabel('is_online', array(_('No'), _('Yes') ));
        $form->setMatch('is_online', $room->is_online);

        $form->addCheck('is_reserved', 1);
        //$form->setLabel('is_reserved', array(_('No'), _('Yes')));
        $form->setMatch('is_reserved', $room->is_reserved);
        
        $form->addCheck('ra_room', 1);
        //$form->setLabel('ra_room', array(_('No'), _('Yes')));
        $form->setMatch('ra_room', $room->ra_room);
        
        $form->addCheck('private_room', 1);
        //$form->setLabel('private_room', array(_('No'), _('Yes')));
        $form->setMatch('private_room', $room->private_room);
        
        $form->addCheck('is_medical', 1);
        //$form->setLabel('is_medical', array(_('No'), _('Yes')));
        $form->setMatch('is_medical', $room->is_medical);

        $form->addCheck('is_overflow', 1);
        //$form->setLabel('is_overflow', array(_('No'), _('Yes')));
        $form->setMatch('is_overflow', $room->is_overflow);

        if($is_in_suite){
            # Room is in a suite
            $tpl['IS_IN_SUITE'] = PHPWS_Text::secureLink('Yes', 'hms', array('type'=>'suite', 'op'=>'show_edit_suite', 'suite'=>$room->suite_id));
            
            # Create the suite and get the rooms in it
            $suite = new HMS_Suite($room->suite_id);
            $suite_rooms = $suite->get_rooms();

            # Generate the list of other rooms in this suite
            foreach ($suite_rooms as $suite_room){
                # Remove this room from the list
                if($room->id == $suite_room->id){
                    #continue;
                }else{
                    $tpl['SUITE_ROOM_LIST'][] = array('SUITE_ROOM' => PHPWS_Text::secureLink($suite_room->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$suite_room->id)));
                }
            }

        }else{
            # Room is not in a suite
            $tpl['IS_IN_SUITE'] = 'No';
        }

        $form->addHidden('room_id', $room->id);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'edit_room');

        $form->addSubmit('submit', 'Submit');
        
        # TODO: add an assignment pager here
        $tpl['BED_PAGER'] = HMS_Bed::bed_pager_by_room($room->id);

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }
        
        # if the user has permission to view the form but not edit it then
        # disable it
        if(    Current_User::allow('hms', 'room_view') 
           && !Current_User::allow('hms', 'room_attributes')
           && !Current_User::allow('hms', 'room_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_room.tpl');   
    }

    public function show_add_room($hall_id = NULL, $floor_id = NULL) {

        # include what we need
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Suite.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Pricing_Tier.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');


        # Setup the title and color of the title bar
        $tpl['TITLE']       = 'Add Room';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        # Check to make sure we have a floor and hall.
        $floor = new HMS_Floor($floor_id);
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }
        
        $hall = new HMS_Residence_Hall($hall_id);
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }

        # Check Permissions
        if(!Current_User::allow('hms','room_structure')) {
            HMS_Floor::show_edit_floor($floor_id,NULL,'You do not have permission to add rooms.');
        }

        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$hall->id));
        $tpl['FLOOR_NUMBER']        = PHPWS_Text::secureLink($floor->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$floor->id));

        $form = new PHPWS_Form;
        $form->addText('room_number');
        $form->addHidden('hall_id',$hall->id);
        $form->addHidden('floor_id',$floor->id);
        
        $form->addDropBox('pricing_tier', HMS_Pricing_Tier::get_pricing_tiers_array());

        if($floor->gender_type == COED) {
            $form->addDropBox('gender_type', array(FEMALE=>FEMALE_DESC, MALE=>MALE_DESC));
            $form->setMatch('gender_type', HMS_Util::formatGender($floor->gender_type));
        }else{
            $form->addDropBox('gender_type', array($floor->gender_type=>HMS_Util::formatGender($floor->gender_type)));
            $form->setReadOnly('gender_type', true);
        }

        //Always show the option to set the default gender
        $defGenders = array(FEMALE => FEMALE_DESC, MALE => MALE_DESC);
        if($floor->gender_type == MALE)     unset($defGenders[FEMALE]);
        if($floor->gender_type == FEMALE)   unset($defGenders[MALE]);
        $form->addDropBox('default_gender', $defGenders);
        if($floor->gender_type != COED) {
            $form->setMatch('default_gender', $floor->gender_type);
        }

        $form->addCheck('is_online', 1);

        $form->addCheck('is_reserved', 1);
        
        $form->addCheck('ra_room', 1);
        
        $form->addCheck('private_room', 1);
        
        $form->addCheck('is_medical', 1);

        $form->addCheck('is_overflow', 1);

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'room');
        $form->addHidden('op', 'add_room');

        $form->addSubmit('submit', 'Submit');
        
        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }
        
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');   
    }
 
}

?>
