<?php

/**
 * HMS Floor class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Floor extends HMS_Item
{
    var $floor_number;
    var $residence_hall_id;
    var $is_online;
    var $gender_type;
    var $f_movein_time_id;
    var $t_movein_time_id;
    var $rt_movein_time_id;
    var $rlc_id;
    var $floor_plan_image_id;

    /**
     * List of rooms associated with this floor
     * @vary array
     */
    var $_rooms     = null;

    /**
     * Holds the parent residence hall object of this floor
     */
    var $_hall      = null;
    
    /**
     * Constructor
     */
    public function HMS_Floor($id = 0)
    {
        $this->construct($id, 'hms_floor');
    }

    /********************
     * Instance Methods *
     *******************/

    /*
     * Saves a new or updated floor hall object
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_floor');

        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    /*
     * Copies this floor object to a new term, then calls copy on all
     * 'this' floor's rooms
     *
     * Setting $assignments to 'TRUE' causes the copy public function to copy
     * the assignments as well as the hall structure.
     *
     * @return bool False if unsuccessful.
     */
    public function copy($to_term, $hall_id, $assignments = FALSE)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_floor, copying this floor id: $this->id <br>";

        // Create a clone of the current floor object
        // Set id to 0, set term, and save
        $new_floor = clone($this);
        $new_floor->reset();
        $new_floor->term = $to_term;
        $new_floor->residence_hall_id = $hall_id;
        $new_floor->f_movein_time_id = NULL;
        $new_floor->t_movein_time_id = NULL;
        $new_floor->rt_movein_time_id = NULL;

        if(!$new_floor->save()) {
            // There was an error saving the new floor
            echo "error saving a copy of this floor";
            return false;
        }

        // Load all the rooms for this floor
        if(empty($this->_rooms)) {
            $result = $this->loadRooms();
            if(!$result) {
                // There was an error loading the rooms
                echo "There was an error loading the rooms";
                //test($this);
                return false;
            }else{
                //echo "rooms loaded successfully<br>";
            }
        }

        /**
         * Rooms exist. Start making copies.
         * Further copying is needed at the room level.
         */
        
        if(!empty($this->_rooms)) {
            foreach ($this->_rooms as $room) {
                $result = $room->copy($to_term, $new_floor->id, NULL, $assignments);
                // What if bed result?
                if(!$result){
                    echo "error copying room id: $room->id <br>";
                    test($result);
                    return false;
                }
            }
        }

        return true;
    }
    
    /**
     * Loads the parent hall object of this floor
     */
    public function loadHall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $result = new HMS_Residence_Hall($this->residence_hall_id);
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }
        $this->_hall = & $result;
        return true;
    }

    /**
     * Pulls all the rooms associated with this floor and stores
     * them in the _room variable.
     */
    public function loadRooms()
    {

        $db = new PHPWS_DB('hms_room');
        $db->addWhere('floor_id', $this->id);
        $db->addOrder('room_number', 'ASC');

        $db->loadClass('hms', 'HMS_Room.php');
        $result = $db->getObjects('HMS_Room');
        //test($result);
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_rooms = & $result;
            return true;
        }
    }

    /*
     * Creates the rooms and beds for a new floor
     */
    public function create_child_objects($rooms_per_floor, $beds_per_room)
    {
        for ($i = 0; $i < $rooms_per_floor; $i++) {
            $room = new HMS_Room;

            $room->floor_id     = $this->id;
            $room->term         = $this->term;
            $room->gender_type  = $this->gender_type;

            if($room->save()) {
                $room->create_child_objects($beds_per_room);
            } else {
                // Decide on bad Result.
            }
        }
    }

    /*
     * Returns TRUE or FALSE. 
     * 
     * This public function uses the following logic:
     * 
     * When ignore_upper = TRUE (a hall is trying to see if this floor can be changed to a target gender):
     *      If the target gender is COED: always return true, since it doesn't matter what the rooms are (or what the hall is)
     *      If the target gender is MALE: return false if any room is female and non-empty
     *      If the target gender is FEMALE: return false if any room is male and non-empty
     *      If all thsoe checks pass, then return true
     *
     *      When ignore_upper = FALSE (we're trying to change *this* floor to a target gender):
     *      If the target gender is COED: return true only if the hall is COED (but it doesn't matter what the rooms are)
     *      If the target gender is MALE: return false if the hall is female, or if there are any female rooms on the floor
     *      If the target gender is FEMALE: return false if the hall is male, or if there are any male rooms on the floor
     *      
     * @param int   target_gender
     * @param bool  ignore_upper
     * @return bool            
     */
    public function can_change_gender($target_gender, $ignore_upper = FALSE)
    {
        # Ignore upper is true, we're trying to change a hall's gender
        if($ignore_upper){
            # If ignore upper is true and the target gender is coed, then
            # we can always return true.
            if($target_gender == COED){
                return true;
            }

            # Can only change to male/female if there are no rooms of the opposite sex on this hall
            # TODO: This should check for rooms that are of the opposite sex AND not empty
            if($target_gender == MALE){
                $check_for_gender = FEMALE;
            }else{
                $check_for_gender = MALE;
            }

            # If a check for rooms of the opposite gender returns true, then return false
            if($this->check_for_rooms_of_gender($check_for_gender)){
                return false;
            }

        }else{
            # Ignore upper is FALSE, load the hall and compare

            if(!$this->loadHall()){
                // an error occured loading the hall
                return false;
            }

            # The target gender must match the hall's gender, unless the hall is COED
            if($this->_hall->gender_type != COED && $this->_hall->gender_type != $target_gender){
                return false;
            }
            
            # Additionally, we need to check for rooms of the oppsite sex, unless the target gender is COED
            if($target_gender != COED){
                if($target_gender == MALE){
                    $check_for_gender = FEMALE;
                }else{
                    $check_for_gender = MALE;
                }

                # If a check for rooms of the opposite gender returns true, then return false
                if($this->check_for_rooms_of_gender($check_for_gender)){
                    return false;
                }   
            }
        }
        
        return true;
    }

    public function check_for_rooms_of_gender($gender_type)
    {
        $db = &new PHPWS_DB('hms_room');

        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        
        $db->addWhere('hms_room.gender_type', $gender_type);
        
        $db->addWhere('hms_floor.id', $this->id);

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
     * Returns the number of rooms on the current floor
     */
    public function get_number_of_rooms()
    {
        $db = &new PHPWS_DB('hms_room');

        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');

        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of beds on the current floor
     */
    public function get_number_of_beds()
    {
        $db = &new PHPWS_DB('hms_bed');
        
        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        
        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');
        
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of assignees on the current floor
     */
    public function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');
        
        $db->addJoin('LEFT OUTER', 'hms_assignment','hms_bed',            'bed_id',             'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',       'hms_room',           'room_id',            'id');
        $db->addJoin('LEFT OUTER', 'hms_room',      'hms_floor',          'floor_id',           'id');
        
        $db->addWhere('hms_floor.id', $this->id);

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
     * Returns the parent hall object of this floor
     */
    public function get_parent()
    {
        $this->loadHall();
        return $this->_hall;
    }

    /*
     * Returns an array of the rooms on the current floor
     */
    public function get_rooms()
    {
        if (!$this->loadRooms()) {
            return false;
        }

        return $this->_rooms;
    }

    /*
     * Returns an associative array where the keys are room ID's
     * and the values are the room numbers.
     */
    public function get_rooms_array()
    {
        if(!$this->loadRooms()) {
            return FALSE;
        }

        $rooms = array();

        foreach($this->_rooms as $room){
            $rooms[$room->id] = $room->room_number;
        }

        return $rooms;

    }

    /**
     * Returns an array of the beds on the current floor
     */
    public function get_beds()
    {
        $beds = array();

        if (!$this->loadRooms()){
            return false;
        }

        foreach($this->_rooms as $room){
            $room_beds = $room->get_beds();
            $beds = array_merge($beds, $room_beds);
        }
        return $beds;
    }

    /**
     * Returns an array of student objects which are currently assigned to this floor
     */
    public function get_assignees()
    {
        if (!$this->loadRooms()) {
            return false;
        }

        $assignees = array();

        foreach($this->_rooms as $room){
            $room_assignees = $room->get_assignees();
            $assignees = array_merge($assignees, $room_assignees);
        }

        return $assignees;
    }

    /**
     * Returns TRUE if this floor has vancancies, FALSE otherwise
     */
    public function has_vacancy()
    {
        if($this->get_number_of_assignees() < $this->get_number_of_beds()){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Returns an array of room objects on this floor that have vacancies
     */
    public function get_rooms_with_vacancies()
    {
        if(!$this->loadRooms()) {
            return FALSE;
        }

        $vacant_room = array();

        foreach($this->_rooms as $room){
            if($room->has_vacancy()){
                $vacant_rooms[] = $room;
            }
        }

        return $vacant_rooms;
    }

    public function where_am_i($link = FALSE)
    {
        $building = $this->get_parent();

        $text = $building->hall_name . ', floor ' . $this->floor_number;

        if($link){
            return PHPWS_Text::secureLink($text, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$this->id));
        }else{
            return $text;
        }
    }

    public function count_avail_lottery_rooms($gender)
    {
        $now = mktime();

        # Calculate the number of non-full male/female rooms in this hall
        $query =   "SELECT DISTINCT COUNT(hms_room.id) FROM hms_room
                    JOIN hms_bed ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                    AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_floor.id = {$this->id}
                    AND hms_room.gender_type = $gender
                    AND hms_room.is_reserved = 0
                    AND hms_room.is_online = 1
                    AND hms_room.private_room = 0
                    AND hms_room.ra_room = 0
                    AND hms_room.is_overflow = 0
                    AND hms_floor.rlc_id IS NULL";

        $avail_rooms = PHPWS_DB::getOne($query);
        if(PEAR::isError($avail_rooms)){
            PHPWS_Error::log($avail_rooms);
            return FALSE;
        }

        return $avail_rooms;
    }

    public function get_avail_lottery_rooms()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');

        $now = mktime();

        $query =   "SELECT DISTINCT hms_room.* FROM hms_room
                    JOIN hms_bed ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                    AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term}))
                    AND hms_floor.id = {$this->id}
                    AND hms_room.is_reserved = 0
                    AND hms_room.is_online = 1
                    AND hms_room.private_room = 0
                    AND hms_room.ra_room = 0
                    AND hms_room.is_overflow = 0
                    AND hms_floor.rlc_id IS NULL";

        $avail_rooms = PHPWS_DB::getAll($query);
        if(PEAR::isError($avail_rooms)){
            PHPWS_Error::log($avail_rooms);
            return FALSE;
        }

        $output_list = array();

        foreach($avail_rooms as $room){
            $obj = new HMS_Room();
            PHPWS_Core::plugObject($obj, $room);
            $output_list[] = $obj;
        }

        return $output_list;
    }

    public function count_lottery_used_rooms()
    {
        $now = mktime();

        $query = "SELECT count(hms_room.*) FROM hms_room 
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       AND hms_floor.id = {$this->id} AND
                       hms_room.id IN (SELECT DISTINCT hms_room.id FROM hms_room
                       JOIN hms_bed ON hms_bed.room_id = hms_room.id
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       WHERE (hms_bed.id IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                       OR hms_bed.id IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term} and lottery = 1))
                       AND hms_floor.id = {$this->id})";

        $used_rooms = PHPWS_DB::getOne($query);
        if(PEAR::isError($used_rooms)){
            PHPWS_Error::log($used_rooms);
            return FALSE;
        }

        return $used_rooms;
    }

    public function count_lottery_full_rooms()
    {
        $now = mktime();

        # Get the number of rooms in this hall which have every bed either assigned or reserved through the lottery.
        $query      = "SELECT count(hms_room.*) FROM hms_room 
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       AND hms_floor.id = {$this->id} AND
                       hms_room.id NOT IN (SELECT DISTINCT hms_room.id FROM hms_room
                       JOIN hms_bed ON hms_bed.room_id = hms_room.id
                       JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                       WHERE (hms_bed.id NOT IN (SELECT bed_id FROM hms_lottery_reservation WHERE term = {$this->term} AND expires_on > $now)
                       AND hms_bed.id NOT IN (SELECT bed_id FROM hms_assignment WHERE term = {$this->term} and lottery = 1))
                       AND hms_floor.id = {$this->id})";

        $used_rooms = PHPWS_DB::getOne($query);
        if(PEAR::isError($used_rooms)){
            PHPWS_Error::log($used_rooms);
            return FALSE;
        }

        return $used_rooms;
    }

    /**
     * Main Method
     */
    public function main()
    {
        if( !Current_User::allow('hms', 'floor_structure') 
            && !Current_User::allow('hms', 'floor_attributes')
            && !Current_User::allow('hms', 'floor_view') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        switch($_REQUEST['op'])
        {
            case 'show_select_floor':
                return HMS_Floor::show_select_floor('Edit Floor', 'floor', 'show_edit_floor');
                break;
            case 'show_edit_floor':
                return HMS_Floor::show_edit_floor();
                break;
            case 'edit_floor':
                return HMS_Floor::edit_floor();
            case 'show_add_room':
                PHPWS_Core::initModClass('hms','HMS_Room.php');
                return HMS_Room::show_add_room($_REQUEST['hall_id'],$_REQUEST['floor_id']);
            case 'delete_room':
                return HMS_Floor::delete_room();default:
                echo "Undefined floor op: {$_REQUEST['op']}";
                break;
        }
    }

    /******************
     * Static Methods *
     *****************/

    public function get_pager_by_hall($hall_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        
        $pager = & new DBPager('hms_floor', 'HMS_Floor');
        
        $pager->addWhere('hms_floor.residence_hall_id', $hall_id);
        $pager->db->addOrder('hms_floor.floor_number');

        $page_tags['TABLE_TITLE']       = 'Floors in this hall';
        $page_tags['FLOOR_NUM_LABEL']   = 'Floor #';
        $page_tags['GENDER_LABEL']      = 'Gender';
        $page_tags['ONLINE_LABEL']      = 'Online';

        $pager->setModule('hms');
        $pager->setTemplate('admin/floor_pager_by_hall.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No floors found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('get_pager_by_hall_tags');
        $pager->addPageTags($page_tags);

        return $pager->get();
    }

    public function get_pager_by_hall_tags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        $tpl['FLOOR_NUMBER']   = PHPWS_Text::secureLink($this->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$this->id));
        $tpl['GENDER_TYPE'] = HMS_Util::formatGender($this->gender_type);
        $tpl['IS_ONLINE']   = $this->is_online ? 'Yes' : 'No';

        return $tpl;
    }

    public function edit_floor()
    {
       if( !Current_User::allow('hms', 'floor_attributes') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
       }

       # Create the floor object gien the floor id
       $floor = new HMS_Floor($_REQUEST['floor_id']);
       if(!$floor){
           return show_select_floor('Edit Floor', 'floor', 'show_edit_floor', NULL, 'Error: The selected floor does not exist.');
       }

       # Compare the floor's gender and the gender the user selected
       # If they're not equal, call 'can_change_gender' public function
       if($floor->gender_type != $_REQUEST['gender_type']){
           if(!$floor->can_change_gender($_REQUEST['gender_type'])){
               return HMS_Floor::show_edit_floor($floor->id, NULL, 'Error: Incompatible genders detected. No changes were made.');
           }
       }

       # Grab all the input from the form and save the floor
       $floor->gender_type = $_REQUEST['gender_type'];
       $floor->is_online = isset($_REQUEST['is_online']) ? 1 : 0;
       $floor->f_movein_time_id = $_REQUEST['f_movein_time'];
       $floor->t_movein_time_id = $_REQUEST['t_movein_time'];
       $floor->rt_movein_time_id = $_REQUEST['rt_movein_time'];
       $floor->floor_plan_image_id = $_REQUEST['floor_plan_image_id'];

       if($_REQUEST['f_movein_time'] == 0){
           $floor->f_movein_time_id = NULL;
       }else{
           $floor->f_movein_time_id = $_REQUEST['f_movein_time'];
       }

        if($_REQUEST['t_movein_time'] == 0){
           $floor->t_movein_time_id = NULL;
       }else{
           $floor->t_movein_time_id = $_REQUEST['t_movein_time'];
       }


       if($_REQUEST['rt_movein_time'] == 0){
           $floor->rt_movein_time_id = NULL;
       }else{
           $floor->rt_movein_time_id = $_REQUEST['rt_movein_time'];
       }

       if($_REQUEST['floor_rlc_id'] == 0){
           $floor->rlc_id = NULL;
       }else{
           $floor->rlc_id = $_REQUEST['floor_rlc_id'];
       }

       $result = $floor->save();

       if(!$result || PHPWS_Error::logIfError($result)){
           return HMS_Floor::show_edit_floor($floor->id, NULL, 'Error: There was a problem saving the floor. No changes were made. Please contact ESS.');
       }

       return HMS_Floor::show_edit_floor($floor->id, 'Floor update successfully.');
    }

    public function delete_room() {
        PHPWS_Core::initModClass('hms','HMS_Room.php');

        if(isset($_REQUEST['room'])) {
            if(HMS_Room::delete_room($_REQUEST['room'])) {
                return HMS_Floor::show_edit_floor($_REQUEST['floor'],'Room Successfully Deleted');
            }else{
                return HMS_Floor::show_edit_floor($_REQUEST['floor'],'Room Could Not Be Deleted');
            }
        }
        return HMS_Floor::show_edit_floor($_REQUEST['floor']);
    }
    /**************
     * UI Methods *
     *************/
    public function show_select_floor($title, $type, $op, $success = NULL, $error = NULL)
    {
        if(   !Current_User::allow('hms', 'floor_view')
           && !Current_User::allow('hms', 'floor_attributes')
           && !Current_User::allow('hms', 'floor_structure'))
        {
            $tpl = array();
            echo(PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl'));
            exit();
        }

        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initCoreClass('Form.php');

        javascript('/modules/hms/select_floor');
        
        $tpl = array();

        # Setup the title and color of the title bar
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl['TITLE']       = $title . ' - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Get the halls for the selected term
        $halls = HMS_Residence_Hall::get_halls_array(HMS_Term::get_selected_term());

        # Show an error if there are no halls for the current term
        if($halls == NULL){
            $tpl['ERROR_MSG'] = 'Error: No halls exist for the selected term. Please create a hall first.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/select_room.tpl');
        }

        $halls[0] = 'Select...';

        $tpl['MESSAGE'] = 'Please select a floor: ';

        # Setup the form
        $form = &new PHPWS_Form;
        $form->setMethod('get');
        $form->addDropBox('residence_hall', $halls);
        $form->setLabel('residence_hall', 'Residence hall: ');
        $form->setMatch('residence_hall', 0);
        $form->setExtra('residence_hall', 'onChange="handle_hall_change()"');

        $form->addDropBox('floor', array(0 => ''));
        $form->setLabel('floor', 'Floor: ');
        $form->setExtra('floor', 'onChange="handle_floor_change()" disabled');

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
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/select_floor.tpl');
    }

    public function show_edit_floor($floor_id = NULL, $success = null, $error = null)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        
        # Determine the floor id. If the passed in variable is NULL,
        # then use the $_REQUEST
        if(!isset($floor_id)){
            $floor_id = $_REQUEST['floor'];
        }

        # Setup the title and color of the title bar
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl['TITLE'] = 'Edit Floor - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Create the floor object given the floor_id
        $floor = new HMS_Floor($floor_id);
        if(!$floor){
            return HMS_Floor::show_select_floor('Edit Floor', 'floor', 'show_select_floor', NULL, 'Error: The select floor does not exist!');
        }

        # Create the parent object
        $hall = $floor->get_parent();
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_floor.tpl');
        }

        $form = &new PHPWS_Form;
        
        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$hall->id));
        $tpl['FLOOR_NUMBER']        = $floor->floor_number;
        $tpl['NUMBER_OF_ROOMS']     = $floor->get_number_of_rooms();
        $tpl['NUMBER_OF_BEDS']      = $floor->get_number_of_beds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $floor->get_number_of_assignees();

        $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('gender_type', $floor->gender_type);
        
        $form->addCheck('is_online', 1);
        $form->setMatch('is_online', $floor->is_online);

        $movein_times = HMS_Movein_Time::get_movein_times_array();

        $form->addDropBox('f_movein_time', $movein_times);
        if(!isset($floor->f_movein_time_id)){
            $form->setMatch('f_movein_time', 0);
        }else{
            $form->setMatch('f_movein_time', $floor->f_movein_time_id);
        }

        $form->addDropBox('t_movein_time', $movein_times);
        if(!isset($floor->t_movein_time_id)){
            $form->setMatch('t_movein_time', 0);
        }else{
            $form->setMatch('t_movein_time', $floor->t_movein_time_id);
        }
        
        $form->addDropBox('rt_movein_time', $movein_times);
        if(!isset($floor->rt_movein_time_id)){
            $form->setMatch('rt_movein_time', 0);
        }else{
            $form->setMatch('rt_movein_time', $floor->rt_movein_time_id);
        }

        # Get a list of the RLCs indexed by id
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $learning_communities = HMS_Learning_Community::getRLCList();
        $learning_communities[0] = 'None';

        $form->addDropBox('floor_rlc_id', $learning_communities);
        if(isset($floor->rlc_id)){
            $form->setMatch('floor_rlc_id', $floor->rlc_id);
        }else{
            $form->setMatch('floor_rlc_id', 0);
        }

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        if(isset($floor->floor_plan_image_id)){
            $manager = Cabinet::fileManager('floor_plan_image_id', $floor->floor_plan_image_id);
        }else{
            $manager = Cabinet::fileManager('floor_plan_image_id');
        }
        $manager->maxImageWidth(300);
        $manager->maxImageHeight(300);
        $manager->imageOnly(false, false);
        $form->addTplTag('FILE_MANAGER', $manager->get());

        $form->addHidden('type', 'floor');
        $form->addHidden('op', 'edit_floor');
        $form->addHidden('floor_id', $floor->id);

        $form->addSubmit('submit_form', 'Submit');

        $tpl['STATIC_ROOM_PAGER'] = HMS_Room::room_pager_by_floor($floor->id);
        $tpl['DYNAMIC_ROOM_PAGER'] = HMS_Room::room_pager_by_floor($floor->id, true);
        
        if(Current_User::allow('hms','room_structure')) {
            $tpl['ADD_LINK'] = PHPWS_Text::secureLink('Add Room','hms',array('type'=>'floor','op'=>'show_add_room','floor_id'=>$floor->id,'hall_id'=>$hall->id));
        }

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }
        
        # if the user has permission to view the form but not edit it then
        # disable it
        if(   Current_User::allow('hms', 'floor_view') 
           && !Current_User::allow('hms', 'floor_attributes')
           && !Current_User::allow('hms', 'floor_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_floor.tpl');
    }
}
?>
