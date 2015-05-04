<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentState.php');

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