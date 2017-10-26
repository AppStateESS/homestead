<?php

namespace Homestead;

class ParticipantStateCancelled extends RoomChangeParticipantState {
    const STATE_NAME = 'Cancelled';
    const FRIENDLY_NAME = 'Cancelled';

    public function getValidTransitions()
    {
        return array();
    }

    // TODO Move request to cancelled, which will notify everyone
}
