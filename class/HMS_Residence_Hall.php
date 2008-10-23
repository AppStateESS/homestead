<?php

/**
 * HMS Residence Hall class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */ 

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Residence_Hall extends HMS_Item
{
    var $hall_name              = NULL;
    var $banner_building_code   = NULL;
    
    var $gender_type            = 2;
    var $air_conditioned        = 0;
    var $is_online              = 0;

    var $rooms_for_lottery      = 0;
    var $meal_plan_required     = 0;

    // Photo IDs
    var $exterior_image_id;
    var $other_image_id;
    var $map_image_id;
    var $room_plan_image_id;

    /**
     * Listing of floors associated with this room
     * @var array
     */
    var $_floors                = null;

    /**
     * Temporary values for rh creation
     */
    var $_number_of_floors      = 0;
    var $_rooms_per_floor       = 0;
    var $_beds_per_room         = 0;
    var $_numbering_scheme      = 0;

    /**
     * Constructor
     */
    function HMS_Residence_Hall($id = 0)
    {
        $this->construct($id, 'hms_residence_hall');
    }

    /********************
     * Instance Methods *
     *******************/

    /*
     * Saves a new or updated residence hall object
     */
    function save()
    {
        $this->stamp();
        $db = new PHPWS_DB('hms_residence_hall');
        $result = $db->saveObject($this);
        //test($result);
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }
    

    /*
     * Copies this residence hall object to a new term, then calls copy
     * on all 'this' room's floors.
     *
     * Setting $assignments to TRUE causes the copy function to copy
     * the current assignments as well as the hall structure.
     *
     * @return bool False if unsuccessful.
     */
    function copy($to_term, $assignments = FALSE)
    {
        if(!$this->id) {
            return false;
        }

        //echo "In hms_residence_hall, copying this hall: $this->id <br>";

        // Create clone of current room object
        // Set id to 0, set term, and save
        $new_hall = clone($this);
        $new_hall->reset();
        $new_hall->id   = 0;
        $new_hall->term = $to_term;

        if(!$new_hall->save()) {
            // There was an error saving the new hall
            // Error will be logged.
            //echo "error saving a copy of this hall<br>";
            return false;
        }

        // Save successful, create new floors
        
        //echo "loading floors of this hall<br>";
        
        // Load all floors for this hall
        if(empty($this->_floors)) {
            if(!$this->loadFloors()) {
                // There was an error loading the floors
                //echo "error loading floors<br>";
                return false;
            }
        }

        // Floors exist, start making copies

        if (!empty($this->_floors)) {
            foreach ($this->_floors as $floor) {
                $result = $floor->copy($to_term, $new_hall->id, $assignments);
                if(!$result){
                    //echo "error copying floor:<br>";
                    //test($result);
                    //test($floor);
                    return false;
                }
                // What if bad result?
            }
        }

        return true;
    }

    /**
     * Pulls all the floors associated with this hall and stores them in
     * the _floors variable.
     *
     */
    function loadFloors()
    {
        if (!$this->id) {
            $this->_floor = null;
            return null;
        }

        $db = new PHPWS_DB('hms_floor');
        $db->addWhere('residence_hall_id', $this->id);
        $db->addOrder('floor_number', 'ASC');

        $db->loadClass('hms', 'HMS_Floor.php');
        $result = $db->getObjects('HMS_Floor');
        if(PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_floors = & $result;
            return true;
        }
    }
    
    /*
     * Creates the floors, rooms, and beds for a new hall
     */
    function create_child_objects($num_floors, $rooms_per_floor, $beds_per_room)
    {
        if (!$this->id) {
            return false;
        }

        for ($i = 0; $i < $num_floors; $i++) {
            $floor = new HMS_Floor;
            
            $floor->residence_hall_id   = $this->id;
            $floor->term                = $this->term;
            $floor->gender_type         = $this->gender_type;

            if($floor->save()){
                $floor->create_child_objects($rooms_per_floor, $beds_per_room);
            } else {
                // Decide on bed result.
            }   
        }
    }


    /*
     * Returns TRUE or FALSE. The gender of a building can only be
     * changed to the target gender if all floors can be changed 
     * to the target gender.
     *
     * This function checks to make sure all floors can be changed,
     * those floors in tern check all thier rooms, and so on.
     */
    #TODO: rewrite this becase the behavior changed
    function can_change_gender($target_gender)
    {
        # You can always change to a COED gender.
        if($target_gender == COED){
            return true;

        }

        # We must be changing to either male or female if we make it here

        # If there are any COED floors, then return false
        if($this->check_for_floors_of_gender(COED)){
            return false;
        }

        # Can only change gender if there are no floors of the opposite sex
        if($target_gender == MALE){
            $check_for_gender = FEMALE;
        }else{
            $check_for_gender = MALE;
        }

        # If a check for rooms of the opposite gender returns true, then return false
        if($this->check_for_floors_of_gender($check_for_gender)){
            return false;
        }

        return true;
    }

    function check_for_floors_of_gender($gender_type)
    {
        $db = &new PHPWS_DB('hms_floor');

        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_floor.gender_type', $gender_type);

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if(PEAR::isError($result)){
            PHPWS_Error::logIfError($result);
            return null;
        }

        if($result == 0){
            return false;
        }else{
        return true;
    }
}
    
    /*
     * Returns the number of floors in the current hall
     */
    function get_number_of_floors()
    {
        $db = &new PHPWS_DB('hms_floor');
        
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');
        
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of suites in the current hall
     */
    function get_number_of_suites()
    {
        $db = &new PHPWS_DB('hms_suite');

        $db->addJoin('LEFT OUTER', 'hms_suite', 'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of rooms in the current hall
     */
    function get_number_of_rooms()
    {
        $db = &new PHPWS_DB('hms_room');
        
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');
        
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }


    /*
     * Returns the number of beds in the current hall
     */
    function get_number_of_beds()
    {
        $db = &new PHPWS_DB('hms_bed');
        
        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_room',           'room_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');
        
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;

    }

    /*
     * Returns the number of students currently assigned to the current hall
     */
    function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');
        
        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed',             'bed_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',        'hms_room',            'room_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_room',       'hms_floor',           'floor_id',         'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',      'hms_residence_hall',  'residence_hall_id','id');
        
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');

        if($result == 0){
            return $result;
        }
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns an array of floor objects which are within the current hall.
     */
    function &get_floors()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        return $this->_floors;
    }


    /*
     * Returns an array with the keys being floor ID's and the value being the floor number
     */
    function get_floors_array()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $floors = array();

        foreach($this->_floors as $floor){
            $floors[$floor->id] = $floor->floor_number;
        }

        return $floors;
    }
     

    /*
     * Returns an array of the suite objects which are within the current hall.
     */
    function &get_suites()
    {
        if (!$this->loadFloors()) {
            return false;
        }
        
        $suites = array();

        foreach($this->_floors as $floor){
            $floor_suites = $floor->get_suites();
            $suites = array_merge($suites, $floor_suites);
        }
        return $suites;
    }

    /*
     * Returns an array of room objects which are in the current hall
     */
    function &get_rooms()
    {
        if (!$this->loadFloors()) {
            return false;
        }
        
        $rooms = array();

        foreach($this->_floors as $floor){
            $floor_rooms = $floor->get_rooms();
            $rooms = array_merge($rooms, $floor_rooms);
        }
        return $rooms;
    }

    /*
     * Returns an array of the bed objects which are in the current hall
     */
    function &get_beds()
    {
        if (!$this->loadFloors()) {
            return false;
        }
        
        $beds = array();
        
        foreach($this->_floors as $floor){
            $floor_beds = $floor->get_beds();
            $beds = array_merge($rooms, $floor_beds);
        }
        return $beds;
    }

    /*
     * Determines the number of beds per room in a hall.  Should the count vary
     * it returns the count that applies to the majority of the rooms.
     */
    function count_beds_per_room()
    {
        $total = array(); //stores the number of rooms with that many beds

        //Get a list of all the rooms in the hall
        $rdb = &new PHPWS_DB('hms_room');

        $rdb->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $rdb->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $rdb->addWhere('hms_residence_hall.id', $this->id);

        $result = $rdb->select();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        //and for each room get a list of the beds
        foreach($result as $room){
            $db = &new PHPWS_DB('hms_bed');
            
            $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
            
            $db->addWhere('hms_room.id',           $room['id']);

            $result = $db->select('count');

            if(PHPWS_Error::logIfError($result)){
                return false;
            }

            //and increment the count of the number of rooms with that many 
            //beds in this hall
            if($result){
                $total[$result] = empty($total[$result]) ? 1 : $total[$result]+1;
            }
        }

        $top   = 0;
        foreach($total as $key => $value){
            if($total[$key] > $total[$top]){
                $top = $key;
            }
        }

        return $top;
    }

    /*
     * Returns an array of the student objects which are currently assigned to the current hall
     */
    function &get_assignees()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $assignees = array();

        foreach($this->_floors as $floor){
            $floor_assignees = $floor->get_assignees();
            $assignees = array_merge($rooms, $floor_assignees);
        }
        return $assignees;
    }

    /*
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
     * Returns an array of floor objects in this hall that have vacancies
     */
    function get_floors_with_vacancies()
    {
        if(!$this->loadFloors()) {
            return false;
        }

        $vacant_floors = array();

        foreach($this->_floors as $floor){
            if($floor->has_vacancy()){
                $vacant_floors[] = $floor;
            }
        }

        return $vacant_floors;
    }

    function count_avail_lottery_rooms($gender)
    {
        $now = mktime();

        # Calculate the number of non-full male/female rooms in this hall
        $query =   "SELECT COUNT(DISTINCT hms_room.id) FROM hms_room
                    JOIN hms_bed ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                    AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_residence_hall.id = {$this->id}
                    AND hms_room.gender_type = $gender
                    AND hms_room.is_medical = 0
                    AND hms_room.is_reserved = 0
                    AND hms_room.is_online = 1
                    AND hms_room.private_room = 0
                    AND hms_room.ra_room = 0
                    AND hms_room.is_lobby = 0
                    AND hms_floor.rlc_id IS NULL";

        $avail_rooms = PHPWS_DB::getOne($query);
        if(PEAR::isError($avail_rooms)){
            PHPWS_Error::log($avail_rooms);
            return FALSE;
        }

        return $avail_rooms;
    }

    function count_lottery_used_rooms()
    {
        $now = mktime();

        # Get the number of rooms in this hall which have every bed either assigned or reserved through the lottery.
        $query      = "SELECT count(hms_room.*) FROM hms_room 
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                       AND hms_residence_hall.id = {$this->id} AND
                       hms_room.id NOT IN (SELECT DISTINCT hms_room.id FROM hms_room
                       JOIN hms_bed ON hms_bed.room_id = hms_room.id
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                       WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                       AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term} and lottery = 1))
                       AND hms_residence_hall.id = {$this->id})";

        $used_rooms = PHPWS_DB::getOne($query);
        if(PEAR::isError($used_rooms)){
            PHPWS_Error::log($used_rooms);
            return FALSE;
        }

        return $used_rooms;
    }
    
    /******************
     * Static Methods *
     *****************/

    /**
     * Main Method
     */
    function main()
    {
        if( !Current_User::allow('hms', 'hall_structure') 
            && !Current_User::allow('hms', 'hall_attributes') 
            && !Current_User::allow('hms', 'hall_view')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        switch ($_REQUEST['op']) {
        case 'select_hall_to_edit':
            return HMS_Residence_Hall::show_select_residence_hall('Edit Residence Hall', 'hall', 'show_edit_hall');
            break;
        case 'show_edit_hall':
            return HMS_Residence_Hall::show_edit_residence_hall();
            break;
        case 'edit_hall':
            return HMS_Residence_Hall::edit_residence_hall();
            break;
        case 'select_residence_hall_for_overview':
            return HMS_Residence_Hall::show_select_residence_hall('Hall Overview', 'hall', 'show_residence_hall_overview');
            break;
        case 'show_residence_hall_overview':
            return HMS_Residence_Hall::show_hall_overview($_REQUEST['hall'], isset($_REQUEST['print']) && $_REQUEST['print'] == 1);
            break;
        default:
            return "Error: undefined hall op";
        }
    }

    


    /**
     * Returns an array of hall objects for the given term. If no
     * term is provided, then the current term is used.
     */
    function get_halls($term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $halls = array();

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addOrder('hall_name', 'DESC');

        if(isset($term)){
            $db->addWhere('term', $term);
        }else{
            $db->addWhere('term', HMS_Term::get_selected_term());
        }

        $results = $db->select();
        
        if(!$results || PHPWS_Error::logIfError($results)){
            return false;
        }

        foreach($results as $result){
            $halls[] = new HMS_Residence_Hall($result['id']);
        }

        return $halls;
    }

    /**
     * Returns an array with the hall id as the key and the hall name as the value
     */
    function get_halls_array($term = NULL)
    {
        $hall_array = array();

        $halls = HMS_Residence_Hall::get_halls($term);

        foreach ($halls as $hall){
            $hall_array[$hall->id] = $hall->hall_name;
        }

        return $hall_array;
    }

    /**
     * Returns an array of only the halls with vacancies
     */
    function get_halls_with_vacancies($term = NULL)
    {
        $vacant_halls = array();
        
        $halls = HMS_Residence_Hall::get_halls($term);

        if(!$halls){
            return false;
        }

        foreach($halls as $hall){
            if($hall->has_vacancy()){
                $vacant_halls[] = $hall;
            }
        }
        
        return $vacant_halls;
    }

    /**
     * Returns an array with a key of the hall ID and a value of the hall name
     * for halls which have vacancies
     */
    function get_halls_with_vacancies_array($term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $hall_array = array();

        $halls = HMS_Residence_Hall::get_halls_with_vacancies($term);

        if(!$halls){
            return FALSE;
        }

        foreach ($halls as $hall){
            $hall_array[$hall->id] = $hall->hall_name;
        }

        return $hall_array;
    }


    /**
     * Returns an associate array (key = hall id, value = hall name) of halls
     * which have an available lottery bed (based on the term, gender, the number
     * of lottery rooms allotted in the hall, the number of used lottery rooms, and 
     * any pending lottery bed reservations.
     */
    function get_lottery_avail_hall_list($term)
    {
        $halls = HMS_Residence_Hall::get_halls($term);

        $output_list = array();

        foreach($halls as $hall){
            $rooms_used = $hall->count_lottery_used_rooms();
            
            # If we've used up the number of allotted rooms, then remove this hall from the list
            if($rooms_used >= $hall->rooms_for_lottery){
                continue;
            }
            
            # Make sure we have a room of the specified gender available in the hall (or a co-ed room)
            if($hall->count_avail_lottery_rooms($gender) <= 0 &&
               $hall->count_avail_lottery_rooms(COED) <= 0){
                continue;
            }

            $output_list[] = $hall;
        }

        return $output_list;
    }

    /**
     * Returns the HTML for a DB pager of the current set of
     * residence halls available.
     */
    function residence_hall_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = &new DBPager('hms_residence_hall','HMS_Residence_Hall');
        $pager->db->addOrder('hall_name','DESC');
        #TODO: $pager->db->addWhere('term', SOMETHING);

        $pager->setModule('hms');
        $pager->setTemplate('admin/residence_hall_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No residence halls found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('get_row_tags');

        return $pager->get();   
    }
    
    /**
     * Returns the pager tags for the db pager
     */
    function get_row_tags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        $tags = $this->item_tags();
        $tags['HALL_NAME'] = $this->hall_name;
        $tags['BANNER_BUILDING_CODE'] = $this->banner_building_code;
        
        $is_online = $this->get_is_online();
        if ($is_online == ONLINE) {
            $tags['IS_ONLINE'] = ONLINE_DESC;
        }else if($is_online == OFFLINE){
            $tags['IS_ONLINE'] = OFFLINE_DESC;
        }else{
            $tags['IS_ONLINE'] = 'Error: Unknown status';
        }

        $gender_type = $this->get_gender_type();
        $tags['GENDER_TYPE'] = HMS_Util::formatGender($this->gender_type);

        #$num_beds = $this->get_number_of_beds();
        #$num_assignees = $this->get_number_of_assignees();
        #$num_beds_free = $num_beds - $num_beds_free;
        
        #$tags['NUM_FLOORS']     = $this->get_number_of_floors();
        #$tags['NUM_ROOMS']      = $this->get_number_of_rooms();
        #$tags['NUM_BEDS']       = $num_beds;
        #$tags['NUM_ASSIGNEES']  = $num_assignees();
        #$tags['NUM_BEDS_FREE']  = $num_beds_free();
        $tags['ACTIONS'] = 'View Delete'; #TODO
        return $tags; 
    }

    function edit_residence_hall()
    {
        # Create the hall object given the hall id
        $hall = new HMS_Residence_Hall($_REQUEST['hall_id']);
        if(!$hall){
            return show_select_residence_hall('Edit Hall', 'hall', 'show_edit__hall', null, 'Error: The select hall does not exist!');
        }

        # Compare the hall's gender and the gender the user selected
        # If they're not equal, call 'can_change_gender' function
        if($hall->gender_type != $_REQUEST['gender_type']){
            if(!$hall->can_change_gender($_REQUEST['gender_type'])){
                return HMS_Residence_Hall::show_edit_residence_hall($hall->id, NULL, 'Error: Incompatible gender detected. No changes were made.');
            }
        }

        # Grab all the input from the form and save the hall
        $hall->hall_name            = $_REQUEST['hall_name'];
        $hall->gender_type          = $_REQUEST['gender_type'];
        $hall->air_conditioned      = isset($_REQUEST['air_conditioned'])   ? 1 : 0;
        $hall->is_online            = isset($_REQUEST['is_online'])         ? 1 : 0;
        $hall->meal_plan_required   = isset($_REQUEST['meal_plan_required'])? 1 : 0;

        $rooms_for_lottery = $_REQUEST['rooms_for_lottery'];
        
        if(!isset($rooms_for_lottery) || $rooms_for_lottery == ''){
            $rooms_for_lottery = 0;
        }else if(!is_numeric($rooms_for_lottery)){
            $rooms_for_lottery = 0;
        }else if ($rooms_for_lottery < 0 || $rooms_for_lottery > 999){
            $rooms_for_lottery = 0;
        }

        # Check to make sure this isn't greater than the number of rooms in the hall
        if($rooms_for_lottery > $hall->get_number_of_rooms()){
            $rooms_for_lottery = $hall->get_number_of_rooms();
        }

        # TODO: check to make sure this isn't greater than the number of non-medical, non-reserved, non-ra, etc...... rooms

        $hall->rooms_for_lottery = $rooms_for_lottery;

        $hall->exterior_image_id    = $_REQUEST['exterior_image_id'];
        $hall->other_image_id       = $_REQUEST['other_image_id'];
        $hall->map_image_id         = $_REQUEST['map_image_id'];
        $hall->room_plan_image_id   = $_REQUEST['room_plan_image_id'];

        $result = $hall->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Residence_Hall::show_edit_residence_hall($hall->id, NULL, 'Error: There was a problem saving the hall data. No changes were made. Please contact ESS.');
        }

        return HMS_Residence_Hall::show_edit_residence_hall($hall->id, 'Residence hall updated successfully.');
    }

    /*********************
     * Static UI Methods *
     ********************/

    /**
     * A general function for displaying a drop down to select a hall.
     * The page can be used for various actions by passing in the 'type' and 'op'
     * variables which are submitted as hidden values in the form.
     */
    function show_select_residence_hall($title, $type, $op, $success = NULL, $error = NULL)
    {
        if(   !Current_User::allow('hms', 'hall_view')
           && !Current_User::allow('hms', 'hall_attributes')
           && !Current_User::allow('hms', 'hall_structure'))
        {
            $tpl = array();
            echo(PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl'));
            exit();
        }

        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();
        
        # Setup the title and color of the title bar
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl['TITLE']       = $title . ' - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Get the halls for the selected term        
        $halls = HMS_Residence_Hall::get_halls_array(HMS_Term::get_selected_term());

        if($halls == NULL){
            $tpl['ERROR_MSG'] = 'Error: No halls exist for the selected term';
            return PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
        }

        $tpl['MESSAGE'] = 'Please select a residence hall:';

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;
        $form->setMethod('get');
        $form->addDropBox('hall', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', $type);
        $form->addHidden('op', $op);
        $form->addSubmit('submit', _('Select Hall'));

        // TODO: REMOVE THIS HACK
        if(isset($_REQUEST['print']) && $_REQUEST['print'] == 1)
            $form->addhidden('print', 1);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/select_residence_hall.tpl');
    }
    
    /**
     * Does not use code from HMS_Forms add_residence_hall, fill_hall_data_display 
     */
    function show_edit_residence_hall($hall_id = null, $success = null, $error = null)
    {
        if( !Current_User::allow('hms', 'hall_attributes') 
            && !Current_User::allow('hms', 'hall_view')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        javascript('/jquery/');

        # Determine the hall id. If the passed in variable is null,
        # use the request.
        if(!isset($hall_id)){
            $hall_id = $_REQUEST['hall'];
        }

        # Setup the title and color of the title bar
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl['TITLE'] = 'Edit Residence Hall - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Create the hall given the hall id
        $hall = new HMS_Residence_Hall($hall_id);
        if(!$hall){
            return HMS_Hall::show_select_hall('Edit Residence Hall', 'hall', 'show_edit_hall', null, 'Error: The selected hall does not exist.'); 
        }
        
        $form = new PHPWS_Form;

        $form->addHidden('beds_per_room', $hall->count_beds_per_room()); // add a hidden field for beds per room

        $form->addText('hall_name', $hall->hall_name);
  
        /*
        $db = &new PHPWS_DB('hms_hall_communities');
        $comms = $db->select();
        foreach($comms as $comm) {
            $communities[$comm['id']] = $comm['community_name'];
        }
        $form->addDropBox('community', $communities);
        if(isset($hall->community)) {
            $form->setMatch('community', $hall->community);
        }
        */

        $tpl['NUMBER_OF_FLOORS']    = $hall->get_number_of_floors();
        $tpl['NUMBER_OF_ROOMS']     = $hall->get_number_of_rooms();
        $tpl['NUMBER_OF_BEDS']      = $hall->get_number_of_beds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $hall->get_number_of_assignees();

        $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('gender_type', $hall->gender_type);

        $form->addText('rooms_for_lottery', $hall->rooms_for_lottery);
        $form->setSize('rooms_for_lottery', 3, 3);
        
        $form->addCheckBox('air_conditioned', 1);
        $form->setMatch('air_conditioned', $hall->air_conditioned);
      
        $form->addCheckBox('is_online', 1);
        $form->setMatch('is_online', $hall->is_online);

        $form->addCheckBox('meal_plan_required', 1);
        $form->setMatch('meal_plan_required', $hall->meal_plan_required);

        // Images
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        if(isset($hall->exterior_image_id)){
            $manager = Cabinet::fileManager('exterior_image_id', $hall->exterior_image_id);
        }else{
            $manager = Cabinet::fileManager('exterior_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('EXTERIOR_IMG', $manager->get());


        if(isset($hall->other_image_id)){
            $manager = Cabinet::fileManager('other_image_id', $hall->other_image_id);
        }else{
            $manager = Cabinet::fileManager('other_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('OTHER_IMG', $manager->get());

        if(isset($hall->map_image_id)){
            $manager = Cabinet::fileManager('map_image_id', $hall->map_image_id);
        }else{
            $manager = Cabinet::fileManager('map_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('MAP_IMG', $manager->get());

        if(isset($hall->room_plan_image_id)){
            $manager = Cabinet::fileManager('room_plan_image_id', $hall->room_plan_image_id);
        }else{
            $manager = Cabinet::fileManager('room_plan_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('ROOM_PLAN_IMG', $manager->get());

        $form->addSubmit('submit', _('Save Hall'));

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', 'edit_hall');
        $form->addHidden('hall_id', $hall->id);
     
        # if the user has permission to view the form but not edit it then
        # disable it
        if(    Current_User::allow('hms', 'hall_view') 
           && !Current_User::allow('hms', 'hall_attributes')
           && !Current_User::allow('hms', 'hall_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }
   
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        $tpl['FLOOR_PAGER'] = HMS_Floor::get_pager_by_hall($hall->id);

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/edit_residence_hall.tpl');
        return $final;
    }

    /**
     * Shows a hall overview, listing the floors, rooms,
     * and assignments for those rooms
     */
    function show_hall_overview($hall_id, $naked = FALSE)
    {
        if( !Current_User::allow('hms', 'run_hall_overview') && !Current_User::allow('hms', 'hall_view') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $rlcs = HMS_Learning_Community::getRLCList();

        $hall = new HMS_Residence_Hall($hall_id);
        
        $content = '<h2 style="font-size: 1.5em">Building overview for ' . $hall->hall_name . '</h2>';

        if($naked) {
            $content .= '<p><a href="index.php">Back to Maintenance</a></p>';
        }

        # Load the halls
        $hall->loadFloors();

        # for each hall, print the floors
        foreach ($hall->_floors as $floor)
        {
            $content .= '<div style="margin: 1em;"><h3 style="font-size: 1.5em; margin: 0;">Floor ' . $floor->floor_number . '</h3>';

            # load the rooms
            $floor->loadRooms();

            # If rooms is null, skip this floor
            if(!isset($floor->_rooms)){
                continue;
            }

            # for each room, print the beds, and assignments
            foreach($floor->_rooms as $room)
            {
                $content .= '<div style="margin: 1em;"><strong>Room ' . $room->room_number . '</strong><br /><div style="margin: 0 0 0 1em;">';
               
                # Load the beds
                $room->loadBeds();

                # For each bed, print the bed (and assignment)
                foreach($room->_beds as $bed)
                {          
                    # Attempt to load the bed's assignment
                    $bed->loadAssignment();

                    if(isset($bed->_curr_assignment)){
                        # There is an assignment, so print it
                        $username = $bed->_curr_assignment->asu_username;
                        $rlc  = HMS_RLC_Assignment::check_for_assignment($username, HMS_Term::get_current_term()); //false or index
                        if($rlc != FALSE){
                            $rlc = ' (' . $rlcs[$rlc['rlc_id']] . ')'; //get the full name for the rlc
                        }
                        $name = HMS_SOAP::get_full_name($username);
                        $link = PHPWS_Text::secureLink($name, 'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$username)) . " (<em>$username</em>)$rlc";
                        $content .= 'Bedroom: ' . $bed->bedroom_label . '&nbsp;&nbsp;&nbsp;&nbsp;Bed: ' . $bed->bed_letter . '&nbsp;&nbsp;&nbsp;&nbsp;' . $link . '<br />';
                    }else{
                        # No one is assigned here
                        #TODO: Link this to Assignment
                        $content .= 'Bedroom: ' . $bed->bedroom_label . '&nbsp;&nbsp;&nbsp;&nbsp;Bed: ' . $bed->bed_letter . '&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"gray\">&lt;unassigned&gt;</font><br />';
                    }
                }//end foreach beds
                $content .= '</div></div>';
            }//end foreach rooms
            $content .= '</div>';
        }// end foreach floors

        if($naked) {
            Layout::nakedDisplay($content, 'Building overview for ' . $hall->hall_name, TRUE);
        }

        return $content; 
    }
}
?>
