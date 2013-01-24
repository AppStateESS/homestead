<?php

PHPWS_Core::initModClass('hms', 'HMS_Item.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');
PHPWS_Core::initModClass('hms', 'Term.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');
PHPWS_Core::initModClass('hms', 'exception/RoomSwapException.php');

define('ROOM_CHANGE_NEW',              0);
define('ROOM_CHANGE_PENDING',          1);
define('ROOM_CHANGE_RD_APPROVED',      2);
define('ROOM_CHANGE_HOUSING_APPROVED', 3);
define('ROOM_CHANGE_COMPLETED',        4);
define('ROOM_CHANGE_DENIED',           5);
define('ROOM_CHANGE_PAIRING',          6);
define('ROOM_CHANGE_PAIRED',           7);

define('MAX_PREFERENCES',              2);

class RoomChangeRequest extends HMS_Item {

    public $id;
    public $state;
    public $term;
    public $curr_hall;
    public $requested_bed_id;
    public $switch_with;
    public $reason;
    public $cell_phone;
    public $username;
    public $denied_reason;
    public $denied_by;
    public $is_swap;

    public $participants = array();
    public $preferences  = array();

    public static function search($username)
    {
        $db = self::getDb();
        $db->addWhere('username', $username);
        $db->addWhere('state', ROOM_CHANGE_DENIED, '<>');
        $db->addWhere('state', ROOM_CHANGE_COMPLETED, '<>');
        $result = $db->getObjects('RoomChangeRequest');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        //okay, look for a completed/denied change request then...
        if(sizeof($result) == 0) {
            $db = self::getDb();
            $db->addWhere('username', $username);
            $db->addOrder('updated_on desc');
            $result = $db->getObjects('RoomChangeRequest');

            if(PHPWS_Error::logIfError($result)) {
                throw new DatabaseException($result->toString());
            }

            if(sizeof($result) == 0) {
                return NULL;
            }
        }

        $result[0]->load();
        return $result[0];
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_room_change_request');
    }

    public function save()
    {
        $this->state = $this->state->getType(); // replace the object with the id before saving
        $this->stamp();

        //convert bool to int for db
        $this->is_swap = ($this->is_swap ? 1 : 0);

        parent::save();

        $this->savePreferences();
        $this->saveParticipants();
        return true; // will throw an exception on failure, only returns true for backwards compatability
    }

    public function addPreference($id)
    {
        if(in_array($id, HMS_Residence_Hall::get_halls_array(Term::getSelectedTerm()))) {
            return false;
        }
        $this->preferences[] = $id;
    }

    public function savePreferences()
    {
        $db = new PHPWS_DB('hms_room_change_preferences');
        $db->addWhere('request', $this->id);
        //clear if the array is empty
        if(empty($this->preferences)) {
            $result = $db->delete();
            if(PHPWS_Error::logIfError($result))
            throw new DatabaseException($result->toString());
            return true;
        }

        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException('Database Error');
        }

        if(sizeof($results) > MAX_PREFERENCES) {
            throw new DatabaseException("You aren't allowed to prefer that many things!");
        }

