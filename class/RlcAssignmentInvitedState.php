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

    protected $stateName = 'invited';
    
    public function onEnter()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $application = $this->rlcAssignment->getApplication();
        
        $term     = $application->getTerm();
        $username = $application->getUsername();
        $community = $this->rlcAssignment->getRlc();
        
        $student = StudentFactory::getStudentByUsername($username, $term);
        
        HMS_Email::sendRlcInviteEmail($student, $community, $term);
    }
}

?>