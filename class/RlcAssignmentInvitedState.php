<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentState.php');

/**
 * RlcAssignmentInvitedState
 * 
 * Represents the state of a RLC assignment when the student has been invited (but has not confirmed the invite)
 * 
 * @author jbooker
 * @package HMS
 */
class RlcAssignmentInvitedState extends RlcAssignmentState {

    public function onEnter()
    {
    
    }
}

?>