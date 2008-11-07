<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Bed extends HMS_Item {
    
    var $room_id            = 0;
    var $bed_letter         = null;
    var $banner_id          = null;
    var $phone_number       = null;
    var $bedroom_label      = null;
    var $ra_bed             = null;
    var $_curr_assignment   = null;

    /**
     * Holds the parent room object of this bed.
     */
    var $_room;

    function HMS_Bed($id = 0)
    {
        $this->construct($id, 'hms_bed');
        //test($this);
    }

    function copy($to_term, $room_id, $assignments)
    {
        if (!$this->id) {
            return false;
        }

        //echo "in hms_beds, making a copy of this bed<br>";
        
        $new_bed = clone($this);
        $new_bed->reset();
        $new_bed->term    = $to_term;
        $new_bed->room_id = $room_id;
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

    function get_banner_building_code()
    {
        $room = $this->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        return $building->banner_building_code;
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
        $db->addWhere('term', $this->term);
        $db->loadClass('hms', 'HMS_Assignment.php');
        $result = $db->getObjects('HMS_Assignment');
        
        if(PEAR::isError($result)){
            PHPWS_Error::logIfError($result);
            return false;
        }else if($result == null){
            return true;
        }elseif(sizeof($result) > 1){
            PHPWS_Error::log(HMS_MULTIPLE_ASSIGNMENTS, 'hms', 'HMS_Bed::loadAssignment',sprintf('A=%s,B=%s', $ass->id, $this->id));
            return false;
        }else{
            $this->_curr_assignment = $result[0];
            return TRUE;
        }
    }

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

    function get_parent()
    {
        if(!$this->loadRoom()){
            return false;
        }

        return $this->_room;
    }

    function get_number_of_assignees()
    {
        $this->loadAssignment();
        return (bool)$this->_curr_assignment ? 1 : 0;
    }

    function get_assignee()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        
        if(!$this->loadAssignment()){
            return false;
        }

        if(!isset($this->_curr_assignment->asu_username)){
            return NULL;
        }else{
            return new HMS_Student($this->_curr_assignment->asu_username);
        }
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

    /**
     * Returns a string like "Justice Hall Room 110"
     */
    function where_am_i($link = FALSE)
    {
        if(!$this->loadRoom()){
            return null;
        }

        $room = $this->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        $text = $building->hall_name . ' Room ' . $room->room_number;

        if($link){
            return PHPWS_Text::secureLink($text, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$room->id));
        }else{
            return $text;
        }
    }

    /**
     * Returns a link. If the bed is assigned, the link is to the
     * student info screen. Otherwise, the link is to the
     * assign student screen.
     */
    function get_assigned_to_link($newWindow = FALSE)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        
        if($this->loadAssignment() === false){
            $tpl['ERROR_MSG'] = 'There was an error loading the assignmnet. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }
        
        if(isset($this->_curr_assignment)){
            $link_re = '';
            $link_un = '';
            if(Current_User::allow('hms','assignment_maintenance')) {
                $link_re = PHPWS_Text::secureLink('(re-assign)', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$this->_curr_assignment->asu_username, 'bed_id'=>$this->id));
                $link_un = PHPWS_Text::secureLink('(un-assign)', 'hms', array('type'=>'assignment', 'op'=>'show_unassign_student', 'username'=>$this->_curr_assignment->asu_username));
            }
            return PHPWS_Text::secureLink(HMS_SOAP::get_full_name($this->_curr_assignment->asu_username),'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$this->_curr_assignment->asu_username)) . ' ' . $link_re . ' ' . $link_un;
        }else{
            $link = '&lt;unassigned&gt';
            if($newWindow){
                if(Current_User::allow('hms','assignment_maintenance')) {
                    $link = PHPWS_Text::secureLink('&lt;unassigned&gt;', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'bed_id'=>$this->id),'index');
                }
                return '<span class="unassigned_link">' . $link . '</span>';
            }else{
                if(Current_User::allow('hms','assignment_maintenance')) {
                    $link = PHPWS_Text::secureLink('&lt;unassigned&gt;', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'bed_id'=>$this->id));
                }
                return '<span class="unassigned_link">' . $link . '</span>';
            }
        }

    }

    function getPagerByRoomTags()
    {
        $tags['BEDROOM']        = $this->bedroom_label;
        $tags['BED_LETTER']     = PHPWS_Text::secureLink($this->bed_letter, 'hms', array('type'=>'bed', 'op'=>'show_edit_bed', 'bed'=>$this->id));
        $tags['ASSIGNED_TO']    = $this->get_assigned_to_link();
        $tags['RA']             = $this->ra_bed ? 'Yes' : 'No';

        $this->loadAssignment();
        if($this->_curr_assignment == NULL && Current_User::allow('hms', 'bed_structure')){
            $confirm = array();
            $confirm['QUESTION']    = 'Are you sure want to delete bed ' .  $this->bed_letter . '?';
            $confirm['ADDRESS']     = PHPWS_Text::linkAddress('hms', array('type'=>'bed', 'op'=>'delete_bed_submit', 'bed_id'=>$this->id, 'room_id'=>$this->room_id));
            $confirm['LINK']        = 'Delete';
            $tags['DELETE']         = Layout::getJavascript('confirm', $confirm);
        }

        return $tags;
    }

    /**
     * Returns TRUE if the room can be administratively assigned, FALSE otherwise
     */
    function canAssignHere()
    {
        # Make sure this bed isn't already assigned
        $this->loadAssignment();
        if($this->_curr_assignment != NULL && $this->_curr_assignment > 0)
            return FALSE;
        
        # Get all of the parent objects
        $room       = $this->get_parent();
        $floor      = $room->get_parent();
        $building   = $floor->get_parent();
                
        # Check if everything is online
        if($room->is_online == 1)
            return FALSE;

        if($floor->is_online == 1)
            return FALSE;

        if($building->is_online == 1)
            return FALSE;
        
        return TRUE;
    }

    /**
     * Returns TRUE if the room can be auto-assigned
     */
    function canAutoAssignHere()
    {  
        # Make sure this bed isn't already assigned
        $this->loadAssignment();
        if($this->_curr_assignment != NULL && $this->_curr_assignment > 0)
            return FALSE;

        # Get all of the parent objects
        $room       = $this->get_parent();
        $floor      = $room->get_parent();
        $building   = $floor->get_parent();

        # Check if everything is online
        if($room->is_online == 1)
            return FALSE;

        if($floor->is_online == 1)
            return FALSE;

        if($building->is_online == 1)
            return FALSE;

        # Make sure nothing is reserved
        if($room->is_reserved == 1)
            return FALSE;

        # Make sure the room isn't medical reserved
        if($room->is_medical == 1)
            return FALSE;

        # Make sure the room isn't a lobby
        if($room->is_overflow == 1)
            return FALSE;

        # Make sure the room isn't private
        if($room->private_room == 1)
            return FALSE;

        # Check if this bed is part of an RLC
        if($floor->rlc_id != NULL)
            return FALSE;

        return TRUE;
    }

    function is_lottery_reserved()
    {
        $db = &new PHPWS_DB('hms_lottery_reservation');
        $db->addWhere('bed_id', $this->id);
        $db->addWhere('term', $this->term);
        $db->addWhere('expires_on', mktime(), '>');
        $result = $db->select('count');
        
        # TODO: error checking here

        if($result > 0){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function get_lottery_reservation_info()
    {
        $db = &new PHPWS_DB('hms_lottery_reservation');
        $db->addWhere('bed_id', $this->id);
        $db->addWhere('term', $this->term);
        $db->addWhere('expires_on', mktime(), '>');
        $result = $db->select('row');

        #TODO: error checking here

        return $result;
    }

    function lottery_reserve($username, $requestor, $timestamp)
    {
        if($this->is_lottery_reserved()){
            return FALSE;
        }

        $db = &new PHPWS_DB('hms_lottery_reservation');
        $db->addValue('asu_username', $username);
        $db->addValue('requestor', $requestor);
        $db->addValue('term', $this->term);
        $db->addValue('bed_id', $this->id);
        $db->addValue('expires_on', $timestamp);
        $result = $db->insert();

        if(!$result || PHPWS_Error::logIfError($result)){
            return FALSE;
        }else{
            return TRUE;
        }
    }


    /******************
     * Static Methods *
     ******************/

    function main()
    {
        if( !Current_User::allow('hms', 'bed_structure') && !Current_User::allow('hms', 'bed_attributes') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        switch($_REQUEST['op'])
        {
            case 'select_bed_to_edit':
                return HMS_Bed::show_select_bed('Edit Bed', 'bed', 'show_edit_bed');
                break;
            case 'show_edit_bed':
                return HMS_Bed::show_edit_bed();
                break;
            case 'edit_bed':
                return HMS_Bed::edit_bed();
                break;
            case 'show_add_bed':
                return HMS_Bed::show_add_bed();
                break;
            case 'add_bed_submit':
                return HMS_Bed::add_bed_submit();
                break;
            case 'delete_bed_submit':
                return HMS_Bed::delete_bed_submit();
                break;
            default:
                echo "undefined bed op: {$_REQUEST['op']}";
                break;
        }
    }
    
    /**
     * This function is depricated, see get_all_free_beds() below
     * 
    function get_all_empty_beds($init = FALSE)
    {
        $sql = "
            SELECT
                hms_bed.*,
                hms_room.gender_type,
                hms_residence_hall.banner_building_code
            FROM 
                hms_bed
                JOIN hms_room           ON hms_bed.room_id         = hms_room.id
                JOIN hms_floor          ON hms_room.floor_id           = hms_floor.id
                JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                LEFT OUTER JOIN hms_assignment ON hms_bed.id = hms_assignment.bed_id
            WHERE
                hms_room.is_online           = 1 AND
                hms_room.is_reserved         = 0 AND
                hms_room.is_medical          = 0 AND
                hms_room.ra_room             = 0 AND
                hms_room.private_room        = 0 AND
                hms_floor.is_online          = 1 AND
                hms_residence_hall.is_online = 1 AND
                hms_assignment.asu_username IS NULL
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
    */

    /**
     * Returns an array of IDs of free beds (which can be auto_assigned)
     * Returns FALSE if there are no more free beds
     */
    function get_all_free_beds($term, $gender, $randomize = FALSE, $banner = FALSE)
    {
        $db = &new PHPWS_DB('hms_bed');

        if($banner) {
            $db->addColumn('hms_bed.banner_id');
            $db->addColumn('hms_residence_hall.banner_building_code');
        }
        $db->addColumn('id');

        // Only get free beds
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addWhere('hms_assignment.asu_username', NULL);

        // Join other tables so we can do the other 'assignable' checks
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        // Term
        $db->addWhere('hms_bed.term', $term);

        // Gender
        $db->addWhere('hms_room.gender_type', $gender);

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

        // Randomize if necessary
        if($randomize) {
            $db->addOrder('random');
        }

        $db->setTestMode();

        if($banner) {
            $result = $db->select();
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }

            return $result;
        }   
        
        $result = $db->select('col');

        // In case of an error, log it and return it
        if(PHPWS_Error::logIfError($result)){
            return $result;
        }

        // Return FALSE if there were no results
        if(sizeof($result) <= 0){
            return FALSE;
        }

        return $result;
    }
    
     /**
     * Returns the ID of a free bed (which can be auto-assigned)
     * Returns FALSE if there are no more free beds
     */
    function get_free_bed($term, $gender, $randomize = FALSE)
    {
        // Get the list of all free beds
        $beds = HMS_Bed::get_all_free_beds($term, $gender);
        
        // Check for db errors
        if(PEAR::isError($beds)){
            return $beds;
        }
        
        // Check for no results (i.e. no empty beds), return false
        if($beds == FALSE){
            return FALSE;
        }

        if($randomize){
            // Get a random index between 0 and the max array index (size - 1)
            $random_index = mt_rand(0, sizeof($beds)-1);
            return $beds[$random_index];
        }else{
            return $beds[0];
        }
    }   

    function edit_bed()
    {
        if( !Current_User::allow('hms', 'bed_attributes') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        # Create the bed object given the bed_id
        $bed = new HMS_Bed($_REQUEST['bed_id']);
        if(!$bed){
            return show_select_bed('Edit Bed', 'bed', 'show_edit_bed', null, 'Error: The selected bed does not exist!');
        }

        $bed->bedroom_label = $_REQUEST['bedroom_label'];
        $bed->phone_number  = $_REQUEST['phone_number'];
        $bed->banner_id     = $_REQUEST['banner_id'];
        
        if(isset($_REQUEST['ra_bed'])){
            $bed->ra_bed = 1;
        }else{
            $bed->ra_bed = 0;
        }

        $result = $bed->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Bed::show_edit_bed($bed->id, NULL, 'Error: There was a problem while saving the bed. No changes were made');
        }

        return HMS_Bed::show_edit_bed($bed->id, 'Bed updated successfully.');
    }

    /*
     * Adds a new bed to the specified room_id
     * 
     * The 'ra_bed' flag is expected to be either TRUE or FALSE.
     * @return TRUE for success, FALSE otherwise
     */
    function add_bed($room_id, $bed_letter, $bedroom_label, $phone_number, $banner_id, $ra_bed = FALSE)
    {
        # Check permissions
        if(!Current_User::allow('hms', 'bed_structure')){
            return false;
        }

        # Load the room (need the 'term' field)
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $room = new HMS_Room($room_id);
        
        # Create a new bed object
        $bed = new HMS_Bed();

        $bed->room_id       = $room_id;
        $bed->term          = $room->term;
        $bed->bed_letter    = $bed_letter;
        $bed->bedroom_label = $bedroom_label;
        $bed->banner_id     = $banner_id;
        $bed->phone_number  = $phone_number;

        if($ra_bed === TRUE){
            $bed->ra_bed = 1;
        }else if($ra_bed === FALSE){
            $bed->ra_bed = 0;
        }else{
            return false;
        }

        $result = $bed->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }

    function delete_bed($bed_id)
    {
        if(!Current_User::allow('hms', 'bed_structure')){
            return false;
        }

        # Create the bed object
        $bed = new HMS_Bed($bed_id);

        # Make sure the bed isn't assigned to anyone
        $bed->loadAssignment();

        if($bed->_curr_assignment != NULL){
            return false;
        }

        $result = $bed->delete();

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }

    /*********************
     * Static UI Methods *
     *********************/
     
    function show_select_bed($title, $type, $op, $success = NULL, $error = NULL)
    {
        if(   !Current_User::allow('hms', 'bed_view') 
           && !Current_User::allow('hms', 'bed_attributes')
           && !Current_User::allow('hms', 'bed_structure'))
        {
            $tpl = array();
            echo(PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl'));
            exit();
        }

        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initCoreClass('Form.php');

        javascript('/modules/hms/select_bed');

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
            return PHPWS_Template::process($tpl, 'hms', 'admin/select_bed.tpl');
        }

        $halls[0] = 'Select...';

        $tpl['MESSAGE'] = 'Please select a bed: ';

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

        $form->addDropBox('bed', array(0 => ''));
        $form->setLabel('bed', 'Bed: ');
        $form->setExtra('bed', 'disabled onChange="handle_bed_change()"');

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
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/select_bed.tpl');
    }

    function show_edit_bed($bed_id = NULL, $success = null, $error = null)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        # Determine the bed id. If the passed in variable is NULL,
        # use the request.
        if(!isset($bed_id)){
            $bed_id = $_REQUEST['bed'];
        }

        # Setup the title and color of the title bar
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl['TITLE'] = 'Edit Bed - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        # Create the room object given the room_id
        $bed = new HMS_Bed($bed_id);
        if(!$bed){
            return HMS_Bed::show_select_bed('Edit B', 'bed', 'show_edit_bed', NULL, 'Error: The selected bed does not exist!'); 
        }

        # Create the room object
        $room = $bed->get_parent();
        if(!$room){
            $tpl['ERROR_MSG'] = 'There was an error getting the room object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        # Create the floor object
        $floor = $room->get_parent();
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        $hall = $floor->get_parent();
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        $tpl['TITLE']               = 'Bed Properties';
        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$hall->id));
        $tpl['FLOOR_NUMBER']        = PHPWS_Text::secureLink($floor->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$floor->id));
        $tpl['ROOM_NUMBER']         = PHPWS_Text::secureLink($room->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$room->id));
        $tpl['BED_LETTER']          = $bed->bed_letter;

        $tpl['ASSIGNED_TO'] = $bed->get_assigned_to_link();
        
        $form = new PHPWS_Form();

        $form->addText('bedroom_label', $bed->bedroom_label);
        
        $form->addText('phone_number', $bed->phone_number);
        $form->setMaxSize('phone_number', 4);
        $form->setSize('phone_number', 5);
        
        $form->addText('banner_id', $bed->banner_id);

        $form->addCheckBox('ra_bed', 1);

        if($bed->ra_bed == 1){
            $form->setExtra('ra_bed', 'checked');
        }

        $form->addSubmit('submit', 'Submit');
        
        $form->addHidden('module', 'hms');        
        $form->addHidden('type', 'bed');
        $form->addHidden('op', 'edit_bed');
        $form->addHidden('bed_id', $bed->id);

        # if the user has permission to view the form but not edit it
        if(   !Current_User::allow('hms', 'bed_view') 
           && !Current_User::allow('hms', 'bed_attributes')
           && !Current_User::allow('hms', 'bed_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
    }

    function show_add_bed($success = NULL, $error = NULL)
    {
        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        
        $room_id = $_REQUEST['room_id'];
        
        $room = new HMS_Room($room_id);
        if(!$room){
            $tpl['ERROR_MSG'] = 'There was an error getting the room object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        # Create the floor object
        $floor = $room->get_parent();
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        $hall = $floor->get_parent();
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
        }

        $tpl['TITLE']               = 'Add New Bed';
        $tpl['HALL_NAME']           = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'hall', 'op'=>'show_edit_hall', 'hall'=>$hall->id));
        $tpl['FLOOR_NUMBER']        = PHPWS_Text::secureLink($floor->floor_number, 'hms', array('type'=>'floor', 'op'=>'show_edit_floor', 'floor'=>$floor->id));
        $tpl['ROOM_NUMBER']         = PHPWS_Text::secureLink($room->room_number, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$room->id));

        $tpl['ASSIGNED_TO'] = '&lt;unassigned&gt;';
        
        $form = new PHPWS_Form();

        if(isset($_REQUEST['bed_letter'])){
            $form->addText('bed_letter', $_REQUEST['bed_letter']);
        }else{
            $form->addText('bed_letter', chr($room->get_number_of_beds() + 97));
        }

        if(isset($_REQUEST['bedroom_label'])){
            $form->addText('bedroom_label', $_REQUEST['bedroom_label']);
        }else{
            $form->addText('bedroom_label', 'a');
        }
       
        if(isset($_REQUEST['phone_number'])){
            $form->addText('phone_number', $_REQUEST['phone_number']);
        }else{
            // Try to guess at the phone number
            $beds = $room->get_beds();
            if(sizeof($beds) > 0){
                $form->addText('phone_number', $beds[0]->phone_number);
            }else{
                $form->addText('phone_number');
            }
        }
        $form->setMaxSize('phone_number', 4);
        $form->setSize('phone_number', 5);
       
        if(isset($_REQUEST['banner_id'])){
            $form->addText('banner_id', $_REQUEST['banner_id']);
        }else{
            // try to guess a the banner ID
            $form->addText('banner_id', '0' . $room->room_number . ($room->get_number_of_beds()+1));
        }

        $form->addCheckBox('ra_bed', 1);

        $form->addSubmit('submit', 'Submit');
        
        $form->addHidden('module', 'hms');        
        $form->addHidden('type', 'bed');
        $form->addHidden('op', 'add_bed_submit');
        $form->addHidden('room_id', $room_id);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        # Reusing the edit bed template here
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
    }

    function add_bed_submit()
    {
        if(!Current_User::allow('hms', 'bed_structure')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        # Sanity checking
        if(!isset($_REQUEST['bed_letter']) || $_REQUEST['bed_letter'] == ''){
            return HMS_Bed::show_add_bed(NULL, 'You must enter a bed letter.');
        }

        if(!isset($_REQUEST['bedroom_label']) || $_REQUEST['bedroom_label'] == ''){
            return HMS_Bed::show_add_bed(NULL, 'You must enter a bedroom label.');
        }

        if(!isset($_REQUEST['phone_number']) || $_REQUEST['phone_number'] == ''){
            return HMS_Bed::show_add_bed(NULL, 'You must enter a phone number.');
        }

        if(!isset($_REQUEST['banner_id']) || $_REQUEST['banner_id'] == ''){
            return HMS_Bed::show_add_bed(NULL, 'You must enter a the bed\'s Banner id.');
        }

        if(!isset($_REQUEST['room_id']) || $_REQUEST['room_id'] == ''){
            return HMS_Bed::show_add_bed(NULL, 'Error: no room id specified.');
        }

        # Try to create the bed
        if(HMS_Bed::add_bed($_REQUEST['room_id'], $_REQUEST['bed_letter'], $_REQUEST['bedroom_label'], $_REQUEST['phone_number'], $_REQUEST['banner_id'], isset($_REQUEST['ra_bed']))){
            PHPWS_Core::initModClass('hms', 'HMS_Room.php');
            return HMS_Room::show_edit_room($_REQUEST['room_id'], 'Bed added successfully.');
        }else{
            return HMS_Bed::show_add_bed(NULL, 'There was an error saving the bed to the database.');
        }
    }

    function delete_bed_submit()
    {
        if(!Current_User::allow('hms', 'bed_structure')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        #TODO: Sanity checking, make sure bed_id and room_id are set, what to do if they're not??
        
        $result = HMS_Bed::delete_bed($_REQUEST['bed_id']);

        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        if($result){
            return HMS_Room::show_edit_room($_REQUEST['room_id'], 'Bed deleted.');
        }else{
            return HMS_Room::show_edit_room($_REQUEST['room_id'], 'Error removing bed.');
        }
    }

    function bed_pager_by_room($room_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = & new DBPager('hms_bed', 'HMS_Bed');
        $pager->db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        
        $pager->addWhere('hms_room.id', $room_id);
        $pager->db->addOrder('hms_bed.bedroom_label');
        $pager->db->addOrder('hms_bed.bed_letter');

        $page_tags['TABLE_TITLE']       = 'Beds in this room:';
        $page_tags['BEDROOM_LABEL']     = 'Bedroom';
        $page_tags['BED_LETTER_LABEL']  = 'Bed';
        $page_tags['ASSIGNED_TO_LABEL'] = 'Assigned to';
        $page_tags['RA_LABEL']          = 'RA bed';

        if(Current_User::allow('hms', 'bed_structure')){
            $page_tags['ADD_BED_LINK'] = PHPWS_Text::secureLink('Add bed', 'hms', array('type'=>'bed', 'op'=>'show_add_bed', 'room_id'=>$room_id));
        }

        $pager->setModule('hms');
        $pager->setTemplate('admin/bed_pager_by_room.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No beds found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerByRoomTags');
        $pager->addPageTags($page_tags);
       
        return $pager->get();
    }
}

?>
