<?php

/**
 * Provides public functionality to actually assign students to a room
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @author Matt     <matt at tux dot appstate dot edu>
 *
 * Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS_Item.php');
 
class HMS_Assignment extends HMS_Item
{
    var $asu_username   = null;
    var $bed_id         = 0;
    var $meal_option    = 0;
    var $letter_printed = 0;
    var $email_sent     = 0;
    var $_gender        = 0;
    var $_bed           = null;

    /********************
     * Instance Methods *
     *******************/
    public function HMS_Assignment($id = 0)
    {
        $this->construct($id, 'hms_assignment');

        return $this;
    }

    public function copy($to_term, $bed_id)
    {
        $new_ass = clone($this);
        $new_ass->reset();
        $new_ass->bed_id = (int)$bed_id;
        $new_ass->term   = $to_term;
        return $new_ass->save();
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_assignment');
        $this->stamp();
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    public function get_row_tags()
    {
        $tpl = $this->item_tags();

        $tpl['ASU_USERNAME']   = $this->asu_username;
        $tpl['BANNER_ID']      = $this->banner_id;
        $tpl['MEAL_OPTION']    = $this->meal_option;

        return $tpl;
    }

    /*
     * Returns the parent floor object of this room
     */
    public function get_parent()
    {
        $this->loadBed();
        return $this->_bed;
    }

    /*
     * Loads the parent bed object of this room
     */
    public function loadBed()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        $result = new HMS_Bed($this->bed_id);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }

        $this->_bed = & $result;
        return true;
    }

    /*
     * Returns the banner building code that is associated with this bed
     */
    public function get_banner_building_code()
    {
        if(!$this->loadBed()){
            return null;
        }

        $bed = $this->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        return $building->banner_building_code;
    }

    /*
     * Returns the banner bed id associated with this bed
     */
    public function get_banner_bed_id(){
        if(!$this->loadBed()){
            return null;
        }

        return $this->_bed->banner_id;
    }

    /**
     * Returns a string like "Justice Hall Room 110"
     */
    public function where_am_i($link = FALSE)
    {
        if(!$this->loadBed()){
            return null;
        }

        $bed = $this->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        $text = $building->hall_name . ' Room ' . $room->room_number;

        if($room->isPrivate()){
            $text .= ' (private)';
        }

        if($link){
            return PHPWS_Text::secureLink($text, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$room->id));
        }else{
            return $text;
        }
    }

    /**
     * Returns the phone number of the bed for this assignment.
     * Useful when called from outside classes....
     */
    public function get_phone_number()
    {
        if(!$this->loadBed()){
            return null;
        }

        return $this->_bed->phone_number;
    }

    public function get_ft_movein_time_id()
    {
        if(!$this->loadBed()){
            return null;
        }

        $room   = $this->_bed->get_parent();
        $floor  = $room->get_parent();

        return $floor->ft_movein_time_id;
    }

    public function get_rt_movein_time_id()
    {
        if(!$this->loadBed()){
            return null;
        }

        $room   = $this->_bed->get_parent();
        $floor  = $room->get_parent();

        return $floor->rt_movein_time_id;
    }

    public function get_room_id()
    {

        if(!$this->loadBed()){
            return null;
        }

        $room   = $this->_bed->get_parent();

        return $room->id;
    }

    /******************
     * Static Methods *
     *****************/
    
    /***************
     * Main Method *
     **************/
    public function main()
    {
        if( !Current_User::allow('hms', 'assignment_maintenance') && !Current_User::allow('hms', 'autoassign') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        switch($_REQUEST['op'])
        {
            case 'show_assign_student':
                return HMS_Assignment::show_assign_student();
                break;
            case 'assign_student':
                return HMS_Assignment::assign_student_result();
                break;
            case 'show_unassign_student':
                return HMS_Assignment::show_unassign_student();
                break;
            case 'unassign_student':
                return HMS_Assignment::unassign_student_result();
                break;
            default:
                echo "undefined assignment op: {$_REQUEST['op']}";
                break;
        }

    }

    /**
     * Returns TRUE if the username exists in hms_assignment,
     * FALSE if the username either is not in hms_assignment.
     * 
     * Uses the current term if none is supplied
     */

    public function check_for_assignment($asu_username, $term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('asu_username', $asu_username, 'ILIKE');

        if(isset($term)){
            if($term != -1) {
               $db->addWhere('term', $term);
            }
        }else{
            $db->addWhere('term', HMS_Term::get_current_term());
        }

        return !is_null($db->select('row'));
    }

    public function get_assignment($asu_username, $term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('id');
        $db->addWhere('asu_username', $asu_username, 'ILIKE');

        if(isset($term)){
            $db->addWhere('term', $term);
        }else{
            $db->addWhere('term', HMS_Term::get_current_term());
        }

        $result = $db->select('row');

        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if(isset($result)){
            return new HMS_Assignment($result);
        }else{
            return NULL;
        }
    }

    /**
     * Does all the checks necessary to assign a student and makes the assignment
     * The $room_id and $bed_id fields are optional, but one or the other must be specificed
     */
    public function assign_student($username, $term, $room_id = NULL, $bed_id = NULL, $meal_plan, $notes="", $lottery = FALSE)
    {   
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        /*
         * Have to comment this out for the lottery, since a student is the current user
        if(!Current_User::allow('hms', 'assignment_maintenance')){
            return E_PERMISSION_DENIED;
        }
        */



        # Make sure a username was entered
        if(!isset($username) || $username == ''){
            return E_ASSIGN_MALFORMED_USERNAME;
        }

        $username = strtolower($username);

        if(HMS_SOAP::get_student_type($username, $term) == TYPE_WITHDRAWN){
            return E_ASSIGN_WITHDRAWN;
        }

        if(HMS_Assignment::check_for_assignment($username, $term)){
            return E_ASSIGN_ALREADY_ASSIGNED;
        }

        if(isset($bed_id)){
            # A bed_id was given, so create that bed object
            $vacant_bed = new HMS_Bed($bed_id);
            if(!$vacant_bed){
                return E_ASSIGN_NULL_BED_OBJECT;
            }
            # Get the room that this bed is in
            $room = $vacant_bed->get_parent();

        }else if(isset($room_id)){
            # A room_id was given, so create that room object
            $room = new HMS_Room($room_id);
            if(!$room) {
                return E_ASSIGN_NULL_ROOM_OBJECT;
            }
            
            # Make sure the room has a vacancy
            if(!$room->has_vacancy()){
                return E_ASSIGN_ROOM_FULL;
            }

            # Make sure the room is not offline
            if(!$room->is_online){
                return E_ASSIGN_ROOM_OFFLINE;
            }
            
            # And find a vacant bed in that room
            $beds = $room->get_beds_with_vacancies();
            $vacant_bed = $beds[0];

        }else{
            # Both the bed and room IDs were null, so return an error
            return E_ASSIGN_NO_DESTINATION;
        }

        # Double check that the resulting bed is empty
        if($vacant_bed->get_number_of_assignees() > 0){
            return E_ASSIGN_BED_NOT_EMPTY;
        }
        
        # Check that the room's gender and the student's gender match
        $student_gender = HMS_SOAP::get_gender($username, TRUE);

        if(is_null($student_gender)){
            return E_ASSIGN_NO_DATA;
        }

        if($room->gender_type != $student_gender){
            return E_ASSIGN_GENDER_MISMATCH;
        }

        # Create the floor object
        $floor = $room->get_parent();
        if(!$floor){
            return E_ASSIGN_NULL_FLOOR_OBJECT;
        }

        # Create the hall object
        $hall = $floor->get_parent();
        if (!$hall) {
            return E_ASSIGN_NULL_HALL_OBJECT;
        }        
       
        if($meal_plan == BANNER_MEAL_NONE){
            $meal_plan = NULL;
        }
        
        # Hard code for plan: HOME
        $meal['plan'] = 'HOME';

        # Determine which meal plan to use
        // If this is a freshmen student and they've somehow selected none or low, give them standard
        if(HMS_SOAP::get_student_type($username) == TYPE_FRESHMEN && ($meal_plan == BANNER_MEAL_NONE || $meal_plan == BANNER_MEAL_LOW)){
            $meal_plan = BANNER_MEAL_STD;
        // If a student is living in a dorm which requires a meal plan and they've selected none, give them low
        }else if($hall->meal_plan_required == 1 && $meal_plan == BANNER_MEAL_NONE){
            $meal_plan = BANNER_MEAL_LOW;
        }
        $meal['meal'] = $meal_plan;

        # Send this off to the queue for assignment in banner
        PHPWS_Core::initModClass('hms', 'HMS_Banner_Queue.php');
        $banner_success = HMS_Banner_Queue::queue_create_assignment(
            $username,
            $term,
            $hall->banner_building_code,
            $vacant_bed->banner_id,
            $meal['plan'],
            $meal['meal']
            );

        if($banner_success != "0" || $banner_success === FALSE){
            return E_ASSIGN_BANNER_ERROR;
        }
        
        # Make the assignment in HMS
        $assignment = new HMS_Assignment();

        $assignment->asu_username   = $username;
        $assignment->bed_id         = $vacant_bed->id;
        $assignment->term           = $term;
        $assignment->letter_printed = 0;
        $assignment->email_sent     = 0;

        # If this was a lottery assignment, flag it as such
        if($lottery){
            $assignment->lottery = 1;
        }else{
            $assignment->lottery = 0;
        }

        $result = $assignment->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return E_ASSIGN_HMS_DB_ERROR;
        }
        
        # Log the assignment
        HMS_Activity_Log::log_activity($username, ACTIVITY_ASSIGNED, Current_User::getUsername(), HMS_Term::get_selected_term() . ' ' . $hall->hall_name . ' ' . $room->room_number . ' ' . $notes);

        # Look for roommates and flag their assignments as needing a new letter
        $room_id = $assignment->get_room_id();
        $room = new HMS_Room($room_id);

        # Go to the room level to get all the roommates
        $assignees = $room->get_assignees(); // get an array of student objects for those assigned to this room

        if(sizeof($assignees) > 1){
            foreach($assignees as $roommate){
                // Skip this student
                if($roommate->asu_username == $username){
                    continue;
                }
                $roommate_assign = HMS_Assignment::get_assignment($roommate->asu_username,$term);
                $roommate_assign->letter_printed = 0;
                $roommate_assign->email_sent     = 0;
                $result = $roommate_assign->save();
            }
        }

        # Return Sucess
        return E_SUCCESS;
    }

    public function unassign_student($username, $term, $notes="")
    {
        if(!Current_User::allow('hms', 'assignment_maintenance')){
            return E_PERMISSION_DENIED;
        }

        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        # Make sure a username was entered
        if(!isset($username) || $username == ''){
            return E_ASSIGN_MALFORMED_USERNAME;
        }

        $username = strtolower($username);
        
        # Make sure the requested username is actually assigned
        if(!HMS_Assignment::check_for_assignment($username, $term)) {
            //$error_msg = "Error: $username ";
            return E_UNASSIGN_NOT_ASSIGNED;
        }

        $assignment = HMS_Assignment::get_assignment($username, $term);
        if($assignment == FALSE || $assignment == NULL){
            return E_UNASSIGN_ASSIGN_LOAD_FAILED;
        }
            
        # Attempt to unassign the student in Banner though SOAP
        PHPWS_Core::initModClass('hms', 'HMS_Banner_Queue.php');
        $banner_result = HMS_Banner_Queue::queue_remove_assignment(
            $username,
            $term,
            $assignment->get_banner_building_code(),
            $assignment->get_banner_bed_id());
        
        # Show an error and return if there was an error
        if($banner_result != E_SUCCESS) {
            //$error_msg = "Error: Banner returned error code: $banner_result. Please contact ESS immediately. $username was not removed.";
            return E_UNASSIGN_BANNER_ERROR;
        }

        # Record this before we delete from the db
        $banner_bed_id          = $assignment->get_banner_bed_id();
        $banner_building_code   = $assignment->get_banner_building_code();
        
        # Attempt to delete the assignment in HMS
        if(!$assignment->delete()){
            //$error_msg = "Error: $username was removed from Banner, but could not be removed from HMS. Pleease contact ESS immediately.";
            return E_UNASSIGN_HMS_DB_ERROR;
        }

        # Log in the activity log
        HMS_Activity_Log::log_activity($username, ACTIVITY_REMOVED, Current_User::getUsername(), $term . ' ' . $banner_building_code . ' ' . $banner_bed_id . ' ' . $notes);
        
        # Show a success message
        return E_SUCCESS;
    }

    /*********************
     * Static UI Methods *
     ********************/

    public function show_assign_student($success = NULL, $error = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        if(!Current_User::allow('hms', 'assignment_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        javascript('/jquery');
        javascript('/modules/hms/assign_student');
        Layout::addStyle('hms', 'css/autosuggest2.css');

        $form = &new PHPWS_Form;

        $form->addText('username');
        $form->setLabel('username', 'ASU Username: ');
        if(isset($_REQUEST['username'])){
            $form->setValue('username', $_REQUEST['username']);
        }

        $form->addTextarea('note');
        $form->setLabel('note', 'Note: ');

        $form->addHidden('term', HMS_Term::get_selected_term());

        # Check to see if a bed_id was passed in, this means
        # the user clicked an 'unassigned' link. We need to pre-populate
        # the drop downs.
        unset($pre_populate);
        if(isset($_REQUEST['bed_id'])){
            $pre_populate = true;
            
            $bed = new HMS_Bed($_REQUEST['bed_id']);
            $room = $bed->get_parent();
            $floor = $room->get_parent();
            $hall = $floor->get_parent();
        }else{
            $pre_populate = false;
        }
       
        $halls_array = array();
        $halls_array[0] = 'Select...';
        $hall_list = HMS_Residence_Hall::get_halls_with_vacancies(HMS_Term::get_selected_term());
        foreach ($hall_list as $_hall) {
            $halls_array[$_hall->id] = $_hall->hall_name;
        }


        $form->addDropBox('residence_hall', $halls_array);

        if($pre_populate){
            $form->setMatch('residence_hall', $hall->id);
        }else{
            $form->setMatch('residence_hall', 0);
        }
        $form->setLabel('residence_hall', 'Residence hall: ');
        $form->setExtra('residence_hall', 'onChange="handle_hall_change()"');

        if($pre_populate){
            $form->addDropBox('floor', $hall->get_floors_array());
            $form->setMatch('floor', $floor->id);
            $form->setExtra('floor', 'onChange="handle_floor_change()"');
        }else{
            $form->addDropBox('floor', array(0 => ''));
            $form->setExtra('floor', 'disabled onChange="handle_floor_change()"');
        }
        $form->setLabel('floor', 'Floor: ');

        if($pre_populate){
            $form->addDropBox('room', $floor->get_rooms_array());
            $form->setMatch('room', $room->id);
            $form->setExtra('room', 'onChange="handle_room_change()"');
        }else{
            $form->addDropBox('room', array(0 => ''));
            $form->setExtra('room', 'disabled onChange="handle_room_change()"');
        }
        $form->setLabel('room', 'Room: ');

        if($pre_populate){
            $form->addDropBox('bed', $room->get_beds_array());
            $form->setMatch('bed', $bed->id);
            $form->setExtra('bed', 'onChange="handle_bed_change()"');
            $show_bed_drop = true;
        }else{
            $form->addDropBox('bed', array(0 => ''));
            $form->setExtra('bed', 'disabled onChange="handle_bed_change()"');
            $show_bed_drop = false;
        }
        $form->setLabel('bed', 'Bed: ');

        if($show_bed_drop){
            $tpl['BED_STYLE'] = '';
            $tpl['LINK_STYLE'] = 'display: none';
        }else{
            $tpl['BED_STYLE'] = 'display: none';
            $tpl['LINK_STYLE'] = '';
        }

        $form->addDropBox('meal_plan', array(BANNER_MEAL_LOW   => 'Low',
                                             BANNER_MEAL_STD   => 'Standard',
                                             BANNER_MEAL_HIGH  => 'High',
                                             BANNER_MEAL_SUPER => 'Super',
                                             BANNER_MEAL_NONE  => 'None',
                                             BANNER_MEAL_4WEEK => 'Summer (4 weeks)',
                                             BANNER_MEAL_5WEEK => 'Summer (5 weeks)'));
        $form->setMatch('meal_plan', BANNER_MEAL_STD);
        $form->setLabel('meal_plan', 'Meal plan: ');

        $form->addSubmit('submit', 'Assign Student');
        if(!$show_bed_drop){
            $form->setExtra('submit', 'disabled');
        }
       
        if($pre_populate){
            $form->addHidden('use_bed', 'true');
        }else{
            $form->addHidden('use_bed', 'false');
        }
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'assign_student');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        $tpl['TITLE'] = 'Assign Student - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        $tpl['MESSAGE'] = 'Please enter the ASU user name of the student you would like to assign and select where to assign the student.';
        return PHPWS_Template::process($tpl, 'hms', 'admin/assign_student.tpl');
        
    }

    public function show_assign_student_move_confirm(){
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $tpl['TITLE'] = 'Assign Student - Confirm Move ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        $fullname = HMS_SOAP::get_full_name($_REQUEST['username']);
        $assignment = HMS_Assignment::get_assignment($_REQUEST['username'], HMS_Term::get_selected_term());
        $location = $assignment->where_am_i();

        $tpl['MESSAGE'] = "Warnring: $fullname is already assigned to $location. Click the 'Confirm' button below to move the student, or 'Cancel' to keep the current assignment.";

        $form = & new PHPWS_Form();

        $form->addButton('cancel', 'Cancel');
        $form->addSubmit('submit', 'Confirm Move');

        $form->addHidden('move_confirmed', 'true');
        $form->addHidden('username', $_REQUEST['username']);
        $form->addHidden('residence_hall', $_REQUEST['residence_hall']);
        $form->addHidden('room', $_REQUEST['room']);
        $form->addHidden('bed', $_REQUEST['bed']);
        $form->addHidden('meal_plan', $_REQUEST['meal_plan']);
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'assign_student');

        $tpl = $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/assign_student_move_confirm.tpl');
    }
    
    public function assign_student_result()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        if(!Current_User::allow('hms', 'assignment_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $more = '';

        
        
        # Check to make sure the student has an application on file
        $application_status = HousingApplication::checkForApplication($_REQUEST['username'], HMS_Term::get_selected_term());

        if($application_status == FALSE){
            $more = ' (Warning: Did not find a housing application on file for this term)';
        }else{
            # Create application object
            $application = new FallApplication($application_status['id']);
            if(PEAR::isError($application)){
                return HMS_Assignment::show_assign_student(NULL, 'There was an error loading the student\'s application. Please contact ESS.');
            }
        }
        
        # If the student is already assigned, show the confirmation screen. If the student is already assigned
        # and the confirmation flag is true, then set a flag and proceed.
        $move_needed = FALSE;
        if(HMS_Assignment::check_for_assignment($_REQUEST['username'], HMS_Term::get_selected_term())){
            if(isset($_REQUEST['move_confirmed'])){
                $move_needed = TRUE;
            }else{
                return HMS_Assignment::show_assign_student_move_confirm();
            }
        }

        // Get the student's gender
        $student_gender = HMS_SOAP::get_gender($_REQUEST['username'], TRUE);

        if(!isset($student_gender) || is_null($student_gender)){
            return HMS_Assignment::show_assign_student(NULL, 'Error: No data found in Banner for the specified user name. Please check the user name and try again.');
        }   

        # Create the room object so we can check gender
        $room = new HMS_Room($_REQUEST['room']);
        if(!$room){
            return HMS_Assignment::show_assign_student(NULL, 'Error creating the room object.');
        }

        # Create the hall object for later
        $floor  = $room->get_parent();
        $hall   = $floor->get_parent();

        # Make sure the student's gender matches the gender of the room.
        if($room->gender_type != $student_gender){
            // Room gender does not match student's gender, so check if we can change it
            if($room->can_change_gender($student_gender) && Current_User::allow('hms', 'room_attributes')){
                $room->gender_type = $student_gender;
                $room->save();
                $more .= ' (Warning: Changing room gender)';
            }else{
                return HMS_Assignment::show_assign_student(NULL, 'Error: The student\'s gender and the room\'s gender do not match and the room could not be changed.');
            }
        }

        # This code is only run if the move was flagged as confirmed above
        # The code was copied/adapted from the 'unassign_student' public function below
        if($move_needed){
            $assignment = HMS_Assignment::get_assignment($_REQUEST['username'], HMS_Term::get_selected_term());
            
            # Attempt to unassign the student in Banner though SOAP
            PHPWS_Core::initModClass('hms', 'HMS_Banner_Queue.php');
            $banner_result = HMS_Banner_Queue::queue_remove_assignment(
                $_REQUEST['username'],
                HMS_Term::get_selected_term(),
                $assignment->get_banner_building_code(),
                $assignment->get_banner_bed_id());

            # Show an error and return if there was an error
            if($banner_result != 0) {
                $error_msg = "Error deleting current assignment: Banner returned error code: $banner_result. Please contact ESS immediately. {$_REQUEST['username']} was not removed.";
                return HMS_Assignment::show_assign_student(NULL, $error_msg);
            }

            # Attempt to delete the assignment in HMS
            if(!$assignment->delete()){
                # Show an error message
                $error_msg = "Error: {$_REQUEST['username']} was removed from Banner, but could not be removed from HMS. Pleease contact ESS immediately.";
                return HMS_Assignment::show_assign_student(NULL, $error_msg);
            }

            # Log in the activity log
            HMS_Activity_Log::log_activity($_REQUEST['username'], ACTIVITY_REMOVED, Current_User::getUsername(), HMS_Term::get_selected_term() . ' ' . $assignment->get_banner_building_code() . ' ' . $assignment->get_banner_bed_id());
        }

        # Actually try to make the assignment, decide whether to use the room id or the bed id
        if(isset($_REQUEST['bed']) && $_REQUEST['bed'] != 0){
            $assign_result = HMS_Assignment::assign_student($_REQUEST['username'], HMS_Term::get_selected_term(), NULL, $_REQUEST['bed'], $_REQUEST['meal_plan'], $_REQUEST['note']);
        }else{
            $assign_result = HMS_Assignment::assign_student($_REQUEST['username'], HMS_Term::get_selected_term(), $_REQUEST['room'], NULL, $_REQUEST['meal_plan'], $_REQUEST['note']);
        }
            
        if($assign_result == E_SUCCESS){
            # Show a success message
            return HMS_Assignment::show_assign_student('Successfully assigned ' . $_REQUEST['username'] . ' to ' . $hall->hall_name . ' room ' . $room->room_number . $more);
        }else{
            # Show an error message
            $error_msg = HMS_Assignment::get_assignment_error_msg($assign_result);
            return HMS_Assignment::show_assign_student(NULL, $error_msg);
        }
    }
    
    public function show_unassign_student($success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        if(!Current_User::allow('hms', 'assignment_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        $form->addText('username');
        if(isset($_REQUEST['username'])) {
            $form->setValue('username', $_REQUEST['username']);
        }
        
        $form->addTextarea('note');
        $form->setLabel('note', 'Note: ');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'unassign_student');
        $form->addSubmit('submit', _('Unassign Student'));

        $tpl = $form->getTemplate();
        
        if(isset($error)){
            $tpl['ERROR_MSG']   = $error;
        }

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }
        
        $tpl['TITLE'] = 'Unassign Student - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        $tpl['MESSAGE'] = 'Please enter an ASU username to unassign:';
        return PHPWS_Template::process($tpl, 'hms', 'admin/unassign_student.tpl');
    }

    public function unassign_student_result()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        
        $unassign_result = HMS_Assignment::unassign_student($_REQUEST['username'], HMS_Term::get_selected_term(), $_REQUEST['note']);

        if($unassign_result == E_SUCCESS){
            return HMS_Assignment::show_unassign_student('Successfully un-assigned ' . $_REQUEST['username'] . '.');
        }else{
            $error_msg = HMS_Assignment::get_assignment_error_msg($unassign_result);
            return HMS_Assignment::show_unassign_student(NULL, $error_msg);
        }
    }

    public function assignment_pager_by_room($room_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = & new DBPager('hms_assignment', 'HMS_Assignment');

        $pager->db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $pager->db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');

        $pager->addWhere('hms_room.id', $room_id);

        $page_tags['NAME_LABEL']    = 'Name';
        $page_tags['ACTION_LABEL']  = 'Action';
        $page_tags['TABLE_TITLE']   = 'Current Assignments';

        $pager->setModule('hms');
        $pager->setTemplate('admin/assignment_pager_by_room.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No assignments found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerByRoomTags');
        $pager->addPageTags($page_tags);
       
        return $pager->get();
    }

    public function getPagerByRoomTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        
        $tags['NAME']       = PHPWS_Text::secureLink(HMS_SOAP::get_full_name($this->asu_username), 'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$this->asu_username));

        $reassign_link = PHPWS_Text::secureLink('Re-Assign','hms', array('module'=>'hms', 'type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$this->asu_username)); 
        $unassign_link = PHPWS_Text::secureLink('Unassign', 'hms', array('module'=>'hms', 'type'=>'assignment', 'op'=>'show_unassign_student', 'username'=>$this->asu_username));
        $tags['ACTION']     = $reassign_link . ' | ' . $unassign_link;

        return $tags;
    }

    /**************************
     * Static Utility Methods *
     *************************/

    /** 
     * Translates an error code into a string
     */
    public function get_assignment_error_msg($error_code)
    {
        $error_msg = 'Error: ';
        
        switch($error_code){
            case E_ASSIGN_MALFORMED_USERNAME:
                $error_msg .= 'Invalid username.';
                break;
            case E_ASSIGN_NULL_HALL_OBJECT:
                $error_msg .= 'There was a problem creating the hall object.';
                break;
            case E_ASSIGN_NULL_FLOOR_OBJECT:
                $error_msg .= 'There was a problem creating the floor object.';
                break;
            case E_ASSIGN_NULL_ROOM_OBJECT:
                $error_msg .= 'There was a problem creating the room object.';
                break;
            case E_ASSIGN_NULL_BED_OBJECT:
                $error_msg .= 'There was a problem creating the bed object.';
                break;
            case E_ASSIGN_ROOM_FULL:
                $error_msg .= 'That room is full.';
                break;
            case E_ASSIGN_GENDER_MISMATCH:
                $error_msg .= 'The student\'s gender and room\'s gender do not match.';
                break;
            case E_ASSIGN_BANNER_ERROR:
                $error_msg .= 'There was an error while talking to Banner.';
                break;
            case E_ASSIGN_HMS_DB_ERROR:
                $error_msg .= 'There was an error while working with the HMS database.';
                break;
            case E_ASSIGN_ALREADY_ASSIGNED:
                $error_msg .= 'The student is already assigned.';
                break;
            case E_ASSIGN_WITHDRAWN:
                $error_msg .= 'The student is withdrawn.';
                break;
            case E_ASSIGN_NO_DATA:
                $error_msg .= 'No data is available for that student. Check the user name and try again.';
                break;
            case E_ASSIGN_ROOM_OFFLINE:
                $error_msg .= 'Selected Room is offline.';
                break;
            case E_ASSIGN_NO_DESTINATION:
                $error_msg .= 'No destination was specified.';
                break;
            case E_ASSIGN_BED_NOT_EMPTY:
                $error_msg .= 'The chosen bed is not vacant.';
                break;
            case E_UNASSIGN_NOT_ASSIGNED:
                $error_msg .= 'The student is not assigned in HMS for the selected term.';
                break;
            case E_UNASSIGN_ASSIGN_LOAD_FAILED:
                $error_msg .= 'There was an error loading the student\'s assignment.';
                break;
            case E_UNASSIGN_BANNER_ERROR:
                $error_msg .= 'There was an error while talking to Banner.';
                break;
            case E_UNASSIGN_HMS_DB_ERROR:
                $error_msg .= 'There was an error while working with the HMS database';
                break;
            default:
                $error_msg .= 'Unknown error code: ' . $error_code;
        }

        return $error_msg;
    }

}
