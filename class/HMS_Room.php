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
     * Returns TRUE or FALSE.
     * TODO: write this documentation
     *
     * @param int  target_gender
     * @param bool ignore_upper In the case that we're attempting to change 
     *                          the gender of just 'this' room, set $ignore_upper
     *                          to TRUE to avoid checking the parent hall's gender.
     * @return bool
     */
    function can_change_gender($target_gender, $ignore_upper = FALSE)
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
            # TODO: make this check for males/females on the floor
            #       and allow for gender changes if everyone assigned
            #       is of the target gender.
            if(($target_gender != $this->gender_type) && ($this->get_number_of_assignees() != 0)){
                return false;
            }
             
            return true;
        }else{
            # Ignore upper is FALSE, load the floor and compare

            # Since we can't have coed rooms, we can never change to a
            # target of COED.
            if($target_gender == COED){
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
        
        if(PHPWS_Error::logIfError($result)){
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

    /**
     * Returns TRUE if this room is part of a suite.
     */
    function is_in_suite()
    {
        if(isset($this->suite_id)){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function getRoomPagerBySuiteTags()
    {
        $tpl['ROOM_NUMBER'] = PHPWS_Text::secureLink($this->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$this->id));
        $tpl['ACTION']      = "Remove";

        return $tpl;
    }

    /******************
     * Static Methods *
     *****************/

    function main()
    {
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
            default:
                echo "undefied room op: {$_REQUEST['op']}";
                break;
        }
    }

    function room_pager_by_floor($floor_id)
    {
       PHPWS_Core::initCoreClass('DBPager.php');
       
       $pager = & new DBPager('hms_room', 'HMS_Room');
       
       $pager->addWhere('hms_room.floor_id', $floor_id);
       $pager->addWhere('hms_room.deleted', 0);

       $page_tags['TABLE_TITLE']        = 'Rooms on this floor'; 
       $page_tags['ROOM_NUM_LABEL']     = 'Room Number';
       $page_tags['GENDER_TYPE_LABEL']  = 'Gender';
       $page_tags['RA_LABEL']           = 'RA';
       $page_tags['PRIVATE_LABEL']      = 'Private';
       $page_tags['LOBBY_LABEL']        = 'Lobby';
       $page_tags['MEDICAL_LABEL']      = 'Medical';
       $page_tags['RESERVED_LABEL']     = 'Reserved';
       $page_tags['ONLINE_LABEL']       = 'Online';

       $pager->setModule('hms');
       $pager->setTemplate('admin/room_pager_by_floor.tpl');
       $pager->setLink('index.php?module=hms');
       $pager->setEmptyMessage('No rooms found.');

       $pager->addToggle('class="toggle1"');
       $pager->addToggle('class="toggle2"');
       $pager->addRowTags('get_row_tags');
       $pager->addPageTags($page_tags);

       return $pager->get();
    }

    function get_row_tags()
    {
        //$tpl = $this->item_tags();

        $tpl['ROOM_NUMBER']  = PHPWS_Text::secureLink($this->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$this->id));
        $tpl['GENDER_TYPE']  = HMS::formatGender($this->gender_type);
        $tpl['RA_ROOM']      = $this->ra_room      ? 'Yes' : 'No';
        $tpl['PRIVATE_ROOM'] = $this->private_room ? 'Yes' : 'No';
        $tpl['IS_LOBBY']     = $this->is_lobby     ? 'Yes' : 'No';
        $tpl['IS_MEDICAL']   = $this->is_medical   ? 'Yes' : 'No';
        $tpl['IS_RESERVED']  = $this->is_reserved  ? 'Yes' : 'No';
        $tpl['IS_ONLINE']    = $this->is_online    ? 'Yes' : 'No';

        return $tpl;
    }
    
    function edit_room(){

        # Create the room object given the room_id
        $room = new HMS_Room($_REQUEST['room_id']);
        if(!$room){
            return show_select_room('Edit Room', 'room', 'show_edit_room', NULL, 'Error: The selected room does not exist!'); 
        }

        # Compare the room's gender and the gender the user selected
        # If they're not equal, call 'can_change_gender' function
        if($room->gender_type != $_REQUEST['gender_type']){
            if(!$room->can_change_gender($_REQUEST['gender_type'])){
                return HMS_Room::show_edit_room($room->id,NULL, 'Error: Incompatible genders detected. No changes were made.');
           }
        }

       # Grab all the input from the form and save the room
       $room->room_number   = $_REQUEST['room_number'];
       $room->pricing_tier  = $_REQUEST['pricing_tier']; 
       $room->gender_type   = $_REQUEST['gender_type'];
       $room->is_online     = $_REQUEST['is_online'];
       $room->is_reserved   = $_REQUEST['is_reserved'];
       $room->ra_room       = $_REQUEST['ra_room'];
       $room->private_room  = $_REQUEST['private_room'];
       $room->is_medical    = $_REQUEST['is_medical'];
       $room->is_lobby      = $_REQUEST['is_lobby'];

       $result = $room->save();

       if(!$result || PHPWS_Error::logIfError($result)){
           return show_edit_room($room->id, NULL, 'Error: There was a problem saving the room. No changes were made. Please contact ESS.');
       }

       return HMS_Room::show_edit_room($room->id, 'Room updated successfully.');
    }
    
    function get_room_pager_by_suite($suite_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = & new DBPager('hms_room', 'HMS_Room');

        $pager->addWhere('hms_room.suite_id', $suite_id);
        $pager->addWhere('hms_room.deleted', 0);

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
    
    /*********************
     * Static UI Methods *
     ********************/

    function show_select_room($title, $type, $op, $success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initCoreClass('Form.php');

        javascript('/modules/hms/select_room');
        
        $tpl = array();

        # Setup the title and color of the title bar
        $tpl['TITLE'] = $title;
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

        $form->addSubmit('submit', 'Select');
        $form->setExtra('submit', 'disabled');

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

    function show_edit_room($room_id = NULL, $success = null, $error = null)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Suite.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Pricing_Tier.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        # Determine the room id. If the passed in variable is NULL, use $_REQUEST
        if(!isset($room_id)){
            $room_id = $_REQUEST['room'];
        }
        
        # Setup the title and color of the title bar
        $tpl['TITLE'] = 'Edit Room';
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
        $tpl['NUMBER_OF_BEDROOMS']  = $room->get_number_of_bedrooms();
        $tpl['NUMBER_OF_BEDS']      = $room->get_number_of_beds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $number_of_assignees;

        $form = &new PHPWS_Form;
        
        $form->addText('room_number', $room->room_number);
        
        $form->addDropBox('pricing_tier', HMS_Pricing_Tier::get_pricing_tiers_array());
        $form->setMatch('pricing_tier', $room->pricing_tier);

        if(($number_of_assignees == 0) && !$is_in_suite){
            # Room is empty and not in a suite, show the drop down so the user can change the gender
            $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC));
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
        
        $form->addRadio('is_online', array(0, 1));
        $form->setLabel('is_online', array(_('No'), _('Yes') ));
        $form->setMatch('is_online', $room->is_online);

        $form->addRadio('is_reserved', array(0, 1));
        $form->setLabel('is_reserved', array(_('No'), _('Yes')));
        $form->setMatch('is_reserved', $room->is_reserved);
        
        $form->addRadio('ra_room', array(0, 1));
        $form->setLabel('ra_room', array(_('No'), _('Yes')));
        $form->setMatch('ra_room', $room->ra_room);
        
        $form->addRadio('private_room', array(0, 1));
        $form->setLabel('private_room', array(_('No'), _('Yes')));
        $form->setMatch('private_room', $room->private_room);
        
        $form->addRadio('is_medical', array(0,1));
        $form->setLabel('is_medical', array(_('No'), _('Yes')));
        $form->setMatch('is_medical', $room->is_medical);

        $form->addRadio('is_lobby', array(0, 1));
        $form->setLabel('is_lobby', array(_('No'), _('Yes')));
        $form->setMatch('is_lobby', $room->is_lobby);

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
        $tpl['ASSIGNMENT_PAGER'] = HMS_Assignment::assignment_pager_by_room($room->id);

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_room.tpl');   
    }

    
}

?>
