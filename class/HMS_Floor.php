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
    var $ft_movein_time_id;
    var $rt_movein_time_id;

    /**
     * List of rooms associated with this floor
     * @vary array
     */
    var $_rooms     = null;

    /**
     * List of suites associated with this floor
     * @var array
     */
    var $_suites    = null;

    /**
     * Holds the parent residence hall object of this floor
     */
    var $_hall      = null;
    
    /**
     * Constructor
     */
    function HMS_Floor($id = 0)
    {
        $this->construct($id, 'hms_floor');
    }

    /********************
     * Instance Methods *
     *******************/

    /*
     * Saves a new or updated floor hall object
     */
    function save()
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
     * 'this' floor's rooms/suites
     *
     * Setting $assignments to 'TRUE' causes the copy function to copy
     * the assignments as well as the hall structure.
     *
     * @return bool False if unsuccessful.
     */
    function copy($to_term, $hall_id, $assignments = FALSE)
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

        if(!$new_floor->save()) {
            // There was an error saving the new floor
            echo "error saving a copy of this floor";
            return false;
        }

        // Save successful, create suites

        //echo "loading suites<br>";

        // Load all the suites for this floor
        if(empty($this->_suites)) {
            if($this->loadSuites() === FALSE) {
                // There was an error loading the suites
                echo "error loading suites";
                test($this);
                return false;
            }
        }

        /**
         * Suites exist. Start making copies.
         * Note: No further copying is needed at the suite level!
         */

        if(!empty($this->_suites)) {
            foreach ($this->_suites as $suite) {
                $result = $suite->copy($to_term, $new_floor->id, $assignments);
                // What if bad result?
                test($result);
                test($suite);
                if(!$result){
                    return false;
                    echo "error copying suite";
                }
            }
        }else{
            //echo "No suites to copy<br>";
        }

        // Load all the rooms for this floor which are not in suites
        if(empty($this->_rooms)) {
            $result = $this->loadRooms(0, 0);
            if(!$result) {
                // There was an error loading the rooms
                echo "There was an error loading the rooms";
                test($this);
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
    function loadHall()
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
     * Pulls all the suites associated with this floor and stores
     * them in the _suites variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     *
     */
    function loadSuites($deleted=0)
    {
        $db = new PHPWS_DB('hms_suite');
        $db->addWhere('floor_id', $this->id);

        switch ($deleted) {
            case -1:
                $db->addWhere('deleted', 1);
                break;
            case 0:
                $db->addWhere('deleted', 0);
                break;
        }

        $db->loadClass('hms', 'HMS_Suite.php');
        $result = $db->getObjects('HMS_Suite');
        if (PHPWS_Error::logIfError($result)) {
            return false;
        } else {
            $this->_suites = & $result;
            return true;
        }
    }

    /**
     * Pulls all the rooms associated with this floor and stores
     * them in the _room variable.
     * @param int deleted -1 deleted only, 0 not deleted only, 1 all
     * @param int suites  -1 suites only, 0 no suites only, 1 all rooms
     */
    function loadRooms($deleted = 0, $suites=1)
    {

        $db = new PHPWS_DB('hms_room');
        $db->addWhere('floor_id', $this->id);
        $db->addOrder('room_number', 'ASC');
        switch ($deleted) {
            case -1:
                $db->addWhere('deleted', 1);
                break;
            case 0:
                $db->addWhere('deleted', 0);
                break;
        }

        switch ($suites) {
            case -1:
                $db->addWhere('suite_id', 0, '>');
                break;
            case 0:
                $db->addWhere('suite_id', NULL, 'IS NULL');
                break;
        }

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
     * Creates the rooms, bedrooms, and beds for a new floor
     */
    function create_child_objects($rooms_per_floor, $bedrooms_per_room, $beds_per_bedroom)
    {
        for ($i = 0; $i < $rooms_per_floor; $i++) {
            $room = new HMS_Room;

            $room->floor_id     = $this->id;
            $room->term         = $this->term;
            $room->gender_type  = $this->gender_type;

            if($room->save()) {
                $room->create_child_objects($bedrooms_per_room, $beds_per_bedroom);
            } else {
                // Decide on bad Result.
            }
        }
    }

    /*
     * Returns TRUE or FALSE. The gender of a floor can only be changed to the
     * target gender if all rooms can be changed to the target gender.
     *
     * Additionally, the floor's gender can only be changed if the target
     * gender will be consistent with the gender of the hall of which
     * this floor is a part.
     *
     * This function checks to make sure all rooms can be changed,
     * those rooms in tern check all thier bedrooms, and so on.
     *
     * In the case that we're attempting to change the gender of just
     * 'this' floor, set $ignore_upper to TRUE to avoid checking the
     * parent hall's gender.
     * TODO: rewrite this because the behavior changed
     */
    function can_change_gender($target_gender, $ignore_upper = FALSE)
    {
        # Ignore upper is true, we're trying to change a hall/floor
        if($ignore_upper){
            # If ignore upper is true and the target gender is coed, then
            # we can always return true.
            if($target_gender == COED){
                return true;
            }

            # If the target gender is not the same, and someone is assigned
            # here, then the gender can't be changed
            # TODO: make this check for males/females on the floor
            #       and allow for gender changes if everyone assigned
            #       is of the target gender.
            if(($target_gener != $this->gender_type) && ($this->get_number_of_assignees() != 0)){
                return false;
            }
        }else{
            # Ignore upper is FALSE, load the hall and compare

            if(!$this->loadHall()){
                // an error occured loading the hall
                return false;
            }

            # If the hall is not coed and the gt is not the target, then return false
            if($this->_hall->gender_type != COED && $this->_hall->gender_type != $target_gender) {
                return false;
            }

            return true;
        }
    }

    /*
     * Returns the number of rooms on the current floor
     */
    function get_number_of_rooms()
    {
        $db = &new PHPWS_DB('hms_room');

        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');

        $db->addWhere('hms_room.deleted', 0);
        $db->addWhere('hms_floor.deleted', 0);

        $db->addWhere('hms_floor.id', $this->id);

        $result = $db->select('count');

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of suites on the current floor
     */
    function get_number_of_suites()
    {
        $db = &new PHPWS_DB('hms_suite');

        $db->addJoin('LEFT OUTER', 'hms_suite', 'hms_floor', 'floor_id', 'id');

        $db->addWhere('hms_suite.deleted', 0);
        $db->addWhere('hms_floor.deleted', 0);

        $db->addWhere('hms_floor.id', $this->id);
        
        $result = $db->select('count');

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /*
     * Returns the number of bedrooms on the current floor
     */
    function get_number_of_bedrooms()
    {
        $db = &new PHPWS_DB('hms_bedroom');
        
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        
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
    function get_number_of_beds()
    {
        $db = &new PHPWS_DB('hms_bed');
        
        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_bedroom',        'bedroom_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        
        $db->addWhere('hms_bed.deleted',            0);
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        
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
    function get_number_of_assignees()
    {
        $db = &new PHPWS_DB('hms_assignment');
        
        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed',            'bed_id',            'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',     'hms_bedroom',        'bedroom_id',        'id');
        $db->addJoin('LEFT OUTER', 'hms_bedroom', 'hms_room',           'room_id',           'id');
        $db->addJoin('LEFT OUTER', 'hms_room',    'hms_floor',          'floor_id',          'id');
        
        $db->addWhere('hms_assignment.deleted',     0);
        $db->addWhere('hms_bed.deleted',            0);
        $db->addWhere('hms_bedroom.deleted',        0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        
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
    function get_parent()
    {
        $this->loadHall();
        return $this->_hall;
    }

    /*
     * Returns an array of the rooms on the current floor
     */
    function get_rooms()
    {
        if (!$this->loadRooms()) {
            return false;
        }

        return $this->_rooms;
    }

    /**
     * Returns an array of the suites on the current floor
     */
    function get_suites()
    {
        if(!$this->loadSuites()) {
            return false;
        }

        return $this->_suites;
    }

    /**
     * Returns an array of the bedrooms on the current floor
     */
    function get_bedrooms()
    {
        $bedrooms = array();
        
        if (!$this->loadRooms()){
            return false;
        }

        foreach($this->_rooms as $room){
            $room_bedrooms = $room->get_bedrooms();
            $bedrooms = array_merge($bedrooms, $room_bedrooms);
        }
        return $bedrooms;
    }

    /**
     * Returns an array of the beds on the current floor
     */
    function get_beds()
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
    function get_assignees()
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
    function has_vacancy()
    {
        if($this->get_number_of_assignees() < $this->get_number_of_beds()){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Returns an array of room objects on this floor that have vacancies
     */
    function get_rooms_with_vacancies()
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

    /**
     * Main Method
     */
    function main()
    {
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
            default:
                echo "Undefined room op: {$_REQUEST['op']}";
                break;
        }
    }

    /******************
     * Static Methods *
     *****************/

    function floor_pager()
    {

    }

    function get_row_tags()
    {

    }

    function edit_floor()
    {
       # Create the floor object gien the floor id
       $floor = new HMS_Floor($_REQUEST['floor_id']);
       if(!$floor){
           return show_select_floor('Edit Floor', 'floor', 'show_edit_floor', NULL, 'Error: The selected floor does not exist.');
       }

       # Compare the floor's gender and the gender the user selected
       # If they're not equal, call 'can_change_gender' function
       if($floor->gender_type != $_REQUEST['gender_type']){
           if(!$floor->can_change_gender($_REQUEST['gender_type'])){
               return HMS_Floor::show_edit_floor($floor->id, NULL, 'Error: Incompatible genders detected. No changes were made.');
           }
       }

       # Grab all the input from the form and save the floor
       $floor->gender_type = $_REQUEST['gender_type'];
       if(isset($_REQUEST['is_online'])) $floor->is_online = 1;
       $floor->ft_movein_time_id = $_REQUEST['ft_movein_time'];
       $floor->rt_movein_time_id = $_REQUEST['rt_movein_time'];

       $result = $floor->save();

       if(!$result || PHPWS_Error::logIfError($result)){
           return HMS_Floor::show_edit_floor($floor->id, NULL, 'Error: There was a problem saving the floor. No changes were made. Please contact ESS.');
       }

       return HMS_Floor::show_edit_floor($floor->id, 'Floor Update successfully.');
    }

    /**************
     * UI Methods *
     *************/
    function show_select_floor($title, $type, $op, $success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initCoreClass('Form.php');

        javascript('/modules/hms/select_floor');
        
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
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/select_floor.tpl');
    }

    function show_edit_floor($floor_id = NULL, $success = null, $error = null)
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
        $tpl['TITLE'] = 'Edit Floor';
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
        
        $tpl['HALL_NAME']           = $hall->hall_name;
        $tpl['FLOOR_NUMBER']        = $floor->floor_number;
        $tpl['NUMBER_OF_ROOMS']     = $floor->get_number_of_rooms();
        $tpl['NUMBER_OF_BEDS']      = $floor->get_number_of_beds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $floor->get_number_of_assignees();

        $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('gender_type', $floor->gender_type);
        
        $form->addCheck('is_online', 1);
        //$form->setLabel('is_online', array(_('No'), _('Yes') ));
        $form->setMatch('is_online', $floor->is_online);

        $form->addDropBox('ft_movein_time', HMS_Movein_Time::get_movein_times_array());
        $form->setMatch('ft_movein_time', $floor->ft_movein_time_id);

        $form->addDropBox('rt_movein_time', HMS_Movein_time::get_movein_times_array());
        $form->setMatch('rt_movein_ime', $floor->rt_movein_time_id);

        $form->addHidden('type', 'floor');
        $form->addHidden('op', 'edit_floor');
        $form->addHidden('floor_id', $floor->id);

        $form->addSubmit('submit_form', 'Submit');

        $tpl['ROOM_PAGER'] = HMS_Room::room_pager_by_floor($floor->id);
        
        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_floor.tpl');
    }
}
?>
