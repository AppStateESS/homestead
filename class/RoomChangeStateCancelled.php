<?php

namespace Homestead;

class RoomChangeStateCancelled extends RoomChangeRequestState {

    const STATE_NAME = 'Cancelled';

    public function getValidTransitions()
    {
        return array();
    }
}
