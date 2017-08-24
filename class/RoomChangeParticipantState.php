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


class ParticipantStateNew extends RoomChangeParticipantState {
    const STATE_NAME = 'New';
    const FRIENDLY_NAME = 'Created';

    public function getValidTransitions()
    {
        return array('ParticipantStateStudentApproved', 'ParticipantStateDenied', 'ParticipantStateDeclined', 'ParticipantStateCancelled');
    }
}

class ParticipantStateStudentApproved extends RoomChangeParticipantState {
    const STATE_NAME = 'StudentApproved';
    const FRIENDLY_NAME = 'Student Approved';

    public function getValidTransitions()
    {
        return array('ParticipantStateCurrRdApproved', 'ParticipantStateDenied', 'ParticipantStateCancelled');
    }

    //TODO Send notification to current RD
}

class ParticipantStateCurrRdApproved extends RoomChangeParticipantState {
    const STATE_NAME = 'CurrRdApproved';
    const FRIENDLY_NAME = 'Current RD Approved';

    public function getValidTransitions()
    {
        return array('ParticipantStateFutureRdApproved', 'ParticipantStateDenied', 'ParticipantStateCancelled');
    }

    // TODO send notification to future RD
}

class ParticipantStateFutureRdApproved extends RoomChangeParticipantState {
    const STATE_NAME = 'FutureRdApproved';
    const FRIENDLY_NAME = 'Future RD Approved';

    public function getValidTransitions()
    {
        return array('ParticipantStateInProcess', 'ParticipantStateDenied', 'ParticipantStateCancelled');
    }

    // TODO If all participants are FutureRdApproved, send notification to Housing
}

class ParticipantStateInProcess extends RoomChangeParticipantState {
    const STATE_NAME = 'InProcess';
    const FRIENDLY_NAME = 'Approved - Move in Progress';

    public function getValidTransitions()
    {
        return array('ParticipantStateCheckedOut', 'ParticipantStateCancelled');
    }
}

class ParticipantStateCheckedOut extends RoomChangeParticipantState {
    const STATE_NAME = 'CheckedOut';
    const FRIENDLY_NAME = 'Checked-out of Old Room';

    public function getValidTransitions()
    {
        return array();
    }

    // TODO Notify "old" RD and Housing
    // TODO If all participants checked out, move request to Complete
}

class ParticipantStateDeclined extends RoomChangeParticipantState {
    const STATE_NAME = 'Declined';
    const FRIENDLY_NAME = 'Declined';

    public function getValidTransitions()
    {
        return array();
    }

    // TODO Move Request to Cancelled, which will notify everyone
}

class ParticipantStateDenied extends RoomChangeParticipantState {
    const STATE_NAME = 'Denied';
    const FRIENDLY_NAME = 'Denied';

    public function getValidTransitions()
    {
        return array();
    }

    // TODO Move Request to Denied, which will notify everyone
}

class ParticipantStateCancelled extends RoomChangeParticipantState {
    const STATE_NAME = 'Cancelled';
    const FRIENDLY_NAME = 'Cancelled';

    public function getValidTransitions()
    {
        return array();
    }

    // TODO Move request to cancelled, which will notify everyone
}
