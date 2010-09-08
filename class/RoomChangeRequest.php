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

    public $participants = array();
    public $preferences  = array();

    public static function search($username){
        $db = self::getDb();
        $db->addWhere('username', $username);
        $db->addWhere('state', ROOM_CHANGE_DENIED, '<>');
        $db->addWhere('state', ROOM_CHANGE_COMPLETED, '<>');
        $result = $db->getObjects('RoomChangeRequest');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        //okay, look for a completed/denied change request then...
        if(sizeof($result) == 0){
            $db = self::getDb();
            $db->addWhere('username', $username);
            $db->addOrder('updated_on desc');
            $result = $db->getObjects('RoomChangeRequest');

            if(PHPWS_Error::logIfError($result)){
                PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
                throw new DatabaseException($result->toString());
            }

            if(sizeof($result) == 0){
                return NULL;
            }
        }

        $result[0]->load();
        return $result[0];
    }

    public function getDb(){
        return new PHPWS_DB('hms_room_change_request');
    }

    public function save(){
        $this->state = $this->state->getType(); // replace the object with the id before saving
        $this->stamp();

        //get the id of the hall they are currently in, so that we can filter the rd pager later
        $assignment = HMS_Assignment::getAssignment($this->username, Term::getSelectedTerm());

        if(!isset($assignment)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You are not currently assigned to a room, so you cannot request a room change.');
            $errorCmd = CommandFactory::getCommand('ShowStudentMenu');
            $errorCmd->redirect();
        }

        //sanity check
        if(isset($this->switch_with) && $this->switch_with == $this->username){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Please select someone other than yourself to switch rooms with.");
            $errorCmd = CommandFactory::getCommand('StudentRoomChange');
            $errorCmd->redirect();
        }

        $building = $assignment->get_parent()->get_parent()->get_parent()->get_parent();
        $this->curr_hall = $building->id;

        parent::save();

        $this->savePreferences();
        $this->saveParticipants();
        return true; // will throw an exception on failure, only returns true for backwards compatability
    }

    public function addPreference($id){
        if(in_array($id, HMS_Residence_Hall::get_halls_array(Term::getSelectedTerm()))){
            return false;
        }
        $this->preferences[] = $id;
    }

    public function savePreferences(){
        $db = new PHPWS_DB('hms_room_change_preferences');
        $db->addWhere('request', $this->id);
        //clear if the array is empty
        if(empty($this->preferences)){
            $result = $db->delete();
            if(PHPWS_Error::logIfError($result))
            throw new DatabaseException($result->toString());
            return true;
        }

        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException('Database Error');
        }

        if(sizeof($results) > MAX_PREFERENCES){
            throw new DatabaseException("You aren't allowed to prefer that many things!");
        }

        foreach($this->preferences as $preference){
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

    public function saveParticipants(){
        $db = new PHPWS_DB('hms_room_change_participants');
        $db->addWhere('request', $this->id);

        //delete participants from table if the participants array is empty
        if(empty($this->participants)){
            $result = $db->delete();
            if(PHPWS_Error::logIfError($result)){
                PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
                throw new DatabaseException($result->toString());
            }
            return true;
        }

        // Otherwise, get the list of participants
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($results->toString());
        }

        foreach($this->participants as $participant){
            $db->reset();
            if(isset($participant['id']))
                $db->addWhere('id', $participant['id']);
            $db->addValue('request', $this->id);
            $db->addValue('role', $participant['role']);
            $db->addValue('username', $participant['username']);
            $db->addValue('name', $participant['name']);
            $db->addValue('updated_on', time());

            if(isset($participant['id'])){
                $result = $db->update();
            }else{
                $db->addValue('added_on', time());
                $result = $db->insert();
            }

            if(PHPWS_Error::logIfError($result)){
                PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
                throw new DatabaseException($result->toString());
            }
        }
        return true;
    }

    public function load(){
        if(!parent::load()){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        //load preferences
        $db = new PHPWS_DB('hms_room_change_preferences');
        $db->addWhere('request', $this->id);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException('Error loading preferences');
        }

        $this->preferences = $results;

        //load participants
        $db = new PHPWS_DB('hms_room_change_participants');
        $db->addWhere('request', $this->id);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException('Error loading participants');
        }

        $this->participants = $results;

        switch($this->state){
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

        $this->state->setRequest($this);
    }

    public function handle(CommandContext $context){
    }

    public function onChange(){
    }

    public function change(RoomChangeState $state){
        if($this->canChangeState($state)){
            //only used by denied
            $prev = $this->state;

            $this->state->onExit();
            $this->state = $state;

            $this->state->request = $this;
            $this->onChange();
            $this->state->onEnter($prev);
            return true;
        }
        return false;
    }

    protected function canChangeState($state){
        return $this->state->canTransition($state);
    }

    public function getRd(){
    }

    public function getFloor(){
    }

    public function getState()
    {
        return $this->state;
    }

    public function getStatus(){
        if($this->state instanceof PendingRoomChangeRequest){
            return 'Awaiting RD Approval';
        } elseif($this->state instanceof RDApprovedChangeRequest){
            return 'Awaiting University Housing Approval';
        } elseif($this->state instanceof HousingApprovedChangeRequest){
            return 'Awaiting Completion';
        } elseif($this->state instanceof CompletedChangeRequest){
            return 'Completed';
        } elseif($this->state instanceof DeniedChangeRequest){
            return 'Denied';
        } elseif($this->state instanceof WaitingForPairing){
            return 'Awaiting Room Swap Pairing';
        } elseif($this->state instanceof PairedRoomChangeRequest){
            return 'Confirmed swap between '.$this->username.' and '.$this->switch_with;
        }
        return 'Unknown';
    }

    public static function getNew(){
        $request = new RoomChangeRequest;
        $request->state = new NewRoomChangeRequest;
        $request->state->request = $request;

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getSelectedTerm());
        $request->addParticipant('student', $student->getUsername(), $student->getFullName());

        $request->username = $student->getUsername();
        $request->term = Term::getSelectedTerm();

        return $request;
    }

    public function addParticipant($role, $username, $name=''){
        $this->participants[] = array('role'=>$role, 'username'=>$username, 'name'=>$name);
    }

    public function rdRowFunction(){
        //try{
            $this->load();
        //} catch(Exception $e){
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

    public function housingRowFunction(){
        try{
            $this->load();
        } catch(Exception $e){
        }

        $actions = array();

        $cmd = CommandFactory::getCommand('HousingRoomChange');
        $cmd->username = $this->username;
        $actions[] = $cmd->getLink($this->state->getType() == ROOM_CHANGE_RD_APPROVED ? 'Manage' : 'View');

        if($this->state->getType() == ROOM_CHANGE_HOUSING_APPROVED){
            //if it's a room swap our strategy changes completely
            if(empty($this->switch_with))
                $cmd = CommandFactory::getCommand('HousingCompleteChange');               
            else
                $cmd = CommandFactory::getCommand('HousingCompleteSwap');

            $cmd->username = $this->username;
            $actions[] = $cmd->getLink('Complete');
        }

        /* might be cleaner as a ternary + append... */
        if(!empty($this->switch_with))
            $template['USERNAME'] = $this->username . ' and ' . $this->switch_with;
        else
            $template['USERNAME'] = $this->username;

        $template['STATUS']   = $this->getStatus();
        $template['ACTIONS']  = implode($actions, ',');
        return $template;
    }

    public function emailParticipants($subject, $status){
        $tags = array();
        $tags['STUDENT'] = $this->username;
        $tags['PHONE']   = $this->cell_phone;

        $student = StudentFactory::getStudentByUsername($this->username, Term::getSelectedTerm());
        $tags['NAME'] = $student->getName();

        foreach($this->participants as $participant){
            HMS_Email::send_template_message($participant['username'], $subject, 'email/roomChange_' . $status . '_' . $participant['role'] . '.tpl', $tags);
        }
    }

    public function updateBuddy(RoomChangeState $newState){
        if(is_null($this->switch_with)){ //such a joker
            throw new Exception("This is not a room swap, I can't do that.");
        }

        $buddy = $this->search($this->switch_with);
        $buddy->change($newState);
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
}

class BaseRoomChangeState implements RoomChangeState {
    public $request;

    public function getValidTransitions(){
        return array();
    }

    public function canTransition(RoomChangeState $state){
        return in_array(get_class($state), $this->getValidTransitions());
    }

    public function onEnter($from=NULL){
        //pass;
    }

    public function onExit(){
        //pass;
    }

    public function setRequest(RoomChangeRequest $request){
        $this->request = $request;
    }

    public function checkPermissions(){
        return false;
    }

    public function addParticipant($role, $username, $name=''){
        $this->request->addParticipant($role, $username, $name);
    }

    public function getType(){
        return -1;
    }

    public function reserveRoom($last_command){
        $params = new CommandContext;
        $params->addParam('last_command', $last_command);
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $cmd = CommandFactory::getCommand('ReserveRoom');
        $cmd = $cmd->execute($params);

        return $cmd;
    }

    public function clearReservedFlag($last_command){
        //clear reserved flag

        if(!isset($this->request->requested_bed_id) || is_null($this->request->requested_bed_id)){
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
}

class NewRoomChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions(){
        return array('PendingRoomChangeRequest');
    }

    public function checkPermissions(){
        //only students
    }
}

class PendingRoomChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions(){
        return array('RDApprovedChangeRequest', 'WaitingForPairing', 'DeniedChangeRequest');
    }

    public function getType(){
        return ROOM_CHANGE_PENDING;
    }

    public function onEnter($from=NULL){
        $this->request->emailParticipants('Your room change request has been submitted', 'pending');
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_SUBMITTED, UserStatus::getUsername(FALSE), $this->request->reason);
    }
}

class RDApprovedChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions(){
        $valid = array('DeniedChangeRequest');

        if(isset($this->request->requested_bed_id)){
            $valid[] = 'HousingApprovedChangeRequest';
        } elseif(isset($this->request->switch_with)){
            $valid[] = 'WaitingForPairing';
        }

        return $valid;
    }

    public function onEnter($from=NULL){
        $this->addParticipant('rd', UserStatus::getUsername(), 'University Housing');
        $cmd = $this->reserveRoom('RDRoomChange');

        if($cmd instanceof Command){
            $cmd->redirect();
        }

        $bed = new HMS_Bed;
        $bed->id = $this->request->requested_bed_id;
        $bed->load();

        $this->request->emailParticipants('Room Change Request Approved!', 'rd_approved');
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_RD, UserStatus::getUsername(FALSE), "Selected ".$bed->where_am_i());
    }

    public function getType(){
        return ROOM_CHANGE_RD_APPROVED;
    }
}

class HousingApprovedChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions(){
        return array('CompletedChangeRequest', 'DeniedChangeRequest');
    }

    public function onEnter($from=NULL){
        $this->addParticipant('housing', EMAIL_ADDRESS, 'University Housing');
        $this->request->emailParticipants('Housing Approved Room Change!', 'housing_approved');

        //if this is a move request
        if(is_null($this->request->switch_with)){
            $curr_assignment = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());
            $bed = $curr_assignment->get_parent();

            $newBed = new HMS_Bed;
            $newBed->id = $this->request->requested_bed_id;
            $newBed->load();
            HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_HOUSING, UserStatus::getUsername(FALSE), "Approved Room Change to ".$newBed->where_am_i()." from ".$bed->where_am_i());
        } else { //if it's a swap
            /* Update our state in the db, don't touch this.  Trust me. */
            $this->request->save();
            $this->request->load();
         
            //then approving the swap should also update it's pair
            $this->request->updateBuddy(new HousingApprovedChangeRequest);
        }
    }

    public function getType(){
        return ROOM_CHANGE_HOUSING_APPROVED;
    }
}

