<?php

namespace Homestead;

class RoomChangeStateHold extends RoomChangeRequestState {

    const STATE_NAME = 'Hold';

    public function getValidTransitions()
    {
        return array('RoomChangeStateApproved',
                     'RoomChangeStateCancelled',
                     'RoomChangeStateDenied');
    }
}
