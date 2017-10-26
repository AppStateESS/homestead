<?php

namespace Homestead;

class ParticipantStateFutureRdApproved extends RoomChangeParticipantState {
    const STATE_NAME = 'FutureRdApproved';
    const FRIENDLY_NAME = 'Future RD Approved';

    public function getValidTransitions()
    {
        return array('ParticipantStateInProcess', 'ParticipantStateDenied', 'ParticipantStateCancelled');
    }

    // TODO If all participants are FutureRdApproved, send notification to Housing
}
