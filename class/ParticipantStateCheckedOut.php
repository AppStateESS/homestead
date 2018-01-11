<?php

namespace Homestead;

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
