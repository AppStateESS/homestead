<?php

namespace Homestead;

class RlcAssignmentSelfAssignedState extends RlcAssignmentState {
	protected $stateName = 'selfselect-assigned';

    public function __construct(HMS_RLC_Assignment $rlcAssignment)
    {
        parent::__construct($rlcAssignment);

    }

    public function onEnter()
    {

    }
}