        foreach($this->preferences as $preference) {
            $db->reset();
            if(is_array($preference) && isset($preference['id']))
                $db->addValue('id', $preference['id']);
            $db->addValue('request', $this->id);
            $db->addValue('building', is_array($preference) ? $preference['building'] : $preference);
            if(is_array($preference) && isset($preference['id']))
                $result = $db->update();
            else
                $result = $db->insert();
        }
        return true;
    }

    public function saveParticipants()
    {
        $db = new PHPWS_DB('hms_room_change_participants');
        $db->addWhere('request', $this->id);

        //delete participants from table if the participants array is empty
        if(empty($this->participants)) {
            $result = $db->delete();
            if(PHPWS_Error::logIfError($result)) {
                throw new DatabaseException($result->toString());
            }
            return true;
        }

        // Otherwise, get the list of participants
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException($results->toString());
        }

        foreach($this->participants as $participant) {
            $db->reset();
            if(isset($participant['id']))
                $db->addWhere('id', $participant['id']);
            $db->addValue('request', $this->id);
            $db->addValue('role', $participant['role']);
            $db->addValue('username', $participant['username']);
            $db->addValue('name', $participant['name']);
            $db->addValue('updated_on', time());

            if(isset($participant['id'])) {
                $result = $db->update();
            }else{
                $db->addValue('added_on', time());
                $result = $db->insert();
            }

            if(PHPWS_Error::logIfError($result)) {
                throw new DatabaseException($result->toString());
            }
        }
        return true;
    }

    public function load()
    {
        if(!parent::load()) {
            throw new DatabaseException($result->toString());
        }

        //load preferences
        $db = new PHPWS_DB('hms_room_change_preferences');
        $db->addWhere('request', $this->id);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException('Error loading preferences');
        }

        $this->preferences = $results;

        //load participants
        $db = new PHPWS_DB('hms_room_change_participants');
        $db->addWhere('request', $this->id);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException('Error loading participants');
        }

        $this->participants = $results;

        switch($this->state) {
            case 0:
                $this->state = new NewRoomChangeRequest;
                break;
            case 1:
                $this->state = new PendingRoomChangeRequest;
                break;
            case 2:
                $this->state = new RDApprovedChangeRequest;
                break;
            case 3:
                $this->state = new HousingApprovedChangeRequest;
                break;
            case 4:
                $this->state = new CompletedChangeRequest;
                break;
            case 5:
                $this->state = new DeniedChangeRequest;
                break;
            case 6:
                $this->state = new WaitingForPairing;
                break;
            case 7:
                $this->state = new PairedRoomChangeRequest;
                break;
        }

        $this->is_swap = $this->is_swap != 0;

        $this->state->setRequest($this);
    }

    public function handle(CommandContext $context)
    {
    }

    public function onChange()
    {
    }

    public function change(RoomChangeState $state)
    {
        if($this->canChangeState($state)) {
            //only used by denied
            $prev = $this->state;

            $this->state->onExit();
            $this->state = $state; // Set new state
            $this->state->request = $this;
            $this->onChange(); // Call onChange
            $this->state->onEnter($prev);
            $this->state->sendNotification();
            return true;
        }
        throw new RoomSwapException('Could not change state!');
    }

    protected function canChangeState($state)
    {
        return $this->state->canTransition($state);
    }

    public function getRd()
    {
    }

    public function getFloor()
    {
    }

    public function getState()
    {
        return $this->state;
    }

    public function getStatus()
    {
        if($this->state instanceof PendingRoomChangeRequest) {
            return 'Awaiting RD Approval';
        } elseif($this->state instanceof RDApprovedChangeRequest) {
            return 'Awaiting University Housing Approval';
        } elseif($this->state instanceof HousingApprovedChangeRequest) {
            return 'Awaiting Completion';
        } elseif($this->state instanceof CompletedChangeRequest) {
            return 'Completed';
        } elseif($this->state instanceof DeniedChangeRequest) {
            return 'Denied';
        } elseif($this->state instanceof WaitingForPairing) {
            return 'Awaiting Room Swap Pairing';
        } elseif($this->state instanceof PairedRoomChangeRequest) {
            return 'Confirmed swap between '.$this->username.' and '.$this->switch_with;
        }
        return 'Unknown';
    }

    public static function getNew()
    {
        $request = new RoomChangeRequest;
        $request->state = new NewRoomChangeRequest;
        $request->state->request = $request;

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getSelectedTerm());
        $request->addParticipant('student', $student->getUsername(), $student->getFullName());

        $request->username = $student->getUsername();
        $request->term = Term::getSelectedTerm();

        return $request;
    }

    public function addParticipant($role, $username, $name='')
    {
        $this->participants[] = array('role'=>$role, 'username'=>$username, 'name'=>$name);
    }

    public function rdRowFunction()
    {
        //try{
            $this->load();
        //} catch(Exception $e) {
        //}

        $cmd = CommandFactory::getCommand('RDRoomChange');
        $cmd->username = $this->username;

        $student = StudentFactory::getStudentByUsername($this->username, Term::getCurrentTerm());

        $template['NAME']     = $student->getFullName();
        $template['USERNAME'] = $this->username;
        $template['STATUS']   = $this->getStatus();
        $template['ACTIONS']  = $cmd->getLink($this->state->getType() == ROOM_CHANGE_PENDING ? 'Manage' : 'View');
        return $template;
    }

    public function housingRowFunction()
    {
        try{
            $this->load();
        } catch(Exception $e) {
        }

        $actions = array();

        $cmd = CommandFactory::getCommand('HousingRoomChange');
        $cmd->username = $this->username;
        $actions[] = $cmd->getLink($this->state->getType() == ROOM_CHANGE_RD_APPROVED ? 'Manage' : 'View');

        if($this->state->getType() == ROOM_CHANGE_HOUSING_APPROVED) {
            //if it's a room swap our strategy changes completely
            if(!$this->is_swap)
                $cmd = CommandFactory::getCommand('HousingCompleteChange');
            else
                $cmd = CommandFactory::getCommand('HousingCompleteSwap');

            $cmd->username = $this->username;
            $actions[] = $cmd->getLink('Complete');
        }

        /* might be cleaner as a ternary + append... */
        if($this->is_swap)
            $template['USERNAME'] = $this->username . ' and ' . $this->switch_with;
        else
            $template['USERNAME'] = $this->username;

        $template['STATUS']   = $this->getStatus();
        $template['ACTIONS']  = implode($actions, ',');
        return $template;
    }



    public function updateBuddy(RoomChangeState $newState)
    {
        if(!$this->is_swap) { //such a joker
            throw new Exception("This is not a room swap, I can't do that.");
        }

        $buddy = $this->search($this->switch_with);
        if($buddy->change($newState, true)) {
            if($newState instanceof DeniedChangeRequest)
                $buddy->denied_reason = "Other half of the swap was denied.";

            $buddy->save();
        }
    }
}

