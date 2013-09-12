<?php
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


/**
 * Room change types
 *
 * @deprecated
 */
/*
define('ROOM_CHANGE_NEW', 0);
define('ROOM_CHANGE_PENDING', 1);
define('ROOM_CHANGE_RD_APPROVED', 2);
define('ROOM_CHANGE_HOUSING_APPROVED', 3);
define('ROOM_CHANGE_COMPLETED', 4);
define('ROOM_CHANGE_DENIED', 5);
define('ROOM_CHANGE_PAIRING', 6);
define('ROOM_CHANGE_PAIRED', 7);

define('MAX_PREFERENCES', 2);
*/


class RoomChangeRequest {

    public $id;

    public $term;

    // Student's reason for requesting change
    public $reason;

    // Reason this request was denied, will be sent to students
    public $denied_reason_public;

    // Reason this request was denied, will not be shown to students
    public $denied_reason_private;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_room_change_request');
    }

    public function save()
    {
        // convert bool to int for db
        $this->is_swap = ($this->is_swap ? 1 : 0);

        $this->stamp();

        $db = $this->getDb();
        $result = $db->saveObject($this);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        // $this->savePreferences();
        // $this->saveParticipants();

        return true; // will throw an exception on failure, only returns true for backwards compatability
    }

    public function load()
    {
        if (!parent::load()) {
            throw new DatabaseException($result->toString());
        }

        /*
        //load preferences
        $db = new PHPWS_DB('hms_room_change_preferences');
        $db->addWhere('request', $this->id);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException('Error loading preferences');
        }

        $this->preferences = $results;
        */

        /*
        //load participants
        $db = new PHPWS_DB('hms_room_change_participants');
        $db->addWhere('request', $this->id);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException('Error loading participants');
        }

        $this->participants = $results;
        */

        $this->is_swap = $this->is_swap != 0;

        // TODO Create a RoomChangeStateFactory
        switch ($this->state) {
            case 0 :
                $this->state = new NewRoomChangeRequest();
                break;
            case 1 :
                $this->state = new PendingRoomChangeRequest();
                break;
            case 2 :
                $this->state = new RDApprovedChangeRequest();
                break;
            case 3 :
                $this->state = new HousingApprovedChangeRequest();
                break;
            case 4 :
                $this->state = new CompletedChangeRequest();
                break;
            case 5 :
                $this->state = new DeniedChangeRequest();
                break;
            case 6 :
                $this->state = new WaitingForPairing();
                break;
            case 7 :
                $this->state = new PairedRoomChangeRequest();
                break;
        }
    }

    public function addPreference($id)
    {
        if (in_array($id, HMS_Residence_Hall::get_halls_array(Term::getSelectedTerm()))) {
            return false;
        }
        $this->preferences[] = $id;
    }

    /*
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
    */

    /*
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
    */

    public function setState(RoomChangeState $toState)
    {
        if ($this->canChangeState($toState)) {
            // only used by denied
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

    public function getState()
    {
        return $this->state;
    }

    /*
     * TODO: Integrate these status descriptions into each state
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
    */
    public function addParticipant($role, $username, $name = '')
    {
        $this->participants[] = array(
                'role' => $role,
                'username' => $username,
                'name' => $name
        );
    }

    public function rdRowFunction()
    {
        $this->load();

        $cmd = CommandFactory::getCommand('RDRoomChange');
        $cmd->username = $this->username;

        $student = StudentFactory::getStudentByUsername($this->username, Term::getCurrentTerm());

        $template['NAME'] = $student->getFullName();
        $template['USERNAME'] = $this->username;
        $template['STATUS'] = $this->getStatus();
        $template['ACTIONS'] = $cmd->getLink($this->state->getType() == ROOM_CHANGE_PENDING ? 'Manage' : 'View');
        return $template;
    }

    public function housingRowFunction()
    {
        try {
            $this->load();
        } catch (Exception $e) {
        }

        $actions = array();

        $cmd = CommandFactory::getCommand('HousingRoomChange');
        $cmd->username = $this->username;
        $actions[] = $cmd->getLink($this->state->getType() == ROOM_CHANGE_RD_APPROVED ? 'Manage' : 'View');

        if ($this->state->getType() == ROOM_CHANGE_HOUSING_APPROVED) {
            // if it's a room swap our strategy changes completely
            if (!$this->is_swap)
                $cmd = CommandFactory::getCommand('HousingCompleteChange');
            else
                $cmd = CommandFactory::getCommand('HousingCompleteSwap');

            $cmd->username = $this->username;
            $actions[] = $cmd->getLink('Complete');
        }

        /* might be cleaner as a ternary + append... */
        if ($this->is_swap)
            $template['USERNAME'] = $this->username . ' and ' . $this->switch_with;
        else
            $template['USERNAME'] = $this->username;

        $template['STATUS'] = $this->getStatus();
        $template['ACTIONS'] = implode($actions, ',');
        return $template;
    }
}

/**
 * Subclass for resotring RoomChange objects form the database
 * without calling the actual constructor.
 *
 * @author jbooker
 *
 */
class RoomChangeRequestRestored extends RoomChangeRequest {
    /**
     * Emptry constructor to override parent
     */
    public function __construct(){}
}
?>