class CompletedChangeRequest extends BaseRoomChangeState {

    //state cannot change

    public function onEnter($from=NULL){
        //if this is a swap, then all of this is handled in the command
        //TODO: move this into the complete change command
        if(!empty($this->request->switch_with)){
            return;
        }

        //clear reserved flag
        $cmd = $this->clearReservedFlag('HousingRoomChange');

        //if it fails...
        if($cmd instanceof Command){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not clear the reserved flag, assignment was not changed.');
            $cmd->redirect();
        }

        $params = new CommandContext;
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $cmd = CommandFactory::getCommand('RoomChangeAssign');
        $cmd = $cmd->execute($params);

        if($cmd instanceof Command){
            //redirect on failure
            $cmd->redirect();
        }

        //email participants
        $this->request->emailParticipants('Room Change Complete!', 'completed');

        //log
        $newBed = new HMS_Bed;
        $newBed->id = $this->request->requested_bed_id;
        $newBed->load();
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_COMPLETED, UserStatus::getUsername(FALSE), "Completed Room change to ".$newBed->where_am_i());
    }

    public function getType(){
        return ROOM_CHANGE_COMPLETED;
    }
}

class DeniedChangeRequest extends BaseRoomChangeState {

    //state cannot change

    public function onEnter($from=NULL){
        $this->request->emailParticipants('Room Change Denied', 'denied');
        $this->request->denied_by = UserStatus::getUsername();

        $other = is_null($this->request->switch_with) ? NULL : $this->request->search($this->request->switch_with);

        //this will break if from is null, but allowing null makes the interface cleaner
        //therefor ***MAKE SURE THIS ISN'T NULL***

        //if denied by RD
        if($from instanceof PendingRoomChangeRequest){
            //send back to RD screen
            $this->clearReservedFlag('RDRoomChange');
        } else { //denied by housing
            //send back to housing screen
            $this->clearReservedFlag('HousingRoomChange');
        }

        //if it's a swap and the requests were paired
        if(!is_null($other)
           && $from         == PairedRoomChangeRequest
           && $other->state == PairedRoomChangeRequest){
            //Fixes recursion, don't touch it for now...
            $this->request->save();
            $this->request->load();

            //deny the buddy too
            $this->request->updateBuddy(new DeniedChangeRequest);
        }
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_DENIED, UserStatus::getUsername(FALSE), $this->request->denied_reason);
    }

    public function getType(){
        return ROOM_CHANGE_DENIED;
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
        /* Double save was causing some bugs, commenting out for deadline
        //look for roommate pair
        $paired = $this->attemptToPair();

        if($paired){
            $this->request->save();
        }
        */
    }

    public function attemptToPair()
    {
        $other = NULL;
        try{
            $other = $this->request->search($this->request->switch_with);
            $other->load();
        } catch(DatabaseException $e){
            //pass; broken database is equivalent to NULL here
        }

        if(!is_null($other) && $other->state instanceof WaitingForPairing){
            $this->request->change(new PairedRoomChangeRequest);
            $this->request->updateBuddy(new PairedRoomChangeRequest);
        }

        return !is_null($other);
    }
}

class PairedRoomChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions(){
        return array('HousingApprovedChangeRequest', 'DeniedChangeRequest');
    }

    public function getType(){
        return ROOM_CHANGE_PAIRED;
    }

    public function onEnter($from=NULL){
        /* Prevent recursion, TODO: cleanup
           For now don't touch this. */
        $this->request->save();
        $this->request->load();

        $this->request->updateBuddy(new PairedRoomChangeRequest);    
    }

    public function getOther(){
        return $other = $this->request->search($this->request->switch_with);
    }
}

//?>