interface RoomChangeState {
    public function canTransition(RoomChangeState $state);
    public function onEnter($from=NULL);
    public function onExit();
    public function setRequest(RoomChangeRequest $request);
    public function checkPermissions();
    public function addParticipant($role, $username, $name='');
    public function getType();
    public function sendNotification();
}

class BaseRoomChangeState implements RoomChangeState {
    public $request;

    public function getValidTransitions()
    {
        return array();
    }

    public function canTransition(RoomChangeState $state)
    {
        return in_array(get_class($state), $this->getValidTransitions());
    }

    public function onEnter($from=NULL)
    {
        //pass;
    }

    public function onExit()
    {
        //pass;
    }

    public function setRequest(RoomChangeRequest $request)
    {
        $this->request = $request;
    }

    public function checkPermissions()
    {
        return false;
    }

    public function addParticipant($role, $username, $name='')
    {
        $this->request->addParticipant($role, $username, $name);
    }

    public function getType()
    {
        return -1;
    }

    public function reserveRoom($last_command)
    {
        $params = new CommandContext;
        $params->addParam('last_command', $last_command);
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $cmd = CommandFactory::getCommand('ReserveRoom');
        $cmd = $cmd->execute($params);

        return $cmd;
    }

    public function clearReservedFlag($last_command)
    {
        //clear reserved flag

        if(!isset($this->request->requested_bed_id) || is_null($this->request->requested_bed_id)) {
            return;
        }

        $params = new CommandContext;
        $params->addParam('last_command', $last_command);
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $params->addParam('clear', true);
        $cmd = CommandFactory::getCommand('ReserveRoom');
        $cmd = $cmd->execute($params);

        return $cmd;
    }

    public function sendNotification()
    {
        // By default, don't send any notifications.
    }
}

class NewRoomChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions()
    {
        return array('PendingRoomChangeRequest');
    }

    public function checkPermissions()
    {
        //only students
    }
}

class PendingRoomChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions()
    {
        return array('RDApprovedChangeRequest', 'WaitingForPairing', 'DeniedChangeRequest');
    }

    public function getType()
    {
        return ROOM_CHANGE_PENDING;
    }

    public function onEnter($from=NULL)
    {

    }

    public function sendNotification()
    {
        $student    = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());
        $assign     = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());

        $bed        = $assign->get_parent();
        $room       = $bed->get_parent();
        $floor      = $room->get_parent();
        $hall       = $floor->get_parent();

        // Send confirmation to student
        $tpl = array();
        $tpl['NAME']        = $student->getName();
        $tpl['CURR_ASSIGN'] = $assign->where_am_i();
        $tpl['PHONE_NUM']   = $this->request->cell_phone;

        // Send confirmation to student
        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Request Received', 'email/roomChange_submitted_student.tpl', $tpl);

        // Send 'New Room Change' to RD
        $approvers = HMS_Permission::getMembership('room_change_approve', $hall);
        foreach($approvers as $user) {
            HMS_Email::send_template_message($user['username'] . TO_DOMAIN, 'New Room Change Request', 'email/roomChange_submitted_rd.tpl', $tpl);
        }
    }
}

class RDApprovedChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions()
    {
        $valid = array('DeniedChangeRequest');

        if(isset($this->request->requested_bed_id)) {
            $valid[] = 'HousingApprovedChangeRequest';
        } elseif($this->request->is_swap) {
            $valid[] = 'WaitingForPairing';
        }

        return $valid;
    }

    public function onEnter($from=NULL)
    {
        $this->addParticipant('rd', UserStatus::getUsername(), 'University Housing');
        $cmd = $this->reserveRoom('RDRoomChange');

        if($cmd instanceof Command) {
            $cmd->redirect();
        }

        $bed = new HMS_Bed;
        $bed->id = $this->request->requested_bed_id;
        $bed->load();

        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_RD, UserStatus::getUsername(FALSE), "Selected ".$bed->where_am_i());
    }

    public function getType()
    {
        return ROOM_CHANGE_RD_APPROVED;
    }

    public function sendNotification()
    {
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());

        $tpl = array();
        $tpl['NAME']        = $student->getName();

        $tpl['USERNAME']    = $student->getUsername();
        $tpl['BANNER_ID']   = $student->getBannerId();

        // Notify the student
        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Pending Approval', 'email/roomChange_rdApproved_student.tpl', $tpl);

        // Confirm with the user who did it and Housing
        HMS_Email::send_template_message(UserStatus::getUsername() . TO_DOMAIN, 'Room Change Pending Approval', 'email/roomChange_rdApproved_housing.tpl', $tpl);
        HMS_Email::send_template_message(EMAIL_ADDRESS . '@' . DOMAIN_NAME, 'Room Change Pending Approval', 'email/roomChange_rdApproved_housing.tpl', $tpl);
    }
}

class HousingApprovedChangeRequest extends BaseRoomChangeState {

    private $isBuddy = false;

    public function __construct($isBuddy=false)
    {
        $this->isBuddy = $isBuddy;
    }

    public function getValidTransitions()
    {
        return array('CompletedChangeRequest', 'DeniedChangeRequest');
    }

    public function onEnter($from=NULL)
    {
        $this->addParticipant('housing', EMAIL_ADDRESS, 'University Housing');

        $curr_assignment = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());
        $bed = $curr_assignment->get_parent();

        $newBed = new HMS_Bed($this->request->requested_bed_id);

