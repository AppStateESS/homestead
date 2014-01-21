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
    protected $denied_reason_public;

    // Reason this request was denied, will not be shown to students
    protected $denied_reason_private;

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

        if ($this->id == 0) {
            $params = array(
                    'term' => $this->getTerm(),
                    'reason' => $this->getReason(),
                    'deniedReasonPublic' => $this->getDeniedReasonPublic(),
                    'deniedReasonPrivate' => $this->getDeniedReasonPrivate()
            );

            // Insert for new record
            $query = "INSERT INTO hms_room_change_request (id, term, reason, denied_reason_public, denied_reason_private) VALUES (nextval('hms_room_change_request_seq'), :term, :reason, :deniedReasonPublic, :deniedReasonPrivate)";
        } else {

            $params = array(
                    'id'                    => $this->getId(),
                    'deniedReasonPublic'    => $this->getDeniedReasonPublic(),
                    'deniedReasonPrivate'   => $this->getDeniedReasonPrivate()
            );

            // Update for existing record
            $query = "UPDATE hms_room_change_request SET (denied_reason_public, denied_reason_private) = (:deniedReasonPublic, :deniedReasonPrivate) WHERE id = :id";
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

    public function transitionTo(RoomChangeRequestState $toState)
    {

        // Be sure we have the latest state
        if(is_null($this->state)){
            $this->getState();
        }

        if (!$this->state->canTransition($toState)) {
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

    public function getState()
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestStateFactory.php');

        $this->state = RoomChangeRequestStateFactory::getCurrentState($this);
        return $this->state;
    }

    public function getAllPotentialApprovers()
    {
        $participants = $this->getParticipants();

        $approvers = array();
        foreach ($participants as $p) {
            // Get approvers for current bed
            $approvers = array_merge($approvers, $p->getCurrentRdList());

            // If there's a destination bed set, merge its approvers in
            $destinationBedId = $p->getToBed();
            if (isset($destinationBedId)) {
                $approvers = array_merge($approvers, $p->getFutureRdList());
            }
        }

        return array_unique($approvers);
    }

    public function getParticipants()
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');

        return RoomChangeParticipantFactory::getParticipantsByRequest($this);
    }

    public function getParticipantUsernames()
    {
        $participants = $this->getParticipants();

        $users = array();
        foreach ($participants as $p) {
            $student = StudentFactory::getStudentByBannerId($p->getBannerId(), $this->getTerm());
            $users[] = $student->getUsername();
        }

        return $users;
    }

    protected function hasParticipantInState(array $states)
    {
        foreach($this->getParticipants() as $p) {
            if(in_array($p->getState()->getName(), $states)) {
                return true;
            }
        }

        return false;
    }

    public function isApprovedByAllParticipants()
    {
        return !$this->hasParticipantInState(array('New'));
    }

    public function isApprovedByAllCurrentRDs()
    {
        return !$this->hasParticipantInState(array('New', 'StudentApproved'));
    }

    public function isApprovedByAllFutureRDs()
    {
        return !$this->hasParticipantInState(array('New', 'StudentApproved', 'CurrRdApproved'));
    }

    /*********************
     * Get / Set Methods *
     *********************/

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

    public function getDeniedReasonPublic()
    {
        return $this->denied_reason_public;
    }

    public function setDeniedReasonPublic($reason)
    {
        $this->denied_reason_public = $reason;
    }

    public function getDeniedReasonPrivate()
    {
        return $this->denied_reason_private;
    }

    public function setDeniedReasonPrivate($reason)
    {
        $this->denied_reason_private = $reason;
    }

    private function setState(RoomChangeRequestState $state)
    {
        $this->state = $state;
        $this->stateChanged = true;
    }

    public function stateChanged()
    {
        if($this->stateChanged){
            return true;
        }

        return false;
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
    public function __construct()
    {
        //TODO Use the RoomChangeRequestStateFactory to load this room change's current state
    }
}
?>