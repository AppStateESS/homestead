<?php
PHPWS_Core::initModClass('hms', 'RoomChangeRequestState.php');


/**
 * Room change types
 *
 * @deprecated
 *
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

/**
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeRequest {

    protected $id;

    protected $term;

    // Student's reason for requesting change
    protected $reason;

    // Reason this request was denied, will be sent to students
    protected $deniedReasonPublic;

    // Reason this request was denied, will not be shown to students
    protected $deniedReasonPrivate;

    protected $state;
    protected $stateChanged; // true if the state has been updated

    /**
     * Create a new RoomChangeReuqest.
     *
     * @param integer $term
     * @param String $reason
     */
    public function __construct($term, $reason)
    {
        $this->id = 0;

        $this->term = $term;
        $this->reason = $reason;

        // Set initial state
        $this->setState(new RoomChangeStatePending($this, time(), null, UserStatus::getUsername()));
    }

    public function save()
    {
        $db = PdoFactory::getPdoInstance();

        // Begin a new transaction
        $db->beginTransaction();

        $params = array(
                'term' => $this->getTerm(),
                'reason' => $this->getReason(),
                'deniedReasonPublic' => $this->getDeniedReasonPublic(),
                'deniedReasonPrivate' => $this->getDeniedReasonPrivate()
        );

        if ($this->id == 0) {
            // Insert for new record
            $query = "INSERT INTO hms_room_change_request (id, term, reason, denied_reason_public, denied_reason_private) VALUES (nextval('hms_room_change_request_seq'), :term, :reason, :deniedReasonPublic, :deniedReasonPrivate)";
        } else {
            throw new Exception('Not yet implemented');
            // Update for existing record
            $query = "";
            $params[id] = $this->getId();
        }


        $stmt = $db->prepare($query);
        $stmt->execute($params);

        // If this request doesn't have an ID, then save the ID of the row inserted
        if ($this->id == 0) {
            $this->id = $db->lastInsertId('hms_room_change_request_seq');
        }

        // If state changed, save the new state
        if ($this->stateChanged()) {
            $this->state->save();
        }

        // Close the transaction
        $db->commit();

        return true; // will throw an exception on failure, only returns true for backwards compatability
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function isDenied()
    {
        $state = $this->getState();

        if ($state == 'Denied') {
            return true;
        }else{
            return false;
        }
    }

    public function getDeniedReasonPublic()
    {
        return $this->deniedReasonPublic;
    }

    public function getDeniedReasonPrivate()
    {
        return $this->deniedReasonPrivate;
    }

    private function setState(RoomChangeRequestState $state)
    {
        $this->state = $state;
        $this->stateChanged = true;
    }

    public function getState()
    {
        return $this->state;
    }

    public function stateChanged()
    {
        if($this->stateChanged){
            return true;
        }

        return false;
    }

    public function transitionTo(RoomChangeRequestState $toState)
    {
        if (!$this->state->canChangeState($toState)) {
            throw new InvalidArgumentException("Invalid state change from: {$this->state->getName()} to {$toState->getName()}.");
        }

        // Set the end date on the current state
        $this->state->setEffectiveUntilDate(time());
        $this->state->update(); // Save changes to current state

        // Set the new state as the current state
        $this->setState($toState);

        $this->state->save(); // Save the new state

        // Send notifications
        $this->state->sendNotification();
    }

    public function getParticipants()
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');

        return RoomChangeParticipantFactory::getParticipantsByRequest($this);
    }

    /**
     * *********** OLD CODE BELOW **********************
     */

    /*
    public function addParticipant($role, $username, $name = '')
    {
        $this->participants[] = array(
                'role' => $role,
                'username' => $username,
                'name' => $name
        );
    }
    */

    /*
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
    */

    /*
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

        // might be cleaner as a ternary + append...
        if ($this->is_swap)
            $template['USERNAME'] = $this->username . ' and ' . $this->switch_with;
        else
            $template['USERNAME'] = $this->username;

        $template['STATUS'] = $this->getStatus();
        $template['ACTIONS'] = implode($actions, ',');
        return $template;
    }
    */
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
    public function __construct()
    {
        //TODO Use the RoomChangeRequestStateFactory to load this room change's current state
    }
}
?>