        //if this is a move request
        if($this->request->is_swap) {

            //so long as we aren't the pair... that leads to some non-finite recursion
            if(!$this->isBuddy) {
                $this->request->updateBuddy(new HousingApprovedChangeRequest(true));
            }
            
            $this->request->save();
            $this->request->load();
        }
        
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_HOUSING, UserStatus::getUsername(FALSE), "Approved Room Change to ".$newBed->where_am_i()." from ".$bed->where_am_i());
    }

    public function getType()
    {
        return ROOM_CHANGE_HOUSING_APPROVED;
    }

    public function sendNotification()
    {
        $student    = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());
        $assign     = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());

        $oldBed     = $assign->get_parent();
        $oldRoom    = $oldBed->get_parent();
        $oldFloor   = $oldRoom->get_parent();
        $oldHall    = $oldFloor->get_parent();

        $newBed     = new HMS_Bed($this->request->requested_bed_id);
        $newRoom    = $newBed->get_parent();
        $newFloor   = $newRoom->get_parent();
        $newHall    = $newFloor->get_parent();

        $tpl = array();
        $tpl['NAME']        = $student->getName();
        $tpl['OLD_ROOM']    = $oldRoom->where_am_i();
        $tpl['NEW_ROOM']    = $newRoom->where_am_i();

        // Notify student
        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Approved', 'email/roomChange_housingApproved_student.tpl', $tpl);

        // Notify new roommates
        $newRoommates = $newRoom->get_assignees();

        foreach($newRoommates as $roommate) {
            $tpl['ROOMMATE'] = $roommate->getName();
            HMS_Email::send_template_message($roommate->getUsername() . TO_DOMAIN, 'New Roommate Notification', 'email/roomChange_approved_newRoommate.tpl', $tpl);
        }

        // Notify old roommates
        $oldRoommates = $oldRoom->get_assignees();

        foreach($oldRoommates as $roommate) {
            if($roommate->getUsername() == $student->getUsername()) {
                //Skip the student who actually made the request
                continue;
            }
            $tpl['ROOMMATE'] = $roommate->getName();
            HMS_Email::send_template_message($roommate->getUsername() . TO_DOMAIN, 'Roommate Change Notification', 'email/roomChange_approved_oldRoommate.tpl', $tpl);
        }

        // Notify old and new RDs
        $oldRDs = HMS_Permission::getMembership('room_change_approve', $oldHall);
        $newRDs = HMS_Permission::getMembership('room_change_approve', $newHall);
        $RDs = array_merge($oldRDs, $newRDs);

        foreach($RDs as $rd) {
            HMS_Email::send_template_message($rd['username'] . TO_DOMAIN, 'Room Change Approved', 'email/roomChange_housingApproved_student.tpl', $tpl);
        }
    }
}

class CompletedChangeRequest extends BaseRoomChangeState {

    //state cannot change

    public function onEnter($from=NULL)
    {
        //if this is a swap, then all of this is handled in the command
        //TODO: move this into the complete change command
        if($this->request->is_swap) {
            return;
        }

        //clear reserved flag
        $cmd = $this->clearReservedFlag('HousingRoomChange');

        //if it fails...
        if($cmd instanceof Command) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not clear the reserved flag, assignment was not changed.');
            $cmd->redirect();
        }

        $params = new CommandContext;
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $cmd = CommandFactory::getCommand('RoomChangeAssign');
        $cmd = $cmd->execute($params);

        if($cmd instanceof Command) {
            //redirect on failure
            $cmd->redirect();
        }

        //log
        $newBed = new HMS_Bed($this->request->requested_bed_id);
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_COMPLETED, UserStatus::getUsername(FALSE), "Completed Room change to ".$newBed->where_am_i());
    }

    public function getType()
    {
        return ROOM_CHANGE_COMPLETED;
    }

    public function sendNotification()
    {
        $student    = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());

        $newBed     = new HMS_Bed($this->request->requested_bed_id);
        $newRoom    = $newBed->get_parent();
        $newFloor   = $newRoom->get_parent();
        $newHall    = $newFloor->get_parent();

        $newRDs = HMS_Permission::getMembership('room_change_approve', $newHall);

        $tpl = array();
        $tpl['NAME'] = $student->getName();
        $tpl['USER_NAME'] = $student->getUsername();

        //$tpl['MOVED_FROM'] =
        $tpl['MOVED_TO'] = $newRoom->where_am_i();

        // Notify new RD that move is complete
        foreach($newRDs as $rd) {
            HMS_Email::send_template_message($rd['username'] . TO_DOMAIN, 'Room Change Completed', 'email/roomChange_completed.tpl', $tpl);
        }
    }
}

