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
    private $respondByTimestamp;
   
    public function __construct(HMS_RLC_Assignment $rlcAssignment, $respondByTimestamp)
    {
        parent::__construct($rlcAssignment);

        $this->respondByTimestamp = $respondByTimestamp;
    }

    public function onEnter()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        $application = $this->rlcAssignment->getApplication();
        
        $term     = $application->getTerm();
        $username = $application->getUsername();
        $community = $this->rlcAssignment->getRlc();
        
        $student = StudentFactory::getStudentByUsername($username, $term);
        
        HMS_Email::sendRlcInviteEmail($student, $community, $term, $this->respondByTimestamp);
        
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_RLC_INVITE_SENT, UserStatus::getUsername());
    }
}

?>
