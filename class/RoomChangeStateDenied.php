<?php

namespace Homestead;

class RoomChangeStateDenied extends RoomChangeRequestState {

    const STATE_NAME = 'Denied';

    public function getValidTransitions()
    {
        return array();
    }
}
