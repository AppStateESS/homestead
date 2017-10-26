<?php

namespace Homestead;

class ParticipantStateStudentApproved extends RoomChangeParticipantState {
    const STATE_NAME = 'StudentApproved';
    const FRIENDLY_NAME = 'Student Approved';

    public function getValidTransitions()
    {
        return array('ParticipantStateCurrRdApproved', 'ParticipantStateDenied', 'ParticipantStateCancelled');
    }

    //TODO Send notification to current RD
}
