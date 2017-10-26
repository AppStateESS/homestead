<?php

namespace Homestead;

class ParticipantStateCurrRdApproved extends RoomChangeParticipantState {
    const STATE_NAME = 'CurrRdApproved';
    const FRIENDLY_NAME = 'Current RD Approved';

    public function getValidTransitions()
    {
        return array('ParticipantStateFutureRdApproved', 'ParticipantStateDenied', 'ParticipantStateCancelled');
    }

    // TODO send notification to future RD
}
