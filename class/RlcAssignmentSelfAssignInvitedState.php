<?php

namespace Homestead;

class RlcAssignmentSelfAssignInvitedState extends RlcAssignmentState {
	protected $stateName = 'selfselect-invite';

    public function __construct(HMS_RLC_Assignment $rlcAssignment)
    {
        parent::__construct($rlcAssignment);

    }

    public function onEnter()
    {

    }
}