class DeniedChangeRequest extends BaseRoomChangeState {

    private $isBuddy = false;

    public function __construct($isBuddy=false)
    {
        $this->isBuddy = $isBuddy;
    }

    //state cannot change

    public function onEnter($from=NULL)
    {
        $this->request->denied_by = UserStatus::getUsername();

        $other = is_null($this->request->switch_with) ? NULL : $this->request->search($this->request->switch_with);

        //this will break if from is null, but allowing null makes the interface cleaner
        //therefor ***MAKE SURE THIS ISN'T NULL***

        //if denied by RD
        if($from instanceof PendingRoomChangeRequest) {
            //send back to RD screen
            $this->clearReservedFlag('RDRoomChange');
        } else { //denied by housing
            //send back to housing screen
            $this->clearReservedFlag('HousingRoomChange');
        }


        //if it's a swap and the requests were paired
        if(!is_null($other)
           && $from         instanceof PairedRoomChangeRequest
           && $other->state instanceof PairedRoomChangeRequest) {
            //deny the buddy too if we aren't the buddy
            if(!$this->isBuddy) {
                $this->request->updateBuddy(new DeniedChangeRequest(true));
            }
        }

        //save the state change to the db
        $this->request->save();
        $this->request->load();

        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_DENIED, UserStatus::getUsername(FALSE), $this->request->denied_reason);
    }

    public function getType()
    {
        return ROOM_CHANGE_DENIED;
    }

    public function sendNotification()
    {
        $student    = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());

        $tpl = array();
        $tpl['NAME'] = $student->getName();

        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Denied', 'email/roomChange_denied_housing.tpl', $tpl);
    }
}

class WaitingForPairing extends BaseRoomChangeState {

    public function getValidTransitions()
    {
        return array('PairedRoomChangeRequest', 'DeniedChangeRequest');
    }

    public function getType()
    {
        return ROOM_CHANGE_PAIRING;
    }

    public function onEnter($from=NULL)
    {
        $student    = StudentFactory::getStudentByUsername($this->request->switch_with, Term::getSelectedTerm());
        $assignment = HMS_Assignment::getAssignment($student->getUsername(), Term::getSelectedTerm());

        if(is_null($assignment)) {
            throw new Exception('Requested swap partner is not assigned, cannot complete.');
        }
        
        $this->request->requested_bed_id = $assignment->bed_id;
        $this->request->save();
        $this->request->load();

        $assignment = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());
        $bed = $assignment->get_parent();
        
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_RD, UserStatus::getUsername(FALSE), "Selected ".$bed->where_am_i());
    }

    public function attemptToPair()
    {
        $other = NULL;
        try{
            $other = $this->request->search($this->request->switch_with);
            if(!is_null($other)) {
                $other->load();
            }
        } catch(DatabaseException $e) {
            //pass; broken database is equivalent to NULL here
        }

        if(!is_null($other) && $other->state instanceof WaitingForPairing) {
            $this->request->change(new PairedRoomChangeRequest);
        }

        return !is_null($other);
    }
}

class PairedRoomChangeRequest extends BaseRoomChangeState {
    private $isBuddy = false;

    public function __construct($isBuddy=false)
    {
        $this->isBuddy = $isBuddy;
    }

    public function getValidTransitions()
    {
        return array('HousingApprovedChangeRequest', 'DeniedChangeRequest');
    }

    public function getType()
    {
        return ROOM_CHANGE_PAIRED;
    }

    public function onEnter($from=NULL)
    {
        //if we are not the buddy then notify our buddy of the change, otherwise we're done here
        if(!$this->isBuddy) {
            $this->request->updateBuddy(new PairedRoomChangeRequest(true));
        }

        $this->request->save();
        $this->request->load();
    }

    public function getOther()
    {
        return $other = $this->request->search($this->request->switch_with);
    }
}

?>