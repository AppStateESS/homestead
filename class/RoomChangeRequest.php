<?php

PHPWS_Core::initModClass('hms', 'HMS_Item.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');
PHPWS_Core::initModClass('hms', 'Term.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

define('ROOM_CHANGE_NEW',              0);
define('ROOM_CHANGE_PENDING',          1);
define('ROOM_CHANGE_RD_APPROVED',      2);
define('ROOM_CHANGE_HOUSING_APPROVED', 3);
define('ROOM_CHANGE_COMPLETED',        4);
define('ROOM_CHANGE_DENIED',           5);

class RoomChangeRequest extends HMS_Item {
    public $state;
    public $participants;
    public $bed_id;
    public $reason;
    public $cell_number;
    public $preferences;
    public $username;
    public $denied_reason;
    public $denied_by;
    public $term;

    public function search($username){
        $db = $this->getDb();
        $db->addWhere('username', $username);
        $db->addWhere('state', ROOM_CHANGE_DENIED, '<>');
        $db->addWhere('state', ROOM_CHANGE_COMPLETED, '<>');
        $result = $db->getObjects('RoomChangeRequest');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException('Database error!');
        }

        //okay, look for a completed/denied change request then...
        if(sizeof($result) == 0){
            $db = $this->getDb();
            $db->addWhere('username', $username);
            $db->addOrder('updated_on desc');
            $result = $db->getObjects('RoomChangeRequest');

            if(PHPWS_Error::logIfError($result)){
                throw new DatabaseException('Database error!');
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
        return parent::save();
    }

    public function load(){
        if(!parent::load()){
            throw new DatabaseException("Load failed!");
        }

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
        }

        //        $this->loadParticipants();
        //        $this->loadPreferences();
        $this->state->setRequest($this);
    }

    public function handle(CommandContext $context){
    }

    public function onChange(){
    }

    public function change(RoomChangeState $state){
        if($this->canChangeState($state)){
            $this->state->onExit();
            $this->state = $state;
            $this->state->request = $this;
            $this->onChange();
            $this->state->onEnter();
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

    public function getStatus(){
        if($this->state instanceof PendingRoomChangeRequest){
            return 'Awaiting RD Approval';
        } elseif($this->state instanceof RDApprovedChangeRequest){
            return 'Awaiting Housing and Residence Life Approval';
        } elseif($this->state instanceof HousingApprovedChangeRequest){
            return 'Awaiting Completion';
        } elseif($this->state instanceof CompletedChangeRequest){
            return 'Completed';
        } elseif($this->state instanceof DeniedChangeRequest){
            return 'Denied';
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
        try{
            $this->load();
        } catch(Exception $e){
        }

        $cmd = CommandFactory::getCommand('RDRoomChange');
        $cmd->username = $this->username;

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
            $cmd = CommandFactory::getCommand('HousingCompleteChange');
            $cmd->username = $this->username;
            $actions[] = $cmd->getLink('Complete');
        }

        $template['USERNAME'] = $this->username;
        $template['STATUS']   = $this->getStatus();
        $template['ACTIONS']  = implode($actions, ',');
        return $template;
    }
}

interface RoomChangeState {
    public function canTransition(RoomChangeState $state);
    public function onEnter();
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

    public function onEnter(){
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
        $this->request->addParticipant($username, $name);
    }

    public function getType(){
        return -1;
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
        return array('RDApprovedChangeRequest', 'DeniedChangeRequest');
    }

    public function getType(){
        return ROOM_CHANGE_PENDING;
    }
}

class RDApprovedChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions(){
        return array('HousingApprovedChangeRequest', 'DeniedChangeRequest');
    }

    public function onEnter(){
        $this->addParticipant('rd', UserStatus::getUsername(), 'Housing and Residence Life');
        $params = new CommandContext;
        $params->addParam('last_command', 'RDRoomChange');
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->bed_id);
        $cmd = CommandFactory::getCommand('ReserveRoom');
        $cmd = $cmd->execute($params);

        if($cmd instanceof Command){
            $cmd->redirect();
        }
    }

    public function getType(){
        return ROOM_CHANGE_RD_APPROVED;
    }
}

class HousingApprovedChangeRequest extends BaseRoomChangeState {

    public function getValidTransitions(){
        return array('CompletedChangeRequest', 'DeniedChangeRequest');
    }

    public function onEnter(){
        $this->addParticipant('housing', 'hrlassignments', 'Housing and Residence Life');
    }

    public function getType(){
        return ROOM_CHANGE_HOUSING_APPROVED;
    }
}

class CompletedChangeRequest extends BaseRoomChangeState {

    //state cannot change

    public function onEnter(){
        //clear reserved flag
        $this->addParticipant('rd', UserStatus::getUsername(), 'Housing and Residence Life');
        $params = new CommandContext;
        $params->addParam('last_command', 'RDRoomChange');
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->bed_id);
        $params->addParam('clear', true);
        $cmd = CommandFactory::getCommand('ReserveRoom');
        $cmd = $cmd->execute($params);

        $params = new CommandContext;
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->bed_id);
        $params->addParam('moveConfirmed', 'true');
    }

    public function getType(){
        return ROOM_CHANGE_COMPLETED;
    }
}

class DeniedChangeRequest extends BaseRoomChangeState {
    
    //state cannot change

    public function onEnter(){
        //set denied reason
    }

    public function getType(){
        return ROOM_CHANGE_DENIED;
    }
}

?>