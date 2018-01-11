<?php

namespace Homestead;

class RoomChangeStatePending extends RoomChangeRequestState {

    const STATE_NAME = 'Pending'; // Text state name

    public function getValidTransitions()
    {
        return array(
                'RoomChangeStateHold',
                'RoomChangeStateApproved',
                'RoomChangeStateDenied',
                'RoomChangeStateCancelled'
        );
    }
}
