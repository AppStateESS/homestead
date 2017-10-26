<?php

namespace Homestead;

class ParticipantStateDeclined extends RoomChangeParticipantState {
    const STATE_NAME = 'Declined';
    const FRIENDLY_NAME = 'Declined';

    public function getValidTransitions()
    {
        return array();
    }

    // TODO Move Request to Cancelled, which will notify everyone
}
