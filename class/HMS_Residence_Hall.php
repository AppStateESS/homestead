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

    var $per_freshmen_rsvd      = NULL;
    var $per_sophomore_rsvd     = NULL;
    var $per_junior_rsvd        = NULL;
    var $per_senior_rsvd        = NULL;

    /**
     * Listing of floors associated with this room
     * @var array
     */
    var $_floors                = null;

    /**
     * Temporary values for rh creation
     */
    var $_number_of_floors      = 0;
    var $_bedrooms_per_room     = 0;
    var $_number_of_rooms       = 0;
    var $_beds_per_bedroom      = 0;
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
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     *
     */
    function loadFloors($deleted=0)
    {
        if (!$this->id) {
            $this->_floor = null;
            return null;
        }

        $db = new PHPWS_DB('hms_floor');
        $db->addWhere('residence_hall_id', $this->id);
        $db->addOrder('floor_number', 'ASC');

        switch ($deleted) {
        case -1:
            $db->addWhere('deleted', 1);
            break;

        case 0:
            $db->addWhere('deleted', 0);
            break;
        }


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
     * Creates the floors, rooms, bedrooms, and beds for a new hall
     */
    function create_child_objects($num_floors, $rooms_per_floor, $bedrooms_per_room, $beds_per_bedroom)
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
                $floor->create_child_objects($rooms_per_floor, $bedrooms_per_room, $beds_per_bedroom);
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
    #TODO: Implement the $ignore_upper flag.
    function can_change_gender($target_gender)
    {
        if ($target_gender != COED) {
            $this->loadFloors();
            if ($this->_floors) {
                foreach ($this->_floors as $floor){
                    if(!$floor->can_change_gender_down($target_gender)){
                        return false;
                    }
                }
            }
        }

        return true;
    }
    
    /*
     * Returns the number of floors in the current hall
     */
    function get_number_of_floors()
    {
        $db = &new PHPWS_DB('hms_floor');
        
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');
        
        $db->addWhere('hms_floor.deleted',          0);
        $db->addWhere('hms_residence_hall.deleted', 0);
        
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

        $db->addWhere('hms_suite.deleted', 0);
        $db->addWhere('hms_floor.deleted', 0);
        $db->addWhere('hms_residence_hall.deleted', 0);

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
        
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        $db->addWhere('hms_residence_hall.deleted', 0);
        
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }


    /*
     * Returns the number of bedrooms in the current hall
     */
    function get_number_of_bedrooms()
    {
        $db = &new PHPWS_DB('hms_bedroom');
        
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');
        
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        $db->addWhere('hms_residence_hall.deleted', 0);
        
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
        
        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_bedroom',        'bedroom_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',   'hms_residence_hall', 'residence_hall_id', 'id');
        
        $db->addWhere('hms_bed.deleted',            0);
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        $db->addWhere('hms_residence_hall.deleted', 0);
        
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
        
        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed',            'bed_id',            'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',        'hms_bedroom',        'bedroom_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_bedroom',    'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',       'hms_floor',          'floor_id',          'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',      'hms_residence_hall', 'residence_hall_id', 'id');
        
        $db->addWhere('hms_assignment.deleted',     0);
        $db->addWhere('hms_bed.deleted',            0);
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        $db->addWhere('hms_residence_hall.deleted', 0);
        
        $db->addWhere('hms_residence_hall.id', $this->id);

        $result = $db->select('count');
        
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
     * Returns an array of the bedroom objects which are in the current hall
     */
    function &get_bedrooms()
    {
        if (!$this->loadFloors()) {
            return false;
        }

        $bedrooms = array();
        
        foreach($this->_floors as $floor){
            $floor_bedrooms = $floor->get_bedrooms();
            $bedrooms = array_merge($rooms, $floor_bedrooms);
        }
        return $bedrooms;
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

    /**
     * Main Method
     */
    function main()
    {
        switch ($_REQUEST['op']) {
        case 'add_hall':
            return HMS_Residence_Hall::edit_residence_hall();
            break;
        case 'post_residence_hall':

            break;
        case 'select_residence_hall_for_overview':
            return HMS_Residence_Hall::show_select_residence_hall('Hall Overview', 'hall', 'show_residence_hall_overview');
            break;
        case 'show_residence_hall_overview':
            return HMS_Residence_Hall::show_hall_overview($_REQUEST['hall_id']);
            break;
        default:
            return "Error: undefined hall op";
        }
    }

    /**
     * Uses code from HMS_Forms add_residence_hall, fill_hall_data_display 
     */
    function edit_residence_hall()
    {
        $form = new PHPWS_Form;

        if (!empty($_REQUEST['halls'])) {
            $hall = new HMS_Residence_Hall($_REQUEST['halls']);
            $form->addHidden('halls', $hall->id);
        } else {
            $hall = new HMS_Residence_Hall;
        }

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

        $floors = array(1=>1,
                        2=>2,
                        3=>3,
                        4=>4,
                        5=>5,
                        6=>6,
                        7=>7,
                        8=>8,
                        9=>9,
                        10=>10,
                        11=>11,
                        12=>12,
                        13=>13,
                        14=>14,
                        15=>15);
        $form->addDropBox('number_floors', $floors);
        $form->setMatch('number_floors', $hall->_number_of_floors);
      
        for($i = 1; $i < 85; $i++) {
            $rooms[$i] = $i;
        }
       
        $form->addDropBox('rooms_per_floor', $rooms);
        $form->setMatch('rooms_per_floor', $hall->_number_of_rooms);

        $form->addDropBox('bedrooms_per_room', array(0=>'0', 1=>'1', 2=>'2', 3=>'3', 4=>'4'));
        $form->setMatch('bedrooms_per_room', $hall->_bedrooms_per_room);

        $form->addDropBox('beds_per_bedroom', array(0=>'0', 1=>'1', 2=>'2', 3=>'3', 4=>'4'));
        $form->setMatch('beds_per_bedroom', $hall->_beds_per_bedroom);

        $db = new PHPWS_DB('hms_pricing_tiers');
        $prices = $db->select();

        foreach($prices as $price) {
            $pricing[$price['id']] = '$' . $price['tier_value'];
        }
        
        $form->addDropBox('pricing_tier', $pricing);
        $form->setMatch('pricing_tier', '1');
        $form->addCheckBox('use_pricing_tier');

        $form->addRadio('gender_type', array(FEMALE, MALE, COED));
        $form->setLabel('gender_type', array(FEMALE_DESC, MALE_DESC, COED_DESC));
        $form->setMatch('gender_type', $hall->gender_type);

        $form->addRadio('air_conditioned', array(0,1));
        $form->setLabel('air_conditioned', array('No', 'Yes'));
        $form->setMatch('air_conditioned', $hall->air_conditioned);

      
        $form->addRadio('is_online', array(0, 1));
        $form->setLabel('is_online', array(_("No"), _("Yes")));
        $form->setMatch('is_online', $hall->is_online);

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'hall');
        $form->addHidden('op', 'save_residence_hall');


        $form->addSelect('numbering_scheme', array(0=>'Ground & First', 
                                                   1=>'Ground & Second', 
                                                   2=>'First & Second'));

        if(!$hall->id) {
            $form->addHidden('is_new_building', TRUE);
        }

        $form->addSubmit('submit', _('Save Hall'));

        $tpl = $form->getTemplate();

        if ($hall->id) {
            $tpl['TITLE'] = 'Edit Residence Hall';
        } else {
            $tpl['TITLE'] = 'Create Residence Hall';
        }
        //        $tpl['ERROR'] = $this->error;
        $tpl['BEDROOMS_PER_ROOM'] = $hall->_bedrooms_per_room;
        $tpl['BEDS_PER_BEDROOM'] = $hall->_beds_per_bedroom;


        /*
        switch($hall->_numbering_scheme)
        {
            case '0':
                $tpl['NUMBERING_SCHEME'] = "Ground + First";
                break;
            case '1':
                $tpl['NUMBERING_SCHEME'] = "Ground + Second";
                break;
            case '2':
                $tpl['NUMBERING_SCHEME'] = "First + Second";
                break;
            default:
                break;
        }
        */

        $halls = '<b>The following halls already exist: <br /><br />';
        $db = new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hall_name');
        $db->addWhere('deleted', '1', '!=');
        $db->addOrder('hall_name', 'ASC');
        $halls_raw = $db->select();
        foreach($halls_raw as $hall_raw) {
            $halls .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $hall_raw['hall_name'] . "<br />";
        }
        $halls .= "</b>";

        $tpl['HALLS']   = $halls;


        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_hall_data.tpl');
        return $final;
    }

    /******************
     * Static Methods *
     *****************/

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
        $db->addWhere('deleted', 0);
        $db->addOrder('hall_name', 'DESC');

        if(isset($term)){
            $db->addWhere('term', $term);
        }else{
            $db->addWhere('term', HMS_Term::get_current_term());
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
        $hall_array = array();

        $halls = HMS_Residence_Hall::get_halls_with_vacancies($term);

        foreach ($halls as $hall){
            $hall_array[$hall->id] = $hall->hall_name;
        }

        return $hall_array;
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
        $pager->db->addWhere('deleted', 0);
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
        $tags['GENDER_TYPE'] = HMS::formatGender($this->gender_type);

        #$num_beds = $this->get_number_of_beds();
        #$num_assignees = $this->get_number_of_assignees();
        #$num_beds_free = $num_beds - $num_beds_free;
        
        #$tags['NUM_FLOORS']     = $this->get_number_of_floors();
        #$tags['NUM_ROOMS']      = $this->get_number_of_rooms();
        #$tags['NUM_BEDROOMS']   = $this->get_number_of_bedrooms();
        #$tags['NUM_BEDS']       = $num_beds;
        #$tags['NUM_ASSIGNEES']  = $num_assignees();
        #$tags['NUM_BEDS_FREE']  = $num_beds_free();
        $tags['ACTIONS'] = 'View Delete'; #TODO
        return $tags; 
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
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();
        
        $tpl['TITLE'] = $title;
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
        $form->addDropBox('hall_id', $halls);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', $type);
        $form->addHidden('op', $op);
        $form->addSubmit('submit', _('Select Hall'));

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
     * Shows a hall overview, listing the floors, rooms,
     * and assignments for those rooms
     */
    function show_hall_overview($hall_id)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $hall = new HMS_Residence_Hall($hall_id);
        
        $content = "<h2>Building overview for " . $hall->hall_name . "</h2><br /><br />";

        # Load the halls
        $hall->loadFloors();

        # for each hall, print the floors
        foreach ($hall->_floors as $floor)
        {
            $content .= '<h3><b></u>Floor ' . $floor->floor_number . '</b></h3></a><br />';

            # load the rooms
            $floor->loadRooms();

            # If rooms is null, skip this floor
            if(!isset($floor->_rooms)){
                continue;
            }

            # for each room, print the bedrooms, beds, and assignments
            foreach($floor->_rooms as $room)
            {
                $content .= '&nbsp;&nbsp;&nbsp;&nbsp;<b>Room ' . $room->room_number . '</b><br />';
               
                # Load the bedrooms
                $room->loadBedrooms();
               
                # For each bedroom, print the beds (and assignments)
                foreach($room->_bedrooms as $bedroom)
                {          
                    # Load the beds
                    $bedroom->loadBeds();

                    foreach($bedroom->_beds as $bed)
                    {
                        # Attempt to load the bed's assignment
                        $bed->loadAssignment();

                        if(isset($bed->_curr_assignment)){
                            # There is an assignment, so print it
                            $username = $bed->_curr_assignment->asu_username;
                            $name = HMS_SOAP::get_full_name($username);
                            $link = PHPWS_Text::secureLink($name, 'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$username));
                            $content .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bedroom: ' . $bedroom->bedroom_letter . '&nbsp;&nbsp;&nbsp;&nbsp;Bed: ' . $bed->bed_letter . '&nbsp;&nbsp;&nbsp;&nbsp;' . $link . '<br />';
                        }else{
                            # No one is assigned here
                            #TODO: Link this to Assignment
                            $content .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bedroom: ' . $bedroom->bedroom_letter . '&nbsp;&nbsp;&nbsp;&nbsp;Bed: ' . $bed->bed_letter . '&nbsp;&nbsp;&nbsp;&nbsp;<font color=\"gray\">&lt;unassigned&gt;</font><br />';
                        }
                    }// end foreach beds
                    $content .= "<br />";
                }//end foreach bedrooms
            }//end foreach rooms
        }// end foreach floors

        return $content; 
    }

    /*******************
     * Mutator Methods *
     ******************/
     
    function set_id($id){
        $this->id = $id;
    }

    function get_id(){
        return $this->id;
    }

    function set_term($term){
        $this->term = $term;
    }

    function set_hall_name($name){
        $this->hall_name = $name;
    }

    function set_banner_building_code($code){
        $this->banner_building_code = $code;
    }
    
    function set_gender_type($gender){
        $this->gender_type = $gender;
    }

    function set_air_conditioned($ac){
        $this->air_conditioned = $ac;
    }

    function set_is_online($online){
        $this->is_online = $online;
    }


    function set_per_freshmen_rsvd($percent){
        $this->per_freshmen_rsvd = $precent;
    }


    function set_per_sophomore_rsvd($percent){
        $this->per_sophomore_rsvd = $percent;
    }


    function set_per_junior_rsvd($percent){
        $this->per_junior_rsvd = $percent;
    }


    function set_per_senior_rsvd($percent){
        $this->per_senior_rsvd = $percent;
    }


    function set_added_by($user_id = NULL){
        if(isset($user_id)){
            $this->added_by = $user_id;
        }else{
            $this->added_by = Current_User::getId();
        }
    }

    function get_added_by(){
        return $this->added_by;
    }


    function get_added_on(){
        return $this->added_on;
    }

    function get_updated_by(){
        return $this->updated_by;
    }

    function get_updated_on(){
        return $this->updated_on;
    }

    function get_deleted_by(){
        return $this->deleted_by;
    }

    function get_deleted_on(){
        return $this->deleted_on;
    }

    function set_deleted($del = 1){
        $this->deleted = $del;
    }

    function get_deleted(){
        return $this->deleted;
    }
}
?>
