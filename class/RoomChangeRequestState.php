<?php

namespace Homestead;

abstract class RoomChangeRequestState {

    const STATE_NAME = 'ParentState'; // Text state name

    private $request; // Reference fo the request object

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
    public function __construct(RoomChangeRequest $request, $effectiveDate, $effectiveUntilDate = null, $committedBy)
    {
        $this->request = $request;
        $this->effectiveDate = $effectiveDate;
        $this->effectiveUntilDate = $effectiveUntilDate;
        $this->committedBy = $committedBy;
    }

    public function save()
    {
        $db = PdoFactory::getPdoInstance();

        $query = "INSERT INTO hms_room_change_request_state (request_id, state_name, effective_date, effective_until_date, committed_by) VALUES (:requestId, :state, :effectiveDate, :effectiveUntilDate, :committedBy)";
        $stmt = $db->prepare($query);

        $params = array(
                'requestId' => $this->request->getId(),
                'state' => $this->getName(),
                'effectiveDate' => $this->getEffectiveDate(),
                'effectiveUntilDate' => $this->getEffectiveUntilDate(),
                'committedBy' => $this->getCommittedBy()
        );

        $stmt->execute($params);
    }

    public function update()
    {
        $db = PdoFactory::getPdoInstance();

        $query = "UPDATE hms_room_change_request_state SET effective_until_date = :effectiveUntilDate WHERE request_id = :requestId AND state_name = :state AND effective_date = :effectiveDate";
        $stmt = $db->prepare($query);

        $params = array(
                'requestId'         => $this->request->getId(),
                'state'                 => $this->getName(),
                'effectiveDate'         => $this->getEffectiveDate(),
                'effectiveUntilDate'    => $this->getEffectiveUntilDate(),
        );

        $stmt->execute($params);
    }

    public function getName()
    {
        return static::STATE_NAME;
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

    public function getValidTransitions()
    {
        throw new \Exception('No transitions implemented.');
    }

    public function canTransition(RoomChangeRequestState $toState)
    {
        return in_array(get_class($toState), $this->getValidTransitions());
    }
}
