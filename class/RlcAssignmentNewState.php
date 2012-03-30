<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentState.php');

/**
 * RlcAssignmentNewState
 * 
 * Represents the state of a RLC assignment when the student has been assigned (but not yet invited)
 * 
 * @author jbooker
 * @package HMS
 */
class RlcAssignmentNewState extends RlcAssignmentState {

    public function onEnter()
    {
    
    }
}

?>