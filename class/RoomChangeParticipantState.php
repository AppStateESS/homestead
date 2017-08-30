<?php

namespace Homestead;

class RoomChangeParticipantState {

    const STATE_NAME = 'ParentState'; // Text state name
    const FRIENDLY_NAME = 'Parent State'; // Friendly (user-readable) state name

    //private $request; // Reference fo the request object

    private $participantId; // Id of participant. We don't keep a reference to the Participant object to avoid circular references
    private $effectiveDate; // Unix timestamp where object enetered this state
    private $effectiveUntilDate; // Unix timestamp where object left this state
    private $committedBy; // User who changed to this state

    /**
     * Constructor
     * @param RoomChangeRequest $request
     * @param unknown $effectiveDate
     * @param unknown $effectiveUntilDate
     * @param unknown $committedBy
     */
    public function __construct(RoomChangeParticipant $participant, $effectiveDate, $effectiveUntilDate = null, $committedBy)
    {
        $this->participantId        = $participant->getId();
        $this->effectiveDate        = $effectiveDate;
        $this->effectiveUntilDate   = $effectiveUntilDate;
        $this->committedBy          = $committedBy;
    }

    public function save()
    {
        $db = PdoFactory::getPdoInstance();

        $query = "INSERT INTO hms_room_change_participant_state (participant_id, state_name, effective_date, effective_until_date, committed_by) VALUES (:participantId, :state, :effectiveDate, :effectiveUntilDate, :committedBy)";
        $stmt = $db->prepare($query);

        $params = array(
                'participantId'         => $this->getParticipantId(),
                'state'                 => $this->getName(),
                'effectiveDate'         => $this->getEffectiveDate(),
                'effectiveUntilDate'    => $this->getEffectiveUntilDate(),
                'committedBy'           => $this->getCommittedBy()
        );

        $stmt->execute($params);
    }

    public function update()
    {
        $db = PdoFactory::getPdoInstance();

        $query = "UPDATE hms_room_change_participant_state SET effective_until_date = :effectiveUntilDate WHERE participant_id = :participantId AND state_name = :state AND effective_date = :effectiveDate";
        $stmt = $db->prepare($query);

        $params = array(
                'participantId'         => $this->getParticipantId(),
                'state'                 => $this->getName(),
                'effectiveDate'         => $this->getEffectiveDate(),
                'effectiveUntilDate'    => $this->getEffectiveUntilDate(),
        );

        $stmt->execute($params);
    }

    public function getValidTransitions()
    {
        throw new \Exception('No transitions implemented.');
    }

    public function canTransition(RoomChangeParticipantState $toState)
    {
        return in_array(get_class($toState), $this->getValidTransitions());
    }

    public function getName()
    {
        return static::STATE_NAME;
    }

    public function getFriendlyName()
    {
        return static::FRIENDLY_NAME;
    }

    public function getParticipantId()
    {
        return $this->participantId;
    }

    public function setParticipantId($id)
    {
        $this->participantId = $id;
    }

    public function getEffectiveDate()
    {
        return $this->effectiveDate;
    }

    public function getEffectiveUntilDate()
    {
        return $this->effectiveUntilDate;
    }

    public function setEffectiveUntilDate($date)
    {
        $this->effectiveUntilDate = $date;
    }

    public function getCommittedBy()
    {
        return $this->committedBy;
    }

    public function sendNotification()
    {
        // By default, don't send any notifications.
    }
}
