<?php

namespace Homestead;

class ParticipantStateInProcess extends RoomChangeParticipantState {
    const STATE_NAME = 'InProcess';
    const FRIENDLY_NAME = 'Approved - Move in Progress';

    public function getValidTransitions()
    {
        return array('ParticipantStateCheckedOut', 'ParticipantStateCancelled');
    }
}
