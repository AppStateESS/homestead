<?php

namespace Homestead;

class RoomChangeStateComplete extends RoomChangeRequestState {

    const STATE_NAME = 'Complete';

    public function getValidTransitions()
    {
        return array();
    }
}
