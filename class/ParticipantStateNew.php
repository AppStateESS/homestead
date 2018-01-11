<?php

namespace Homestead;

class ParticipantStateNew extends RoomChangeParticipantState {
    const STATE_NAME = 'New';
    const FRIENDLY_NAME = 'Created';

    public function getValidTransitions()
    {
        return array('ParticipantStateStudentApproved', 'ParticipantStateDenied', 'ParticipantStateDeclined', 'ParticipantStateCancelled');
    }
}
