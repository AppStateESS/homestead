<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentState.php');

/**
 * RlcAssignmentConfirmedState
 * 
 * Represents the state of an RLC assignment once the student has confirmed the RLC invite.
 * 
 * @author jbooker
 * @package HMS
 */
class RlcAssignmentConfirmedState extends RlcAssignmentState {
    
    protected $stateName = 'confirmed';
    
    public function onEnter()
    {
        
    }
    
}

?>