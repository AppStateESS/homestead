<?php

/**
 * Provides functionality to actually assign students to a room
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
    var $_gender        = 0;
    var $_bed           = null;

    /********************
     * Instance Methods *
     *******************/
    function HMS_Assignment($id = 0)
    {
        $this->construct($id, 'hms_assignment');
    }

    function copy($to_term, $bed_id)
    {
        $new_ass = clone($this);
        $new_ass->reset();
        $new_ass->bed_id = (int)$bed_id;
        $new_ass->term   = $to_term;
        return $new_ass->save();
    }

    function save()
    {
        $db = new PHPWS_DB('hms_assignment');
        $this->stamp();
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    function get_row_tags()
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
    function get_parent()
    {
        $this->loadBed();
        return $this->_bed;
    }

    /*
     * Loads the parent bed object of this room
     */
    function loadBed()
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
    function get_banner_building_code()
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
    function get_banner_bed_id(){
        if(!$this->loadBed()){
            return null;
        }

        return $this->_bed->banner_id;
    }

    /**
     * Returns a string like "Justice Hall Room 110"
     */
    function where_am_i()
    {
        if(!$this->loadBed()){
            return null;
        }

        $bed = $this->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        return ($building->hall_name . ' Room ' . $room->room_number);
    }


    /******************
     * Static Methods *
     *****************/
    
    /***************
     * Main Method *
     **************/
    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'show_assign_student':
                return HMS_Assignment::show_assign_student();
                break;
            case 'assign_student':
                return HMS_Assignment::assign_student();
                break;
            case 'show_unassign_student':
                return HMS_Assignment::show_unassign_student();
                break;
            case 'unassign_student':
                return HMS_Assignment::unassign_student();
                break;
            default:
                echo "undefined assignment op: {$_REQUEST['op']}";
                break;
        }

    }

    /**
     * Returns TRUE if the username exists in hms_assignment and is not deleted,
     * FALSE if the username either is not in hms_assignment or is deleted.
     * 
     * Uses the current term if none is supplied
     */

    function check_for_assignment($asu_username, $term = NULL)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('deleted', 0);
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

    /**
     * Creates a new HMS_Assignment object, and appropriately assigns the student.  Also takes care
     * of any fussy SOAP business.
     *
     * Returns the HMS_Assignment object, or FALSE if there was any error (like someone was
     * already assigned)
     *
    function create_assignment($asu_username, $term, $bed_id, $meal)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $meal_plan = HMS_SOAP::get_plan_meal_codes($asu_username, 
    }*/

    function get_assignment($asu_username, $term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('id');
        $db->addWhere('deleted', 0);
        $db->addWhere('asu_username', $asu_username, 'ILIKE');

        if(isset($term)){
            $db->addWhere('term', $term);
        }else{
            $db->addWhere('term', HMS_Term::get_current_term());
        }

        $result = $db->select('row');

        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }

        if(isset($result)){
            return new HMS_Assignment($result);
        }else{
            return NULL;
        }
    }

    function assign_student()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        if(!Current_User::allow('hms', 'assignment_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $more = '';

        # Make sure a username was entered
        if(!isset($_REQUEST['username']) || $_REQUEST['username'] == ''){
            return HMS_Assignment::show_assign_student(NULL, 'Error: You must enter a user name.');
        }
        
        # Create the hall object
        $hall = new HMS_Residence_Hall($_REQUEST['residence_hall']);
        if (!$hall) {
            return HMS_Assignment::show_assign_student(NULL, 'Error: There was a problem creating the hall object. Please contact ESS.');
        }

        # Create the room object
        $room = new HMS_Room($_REQUEST['room']);
        if(!$room) {
            return HMS_Assignment::show_assign_student(NULL, 'Error: There was a problem creating the room object. Please contact ESS.');
        }

        # Make sure the room has a vacancy
        if(!$room->has_vacancy()){
            return HMS_Assignment::show_assign_student(NULL, 'Error: The room is full.');
        }

        if($_REQUEST['use_bed'] == 'true'){
           $vacant_bed = new HMS_Bed($_REQUEST['bed']);
           if(!$vacant_bed){
               return HMS_Assignment::show_assign_student(NULL, 'Error: Could not create the bed object');
           }
        }else{
            # Find a vacant bed
            # TODO, revist this so that they can specificy the bed
            $beds = $room->get_beds_with_vacancies();
            $vacant_bed = $beds[0];
        }

        # TODO: double check that the bed is really empty.

        # Create application object
        $application = new HMS_Application($_REQUEST['username']);
        if(PEAR::isError($application)){
            return HMS_Assignment::show_assign_student(NULL, 'There was an error loading the student\'s application. Please contact ESS.');
        }
        
        # Check to make sure the student has an application on file
        $application_status = HMS_Application::check_for_application($_REQUEST['username'], HMS_Term::get_selected_term());

        if($application_status == FALSE){
            $more = ' (Warning: Did not find a housing application on file for this term)';
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

        # Make sure the student's gender matches the gender of the room.
        if($room->gender_type != HMS_SOAP::get_gender($_REQUEST['username'], TRUE)){
            return HMS_Assignment::show_assign_student(NULL, 'Error: The student\'s gender and the room\'s gender do not match.');
        }

        # This code is only run if the move was flagged as confirmed above
        # The code was copied/adapted from the 'unassign_student' function below
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
                $error_msg = "Error: Banner returned error code: $banner_result. Please contact ESS immediately. {$_REQUEST['username']} was not removed.";
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
            
        /*
        # Get the necessary meal plan code from the application
        $meal_plan = HMS_SOAP::get_plan_meal_codes($_REQUEST['username'], $hall->banner_building_code, $application->getMealOption());
        */

        # Hard code for plan: HOME, and use the meal plan selected in the drop down by the user
        $meal_plan['plan'] = 'HOME';
        $meal_plan['meal'] = $_REQUEST['meal_plan'];

        # Send this off to the queue for assignment in banner
        PHPWS_Core::initModClass('hms', 'HMS_Banner_Queue.php');
        $banner_success = HMS_Banner_Queue::queue_create_assignment(
            $_REQUEST['username'],
            HMS_Term::get_selected_term(),
            $hall->banner_building_code,
            $vacant_bed->banner_id,
            $meal_plan['plan'],
            $meal_plan['meal']
            );
        
        if($banner_success){
            return HMS_Assignment::show_assign_student(NULL, 'Banner Error: ' . $banner_success . ' The student was not assigned.');
        }

        # Make the assignment in HMS
        $assignment = new HMS_Assignment();

        $assignment->asu_username   = $_REQUEST['username'];
        $assignment->bed_id         = $vacant_bed->id;
        $assignment->term           = HMS_Term::get_selected_term();

        $result = $assignment->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Assignment::show_assign_student(NULL, 'Error: There was problem saving the assignment in HMS. The student was assigned in Banner, but not HMS. Please contact ESS.');
        }
        
        # Log the assignment
        HMS_Activity_Log::log_activity($_REQUEST['username'], ACTIVITY_ASSIGNED, Current_User::getUsername(), HMS_Term::get_selected_term() . ' ' . $hall->hall_name . ' ' . $room->room_number);

        # Show a success message
        return HMS_Assignment::show_assign_student('Successfully assigned ' . $_REQUEST['username'] . ' to ' . $hall->hall_name . ' room ' . $room->room_number . $more);
    }

    function unassign_student()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        if(!Current_User::allow('hms', 'assignment_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        $username = $_REQUEST['username'];
        
        # Make sure the requested username is actually assigned
        if(!HMS_Assignment::check_for_assignment($username, HMS_Term::get_selected_term())) {
            $error_msg = "Error: $username is not assigned in HMS for the selected term.";
            return HMS_Assignment::show_unassign_student(NULL, $error_msg);
        }

        $assignment = HMS_Assignment::get_assignment($username, HMS_Term::get_selected_term());
            
        # Attempt to unassign the student in Banner though SOAP
        PHPWS_Core::initModClass('hms', 'HMS_Banner_Queue.php');
        $banner_result = HMS_Banner_Queue::queue_remove_assignment(
            $_REQUEST['username'],
            HMS_Term::get_selected_term(),
            $assignment->get_banner_building_code(),
            $assignment->get_banner_bed_id());
        
        # Show an error and return if there was an error
        if($banner_result != 0) {
            $error_msg = "Error: Banner returned error code: $banner_result. Please contact ESS immediately. $username was not removed.";
            return HMS_Assignment::show_unassign_student(NULL, $error_msg);
        }

        # Attempt to delete the assignment in HMS
        if(!$assignment->delete()){
            # Show an error message
            $error_msg = "Error: $username was removed from Banner, but could not be removed from HMS. Pleease contact ESS immediately.";
            return HMS_Assignment::show_unassign_student(NULL, $error_msg);
        }

        # Log in the activity log
        HMS_Activity_Log::log_activity($username, ACTIVITY_REMOVED, Current_User::getUsername(), HMS_Term::get_selected_term() . ' ' . $assignment->get_banner_building_code() . ' ' . $assignment->get_banner_bed_id());
        
        # Show a success message
        $success_msg = "Success: $username was removed from HMS and Banner.";
        return HMS_Assignment::show_unassign_student($success_msg);
    }

    /*********************
     * Static UI Methods *
     ********************/

    function show_assign_student($success = NULL, $error = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        if(!Current_User::allow('hms', 'assignment_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        javascript('/modules/hms/assign_student');

        $form = &new PHPWS_Form;

        $form->addText('username');
        $form->setLabel('username', 'ASU Username: ');
        if(isset($_REQUEST['username'])){
            $form->setValue('username', $_REQUEST['username']);
        }

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
       
        #TODO: Write a nice foreach loop to merge these arrays keeping the keys the same
        # so that the 'select' option shows up at the top of the list. 
        $halls_array = HMS_Residence_Hall::get_halls_with_vacancies_array(HMS_Term::get_selected_term());
        $halls_array[0] = 'Select...';

        $form->addDropBox('residence_hall', $halls_array);
        $form->setLabel('residence_hall', 'Residence hall: ');
        if($pre_populate){
            $form->setMatch('residence_hall', $hall->id);
        }else{
            $form->setMatch('residence_hall', 0);
        }
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

        $form->addDropBox('meal_plan', array(HMS_MEAL_LOW=>'Low',
                                             HMS_MEAL_STD=>'Standard',
                                             HMS_MEAL_HIGH=>'High',
                                             HMS_MEAL_SUPER=>'Super',
                                             HMS_MEAL_NONE=>'None'));
        $form->setMatch('meal_plan', HMS_MEAL_STD);
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

    function show_assign_student_move_confirm(){
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
        $form->addHidden('type', 'assignment');
        $form->addHidden('op', 'assign_student');

        $tpl = $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/assign_student_move_confirm.tpl');
    }

    function show_unassign_student($success = NULL, $error = NULL)
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

    function assignment_pager_by_room($room_id)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = & new DBPager('hms_assignment', 'HMS_Assignment');

        $pager->db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $pager->db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');

        $pager->addWhere('hms_room.id', $room_id);
        $pager->addWhere('hms_assignment.deleted', 0);
        $pager->addWhere('hms_bed.deleted', 0);
        $pager->addWhere('hms_room.deleted', 0);

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

    function getPagerByRoomTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        
        $tags['NAME']       = PHPWS_Text::secureLink(HMS_SOAP::get_full_name($this->asu_username), 'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$this->asu_username));

        $reassign_link = PHPWS_Text::secureLink('Re-Assign','hms', array('module'=>'hms', 'type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$this->asu_username)); 
        $unassign_link = PHPWS_Text::secureLink('Unassign', 'hms', array('module'=>'hms', 'type'=>'assignment', 'op'=>'show_unassign_student', 'username'=>$this->asu_username));
        $tags['ACTION']     = $reassign_link . ' | ' . $unassign_link;

        return $tags;
    }

    function get_assignment_pager_by_suite($suite_id){
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = & new DBPager('hms_assignment', 'HMS_Assignment');

        $pager->db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $pager->db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');

        $pager->addWhere('hms_room.suite_id', $suite_id);
        $pager->addWhere('hms_assignment.deleted', 0);
        $pager->addWhere('hms_bed.deleted', 0);
        $pager->addWhere('hms_room.deleted', 0);

        $page_tags['NAME_LABEL']    = "Name";
        $page_tags['ACTION_LABEL']  = "Action";
        $page_tags['TABLE_TITLE']   = "Current Assignments";

        $pager->setModule('hms');
        $pager->setTemplate('admin/assignment_pager_by_suite.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->SetEmptyMessage("No assignments found.");

        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerBySuiteTags');
        $pager->addPageTags($page_tags);
       
        return $pager->get();
    }

    function getPagerBySuiteTags()
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



}
