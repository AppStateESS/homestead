<?php

namespace Homestead;

/**
 * Provides public functionality to actually assign students to a room
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @author Matt <matt at tux dot appstate dot edu>
 *
 *         Some code copied from:
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Assignment extends HMS_Item {

    public $asu_username = null;
    public $banner_id;
    public $bed_id = 0;
    public $letter_printed = 0;
    public $email_sent = 0;
    public $reason = null;
    public $application_term = null;
    public $class = null;
    public $_gender = 0;
    public $_bed = null;

    /**
     * ******************
     * Instance Methods *
     * *****************
     */
    public function __construct($id = 0)
    {
        parent::__construct($id, 'hms_assignment');

        return $this;
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_assignment');
    }

    public function copy($to_term, $bed_id)
    {
        $new_ass = clone ($this);
        $new_ass->reset();
        $new_ass->bed_id = (int) $bed_id;
        $new_ass->term = $to_term;

        try {
            $new_ass->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_assignment');
        $this->stamp();
        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
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
        if (!$this->loadBed()) {
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
    public function get_banner_bed_id()
    {
        if (!$this->loadBed()) {
            return null;
        }

        return $this->_bed->banner_id;
    }

    /**
     * Returns a string like "Justice Hall Room 110"
     */
    public function where_am_i($link = FALSE)
    {
        if (!$this->loadBed()) {
            return null;
        }

        $bed = $this->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        $text = $building->hall_name . ' Room ' . $room->room_number . ' - ' . $bed->bedroom_label . $bed->bed_letter;

        if ($room->isPrivate()) {
            $text .= ' (private)';
        }

        if ($link) {
            $roomCmd = CommandFactory::getCommand('EditRoomView');
            $roomCmd->setRoomId($room->id);

            return $roomCmd->getLink($text);
        } else {
            return $text;
        }
    }

    /**
     * Returns the phone number of the bed for this assignment.
     *
     * @depricated
     */
    public function get_phone_number()
    {
        if (!$this->loadBed()) {
            return null;
        }

        return $this->_bed->phone_number;
    }

    public function get_f_movein_time_id()
    {
        if (!$this->loadBed()) {
            return null;
        }

        $room = $this->_bed->get_parent();
        $floor = $room->get_parent();

        return $floor->f_movein_time_id;
    }

    public function get_t_movein_time_id()
    {
        if (!$this->loadBed()) {
            return null;
        }

        $room = $this->_bed->get_parent();
        $floor = $room->get_parent();

        return $floor->t_movein_time_id;
    }

    public function get_rt_movein_time_id()
    {
        if (!$this->loadBed()) {
            return null;
        }

        $room = $this->_bed->get_parent();
        $floor = $room->get_parent();

        return $floor->rt_movein_time_id;
    }

    public function get_room_id()
    {
        if (!$this->loadBed()) {
            return null;
        }

        $room = $this->_bed->get_parent();

        return $room->id;
    }

    /**
     * ****************************
     * Accessor / Mutator Methods *
     * ***************************
     */
    public function getId()
    {
        return $this->id;
    }

    public function getBannerId()
    {
        return $this->banner_id;
    }

    public function setBannerId($id)
    {
        $this->banner_id = $id;
    }

    public function getUsername()
    {
        return $this->asu_username;
    }

    public function getBedId()
    {
        return $this->bed_id;
    }

    public function getReason()
    {
        return $this->reason;
    }

    /**
     * ****************
     * Static Methods *
     * ***************
     */

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

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if($result === false || is_null($result)){
            return false;
        }

        return true;
    }

    public static function getAssignment($asu_username, $term)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('id');
        $db->addWhere('asu_username', $asu_username, 'ILIKE');

        $db->addWhere('term', $term);

        $result = $db->select('one');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if (isset($result)) {
            return new HMS_Assignment($result);
        } else {
            return NULL;
        }
    }

    public static function getAssignmentByBannerId($bannerId, $term)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('banner_id', $bannerId);
        $db->addWhere('term', $term);

        $assignment = new HMS_Assignment();

        $result = $db->loadObject($assignment);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if ($assignment->id > 0) {
            return $assignment;
        } else {
            return null;
        }
    }

    /**
     * Does all the checks necessary to assign a student and makes the assignment
     *
     * The $room_id and $bed_id fields are optional, but one or the other must be specificed
     *
     * @param Student $student
     * @param Integer $term
     * @param Integer $room_id
     * @param Integer $bed_id
     * @param String $notes
     * @param boolean $lottery
     * @param string $reason
     * @throws InvalidArgumentException
     * @throws AssignmentException
     * @throws DatabaseException
     * @throws Exception
     */
    public static function assignStudent(Student $student, $term, $room_id = NULL, $bed_id = NULL, $notes = "", $lottery = FALSE, $reason)
    {

        /**
         * Can't check permissions here because there are some student-facing commands that needs to make assignments (e.g.
         * the lottery/re-application code)
         *
         * if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')) {
         * PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
         * throw new PermissionException('You are not allowed to edit student assignments.');
         * }
         */
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'BannerQueue.php');
        PHPWS_Core::initModClass('hms', 'AssignmentHistory.php');

        PHPWS_Core::initModClass('hms', 'exception/AssignmentException.php');

        $username = $student->getUsername();

        // Make sure a username was entered
        if (!isset($username) || $username == '') {
            throw new InvalidArgumentException('Bad username.');
        }

        $username = strtolower($username);

        // Make sure the student has a valid student type.
        if ($student->getType() == TYPE_WITHDRAWN) {
            throw new AssignmentException('Invalid student type. Student is withdrawn.');
        }

        // Make sure the student has a valid application term
        if($student->getApplicationTerm() === null || $student->getApplicationTerm() === ''){
            throw new AssignmentException('The student\'s application term is missing or invalid.');
        }

        if (HMS_Assignment::checkForAssignment($username, $term)) {
            throw new AssignmentException('The student is already assigned.');
        }

        if (isset($bed_id)) {
            // A bed_id was given, so create that bed object
            $vacant_bed = new HMS_Bed($bed_id);

            if (!$vacant_bed) {
                throw new AssignmentException('Null bed object.');
            }
            // Get the room that this bed is in
            $room = $vacant_bed->get_parent();
        } else if (isset($room_id)) {
            // A room_id was given, so create that room object
            $room = new HMS_Room($room_id);

            // And find a vacant bed in that room
            $beds = $room->getBedsWithVacancies();
            $vacant_bed = $beds[0];
        } else {
            // Both the bed and room IDs were null, so return an error
            throw new AssignmentException('No room nor bed specified.');
        }

        if (!$room) {
            throw new AssignmentException('Null room object.');
        }

        // Make sure the room has a vacancy
        if (!$room->has_vacancy()) {
            throw new AssignmentException('The room is full.');
        }

        // Make sure the room is not offline
        if ($room->offline) {
            throw new AssignmentException('The room is offline');
        }

        // Double check that the bed is in the same term as we're being requested to assign for
        if ($vacant_bed->getTerm() != $term) {
            throw new AssignmentException('The bed\'s term and the assignment term do not match.');
        }

        // Double check that the resulting bed is empty
        if ($vacant_bed->get_number_of_assignees() > 0) {
            throw new AssignmentException('The bed is not empty.');
        }

        // Check that the room's gender and the student's gender match
        $student_gender = $student->getGender();

        if (is_null($student_gender)) {
            throw new AssignmentException('Student gender is null.');
        }

        // Genders must match unless the room is COED
        if ($room->getGender() != $student_gender && $room->getGender() != COED) {
            throw new AssignmentException('Room gender does not match the student\'s gender.');
        }

        // We probably shouldn't check permissions inside this method, since sometimes this can be
        // called from student-facing interfaces.. But, since I want to be really careful with co-ed rooms,
        // I'm going to take the extra step of making sure no students are putting themselves in co-ed rooms.
        if ($room->getGender() == COED && !Current_User::allow('hms', 'coed_assignment')) {
            throw new AssignmentException('You do not have permission to make assignments for Co-ed rooms.');
        }

        // Create the floor object
        $floor = $room->get_parent();
        if (!$floor) {
            throw new AssignmentException('Null floor object.');
        }

        // Create the hall object
        $hall = $floor->get_parent();
        if (!$hall) {
            throw new AssignmentException('Null hall object.');
        }

        /**
         * ***************************
         * Temporary Assignment HACK *
         * ***************************
         */

        // Check for an assignment in the temp assignment table

        $db = new PHPWS_DB('hms_temp_assignment');
        $db->addWhere('banner_id', $student->getBannerId());
        $result = $db->select();

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if (sizeof($result) > 0) {
            // Queue an unassign for this student
            $soap = SOAP::getInstance(UserStatus::getUsername(), UserStatus::isAdmin() ? (SOAP::ADMIN_USER) : (SOAP::STUDENT_USER));
            try {
                $soap->removeRoomAssignment($student->getBannerId(), $term, 'TMPR', $result[0]['room_number'], 100); // Hard-code to 100% refund
            } catch (Exception $e) {
                throw $e;
            }

            $db = new PHPWS_DB('hms_temp_assignment');
            $db->addValue('banner_id', null);
            $db->addWhere('room_number', $result[0]['room_number']);
            $db->update();

            if (PHPWS_Error::logIfError($result)) {
                throw new DatabaseException($result->toString());
            }

            NQ::simple('hms', hms\NotificationView::WARNING, 'Temporary assignment was removed.');
        }

        // Send this off to the queue for assignment in banner
        $banner_success = BannerQueue::queueAssignment($student, $term, $hall, $vacant_bed);
        if ($banner_success !== TRUE) {
            throw new AssignmentException('Error while adding the assignment to the Banner queue.');
        }

        // Make the assignment in HMS
        $assignment = new HMS_Assignment();

        $assignment->setBannerId($student->getBannerId());
        $assignment->asu_username = $username;
        $assignment->bed_id = $vacant_bed->id;
        $assignment->term = $term;
        $assignment->letter_printed = 0;
        $assignment->email_sent = 0;
        $assignment->reason = $reason;
        $assignment->application_term = $student->getApplicationTerm();
        $assignment->class = $student->getComputedClass($term);

        // If this was a lottery assignment, flag it as such
        if ($lottery) {
            $assignment->lottery = 1;
            if (!isset($reason))
                // Automatically tag reason as lottery
                $assignment->reason = ASSIGN_LOTTERY;
        } else {
            $assignment->lottery = 0;
        }

        $result = $assignment->save();

        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        // Log the assignment
        HMS_Activity_Log::log_activity($username, ACTIVITY_ASSIGNED, UserStatus::getUsername(), $term . ' ' . $hall->hall_name . ' ' . $room->room_number . ' ' . $notes);

        // Insert assignment into History table
        AssignmentHistory::makeAssignmentHistory($assignment);

        // Look for roommates and flag their assignments as needing a new letter
        $room_id = $assignment->get_room_id();
        $room = new HMS_Room($room_id);

        // Go to the room level to get all the roommates
        $assignees = $room->get_assignees(); // get an array of student objects for those assigned to this room

        if (sizeof($assignees) > 1) {
            foreach ($assignees as $roommate) {
                // Skip this student
                if ($roommate->getUsername() == $username) {
                    continue;
                }
                $roommate_assign = HMS_Assignment::getAssignment($roommate->getUsername(), $term);
                $roommate_assign->letter_printed = 0;
                $roommate_assign->email_sent = 0;

                $roommate_assign->save();
            }
        }

        // Return Sucess
        return true;
    }

    /**
     * Removes/unassignes a student
     *
     * Valid values for $reason are defined in defines.php.
     *
     * @param Student $student Student to un-assign.
     * @param String $term The term of the assignment to remove.
     * @param String $notes Additional notes for the ActivityLog.
     * @param String $reason Reason string, defined in defines.php
     * @param Integer $refund Percentage of original charges student should be refunded
     * @throws PermissionException
     * @throws InvalidArgumentException
     * @throws AssignmentException
     * @throws DatabaseException
     */
    public static function unassignStudent(Student $student, $term, $notes = "", $reason, $refund)
    {
        if (!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        PHPWS_Core::initModClass('hms', 'BannerQueue.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'AssignmentHistory.php');

        PHPWS_Core::initModClass('hms', 'exception/AssignmentException.php');

        $username = $student->getUsername();

        // Make sure a username was entered
        if (!isset($username) || $username == '') {
            throw new InvalidArgumentException('Bad username.');
        }

        $username = strtolower($username);

        // Check refund field, required field
        if(!isset($refund) || $refund == '') {
            throw new InvalidArgumentException('Please enter a refund percentage.');
        }

        // Refund must be numeric
        if(!is_numeric($refund) || $refund < 0 || $refund > 100) {
            throw new InvalidArgumentException('The refund percentage must be between 0 and 100 percent.');
        }

        // Must be whole number
        if (is_float($refund)) {
            throw new InvalidArgumentException('Only whole number refund percentages are supported, no decimal place is allowed.');
        }

        // Make sure the requested username is actually assigned
        if (!HMS_Assignment::checkForAssignment($username, $term)) {
            throw new AssignmentException('Student is not assigned.');
        }

        $assignment = HMS_Assignment::getAssignment($username, $term);
        if ($assignment == FALSE || $assignment == NULL) {
            throw new AssignmentException('Could not load assignment object.');
        }

        $bed = $assignment->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $building = $floor->get_parent();

        // Attempt to unassign the student in Banner though SOAP
        $banner_result = BannerQueue::queueRemoveAssignment($student, $term, $building, $bed, $refund);

        // Show an error and return if there was an error
        if ($banner_result !== TRUE) {
            throw new AssignmentException('Error while adding the assignment removal to the Banner queue.');
        }

        // Record this before we delete from the db
        $banner_bed_id = $bed->getBannerId();
        $banner_building_code = $building->getBannerBuildingCode();

        // Attempt to delete the assignment in HMS
        $result = $assignment->delete();
        if (!$result) {
            throw new DatabaseException($result->toString());
        }

        // Log in the activity log
        HMS_Activity_Log::log_activity($username, ACTIVITY_REMOVED, UserStatus::getUsername(), $term . ' ' . $banner_building_code . ' ' . $banner_bed_id . ' ' . $notes . 'Refund: ' . $refund);

        // Insert into history table
        AssignmentHistory::makeUnassignmentHistory($assignment, $reason);

        // Generate assignment notices for old roommates
        $assignees = $room->get_assignees(); // get an array of student objects for those assigned to this room

        if (sizeof($assignees) > 1) {
            foreach ($assignees as $roommate) {
                // Skip this student
                if ($roommate->getUsername() == $username) {
                    continue;
                }
                $roommate_assign = HMS_Assignment::getAssignment($roommate->getUsername(), Term::getSelectedTerm());
                $roommate_assign->letter_printed = 0;
                $roommate_assign->email_sent = 0;

                $roommate_assign->save();
            }
        }

        // Show a success message
        return true;
    }

    /**
     *
     * @param Array<BannerRoomChangeStudent> $students Array of BannerRoomChangeStudent objects representing the students to be moved and their to/from beds (hall code + room code)
     * @param string Term
     */
    public static function moveAssignments(Array $students, $term)
    {
        PHPWS_Core::initModClass('hms', 'exception/AssignmentException.php');

        // Update the assignments in Banner through the Web Service
        $soap = SOAP::getInstance(UserStatus::getUsername(), SOAP::ADMIN_USER);
        $soap->moveRoomAssignment($students, $term);

        $numberOfStudents = sizeof($students);

        // Do sanity checks for each student, before we go removing anything
        foreach($students as $student){
            // Get the new bed and corresponding room for sanity checks
            $newBed = $student->getNewBed();
            $room = $newBed->get_parent();

            // TODO: Most of this is duplicated from createRoomAssignment; Refactor so we don't have to do that.

            // Make sure the room is not offline
            if ($room->offline) {
                throw new AssignmentException('The room is offline');
            }

            // Double check that the bed is in the same term as we're being requested to assign for
            if ($newBed->getTerm() != $term) {
                throw new AssignmentException('The bed\'s term and the assignment term do not match.');
            }

            // Sanity checks vary depending on whether this is one student moving (a switch), or multiple students (a swap)
            if($numberOfStudents == 1){
                // There's only one student, so this is a "switch" - destination bed must be empty/available
                // Check that the room has a vacancy
                if (!$room->has_vacancy()) {
                    throw new AssignmentException('The room is full.');
                }

                // Double check that the bed is empty
                if ($newBed->get_number_of_assignees() > 0) {
                    throw new AssignmentException('The bed is not empty.');
                }
            }else{
                // There are > 1 students, so this is a swap. Destination bed can be occupied,
                // so long as its occupant is also in the set of students moving
                $newBedOccupant = $newBed->get_assignee();
                if($newBedOccupant !== null){
                    // Destination bed is occupied, so make sure its current occupant is moving too
                    $destinationOccupantInSet = false;
                    foreach($students as $s){
                        if($newBedOccupant->getBannerId() === $s->banner_id){
                            $destinationOccupantInSet = true;
                            break;
                        }
                    }

                    if(!$destinationOccupantInSet){
                        throw new AssignmentException('Destination bed for ' . $student->banenr_id . ' is not empty, but existing student ' .  $newBedOccupant->getBanenrId() . 'is not moving.');
                    }
                }
            }

            // Get the student's Banner Info
            $studentObj = $student->getStudent();

            // Double check that the student's gender field is set
            $studentGender = $studentObj->getGender();

            if (is_null($studentGender)) {
                throw new AssignmentException('Student gender is null.');
            }
        }

        $db = new \PHPWS_DB();
        $db->query('BEGIN'); //TODO: convert to PDO::beginTransaction()

        // Remove each student from their beds
        foreach($students as $student){
            // Lookup this student's old (current) assignment
            $oldAssignment = HMS_Assignment::getAssignmentByBannerId($student->banner_id, $term);

            // Delete the old assigment
            $result = $oldAssignment->delete();

            // Update the assignment history table to include the removed assignment
            AssignmentHistory::makeUnassignmentHistory($oldAssignment, UNASSIGN_CHANGE);
        }


        // Make each of the new assignments
        foreach($students as $student){

            $newBed = $student->getNewBed();
            $room = $newBed->get_parent();
            $studentObj = $student->getStudent();

            // Genders must match unless the room is COED
            if ($room->getGender() != COED && $room->getGender() != $studentObj->getGender()) {
                throw new AssignmentException('Room gender does not match the student\'s gender.');
            }

            // Make the assignment in HMS
            $assignment = new HMS_Assignment();

            $assignment->setBannerId($studentObj->getBannerId());
            $assignment->asu_username = $studentObj->getUsername();
            $assignment->bed_id = $newBed->id;
            $assignment->term = $term;
            $assignment->letter_printed = 0;
            $assignment->email_sent = 0;
            $assignment->reason = $oldAssignment->getReason();
            $assignment->application_term = $studentObj->getApplicationTerm();
            $assignment->class = $studentObj->getComputedClass($term);

            $result = $assignment->save();

            // Insert assignment into History table
            AssignmentHistory::makeAssignmentHistory($assignment);

            // Log in the activity log
            HMS_Activity_Log::log_activity($studentObj->username, ACTIVITY_ROOM_CHANGE_REASSIGNED, UserStatus::getUsername(), "Room Change Approved in $term From {$student->old_bldg_code} {$student->old_room_code} to {$student->new_bldg_code} {$student->new_room_code}");
        }

        $db->query('COMMIT');

    }
}
