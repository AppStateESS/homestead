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
	public $asu_username   = null;
	public $bed_id         = 0;
	public $meal_option    = 0;
	public $letter_printed = 0;
	public $email_sent     = 0;
	public $_gender        = 0;
	public $_bed           = null;

	/********************
	 * Instance Methods *
	 *******************/
	public function HMS_Assignment($id = 0)
	{
		$this->construct($id, 'hms_assignment');

		return $this;
	}

    public function getDb()
    {
        return new PHPWS_DB('hms_assignment');
    }

	public function copy($to_term, $bed_id)
	{
		$new_ass = clone($this);
		$new_ass->reset();
		$new_ass->bed_id = (int)$bed_id;
		$new_ass->term   = $to_term;

		try{
		    $new_ass->save();
		}catch(Exception $e){
		    throw $e;
		}
	}

	public function save()
	{
		$db = new PHPWS_DB('hms_assignment');
		$this->stamp();
		$result = $db->saveObject($this);
		if (!$result || PHPWS_Error::logIfError($result)) {
			PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
			throw new DatabaseException($result->toString());
		}

		return true;
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
		$bed = new HMS_Bed($this->bed_id);


		$this->_bed = &$bed;
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

		$text = $building->hall_name . ' Room ' . $room->room_number . ' - ' .$bed->bedroom_label;

		if($room->isPrivate()){
			$text .= ' (private)';
		}

		if($link){
			$roomCmd = CommandFactory::getCommand('EditRoomView');
			$roomCmd->setRoomId($room->id);

			return $roomCmd->getLink($text);
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

    public function get_f_movein_time_id()
    {
        if(!$this->loadBed()){
            return null;
        }

		$room   = $this->_bed->get_parent();
		$floor  = $room->get_parent();

        return $floor->f_movein_time_id;
    }

    public function get_t_movein_time_id()
    {
        if(!$this->loadBed()){
            return null;
        }

        $room   = $this->_bed->get_parent();
        $floor  = $room->get_parent();

        return $floor->t_movein_time_id;
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

	/**
	 * Returns TRUE if the username exists in hms_assignment,
	 * FALSE if the username either is not in hms_assignment.
	 *
	 * Uses the current term if none is supplied
	 */

	public static function checkForAssignment($asu_username, $term)
	{
		$db = new PHPWS_DB('hms_assignment');
		$db->addWhere('asu_username', $asu_username, 'ILIKE');
		$db->addWhere('term', $term);

		$result = $db->select('row');

		if(PHPWS_Error::logIfError($result)){
			PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
			throw new DatabaseException($result->toString());
		}

		return !is_null($result);
	}

	public static function getAssignment($asu_username, $term)
	{
		$db = new PHPWS_DB('hms_assignment');
		$db->addColumn('id');
		$db->addWhere('asu_username', $asu_username, 'ILIKE');

		$db->addWhere('term', $term);

		$result = $db->select('one');

		if (PHPWS_Error::logIfError($result)) {
			PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
			throw new DatabaseException($result->toString());
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
	public static function assignStudent(Student $student, $term, $room_id = NULL, $bed_id = NULL, $meal_plan, $notes="", $lottery = FALSE)
	{
	    /**
	     * Can't check permissions here because there are some student-facing commands that needs to make assignments (e.g. the lottery/re-application code)
	     *
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You are not allowed to edit student assignments.');
        }
        */

		PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
		PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
		PHPWS_Core::initModClass('hms', 'HMS_Room.php');
		PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
		PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
		PHPWS_Core::initModClass('hms', 'BannerQueue.php');

		PHPWS_Core::initModClass('hms', 'exception/AssignmentException.php');
		PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');

		$username = $student->getUsername();

		# Make sure a username was entered
		if(!isset($username) || $username == ''){
			throw new InvalidArgumentException('Bad username.');
		}

		$username = strtolower($username);

		if($student->getType() == TYPE_WITHDRAWN){
			throw new AssignmentException('Invalid student type. Student is withdrawn.');
		}

		if(HMS_Assignment::checkForAssignment($username, $term)){
			throw new AssignmentException('The student is already assigned.');
		}

		if(isset($bed_id)){
			# A bed_id was given, so create that bed object
			$vacant_bed = new HMS_Bed($bed_id);
            $vacant_bed->term = $term;
			if(!$vacant_bed){
				throw new AssignmentException('Null bed object.');
			}
			# Get the room that this bed is in
			$room = $vacant_bed->get_parent();

		}else if(isset($room_id)){
			# A room_id was given, so create that room object
			$room = new HMS_Room($room_id);
			if(!$room) {
				throw new AssignmentException('Null room object.');
			}

			# Make sure the room has a vacancy
			if(!$room->has_vacancy()){
				throw new AssignmentException('The room is full.');
			}

			# Make sure the room is not offline
			if(!$room->is_online){
				throw new AssignmentException('The room is offline');;
			}

			# And find a vacant bed in that room
			$beds = $room->getBedsWithVacancies();
			$vacant_bed = $beds[0];

		}else{
			# Both the bed and room IDs were null, so return an error
			throw new AssignmentException('No room nor bed specified.');
		}

		# Double check that the resulting bed is empty
		if($vacant_bed->get_number_of_assignees() > 0){
			throw new AssignmentException('The bed is not empty.');
		}

        # Issue a warning if the bed was reserved for room change
        if($vacant_bed->room_change_reserved != 0){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Room was reserved for room change');
        }

		# Check that the room's gender and the student's gender match
		$student_gender = $student->getGender();

		if(is_null($student_gender)){
			throw new AssignmentException('Student gender is null.');
		}

		if($room->gender_type != $student_gender){
			throw new AssignmentException('Room gender does not match the student\'s gender.');
		}

		# Create the floor object
		$floor = $room->get_parent();
		if(!$floor){
			throw new AssignmentException('Null floor object.');
		}

		# Create the hall object
		$hall = $floor->get_parent();
		if (!$hall) {
			throw new AssignmentException('Null hall object.');;
		}

		if($meal_plan == BANNER_MEAL_NONE){
			$meal_plan = NULL;
		}

		# Determine which meal plan to use
		// If this is a freshmen student and they've somehow selected none or low, give them standard
		if($student->getType() == TYPE_FRESHMEN && ($meal_plan == BANNER_MEAL_NONE || $meal_plan == BANNER_MEAL_LOW)){
			$meal_plan = BANNER_MEAL_STD;
			// If a student is living in a dorm which requires a meal plan and they've selected none, give them low
		}else if($hall->meal_plan_required == 1 && $meal_plan == BANNER_MEAL_NONE){
			$meal_plan = BANNER_MEAL_LOW;
		}

		# Send this off to the queue for assignment in banner
	        $banner_success = BannerQueue::queueAssignment($student, $term, $hall, $vacant_bed, 'HOME', $meal_plan);
		if($banner_success !== TRUE){
			throw new AssignmentException('Error while adding the assignment to the Banner queue.');
		}

		# Make the assignment in HMS
		$assignment = new HMS_Assignment();

		$assignment->asu_username   = $username;
		$assignment->bed_id         = $vacant_bed->id;
		$assignment->term           = $term;
		$assignment->letter_printed = 0;
		$assignment->email_sent     = 0;
        $assignment->meal_option    = $meal_plan;

		# If this was a lottery assignment, flag it as such
		if($lottery){
			$assignment->lottery = 1;
		}else{
			$assignment->lottery = 0;
		}

		$result = $assignment->save();

		if(!$result || PHPWS_Error::logIfError($result)){
			throw new DatabaseException($result->toString());
		}

		# Log the assignment
		HMS_Activity_Log::log_activity($username, ACTIVITY_ASSIGNED, UserStatus::getUsername(), $term . ' ' . $hall->hall_name . ' ' . $room->room_number . ' ' . $notes);

		# Look for roommates and flag their assignments as needing a new letter
		$room_id = $assignment->get_room_id();
		$room = new HMS_Room($room_id);

		# Go to the room level to get all the roommates
		$assignees = $room->get_assignees(); // get an array of student objects for those assigned to this room

		if(sizeof($assignees) > 1){
			foreach($assignees as $roommate){
				// Skip this student
				if($roommate->getUsername() == $username){
					continue;
				}
				$roommate_assign = HMS_Assignment::getAssignment($roommate->getUsername(),$term);
				$roommate_assign->letter_printed = 0;
				$roommate_assign->email_sent     = 0;

				$result = $roommate_assign->save();
			}
		}

		# Return Sucess
		return true;
	}

	public static function unassignStudent(Student $student, $term, $notes="")
	{
		if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')){
			PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
			throw new PermissionException('You do not have permission to unassign students.');
		}

		PHPWS_Core::initModClass('hms', 'BannerQueue.php');
		PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

		PHPWS_Core::initModClass('hms', 'exception/AssignmentException.php');
		PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');

		$username = $student->getUsername();

		# Make sure a username was entered
		if(!isset($username) || $username == ''){
			throw new InvalidArgumentException('Bad username.');
		}

		$username = strtolower($username);

		# Make sure the requested username is actually assigned
		if(!HMS_Assignment::checkForAssignment($username, $term)) {
			throw new AssignmentException('Student is not assigned.');
		}

		$assignment = HMS_Assignment::getAssignment($username, $term);
		if($assignment == FALSE || $assignment == NULL){
            throw new AssignmentException('Could not load assignment object.');
		}

        $bed = $assignment->get_parent();
        $room = $bed->get_parent();
		$floor = $room->get_parent();
		$building = $floor->get_parent();

		# Attempt to unassign the student in Banner though SOAP
		$banner_result = BannerQueue::queueRemoveAssignment($student,$term,$building,$bed);

		# Show an error and return if there was an error
		if($banner_result !== TRUE) {
			throw new AssignmentException('Error while adding the assignment removal to the Banner queue.');
		}

		# Record this before we delete from the db
		$banner_bed_id          = $bed->getBannerId();
		$banner_building_code   = $building->getBannerBuildingCode();

		# Attempt to delete the assignment in HMS
		$result = $assignment->delete();
		if(!$result){
			throw new DatabaseException($result->toString());
		}

		# Log in the activity log
		HMS_Activity_Log::log_activity($username, ACTIVITY_REMOVED, UserStatus::getUsername(), $term . ' ' . $banner_building_code . ' ' . $banner_bed_id . ' ' . $notes);

		# Generate assignment notices for old roommates
		$assignees = $room->get_assignees(); // get an array of student objects for those assigned to this room

		if(sizeof($assignees) > 1){
		    foreach($assignees as $roommate){
		        // Skip this student
		        if($roommate->getUsername() == $username){
		            continue;
		        }
		        $roommate_assign = HMS_Assignment::getAssignment($roommate->getUsername(), Term::getSelectedTerm());
		        $roommate_assign->letter_printed = 0;
		        $roommate_assign->email_sent = 0;

		        $result = $roommate_assign->save();
		    }
		}

		# Show a success message
		return true;
	}

}
