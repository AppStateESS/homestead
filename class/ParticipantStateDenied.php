<?php

namespace Homestead;

class ParticipantStateDenied extends RoomChangeParticipantState {
    const STATE_NAME = 'Denied';
    const FRIENDLY_NAME = 'Denied';

    public function getValidTransitions()
    {
        return array();
    }

    // TODO Move Request to Denied, which will notify everyone
}
