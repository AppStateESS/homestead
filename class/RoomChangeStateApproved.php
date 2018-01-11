<?php

namespace Homestead;

class RoomChangeStateApproved extends RoomChangeRequestState {

    const STATE_NAME = 'Approved';

    public function getValidTransitions()
    {
        return array('RoomChangeStateComplete', 'RoomChangeStateCancelled');
    }

    // TODO Send approval notifiction to student/RDs
}
