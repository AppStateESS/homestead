<?php

namespace Homestead\Command;

 

class RlcSelfAssignStartCommand extends Command {

    private $term;
    private $roomateRequestId;

    public function setTerm($term)
    {
    	$this->term = $term;
    }

    public function setRoommateRequestId($requestId)
    {
    	$this->roommateRequestId = $requestId;
    }

    public function getRequestVars()
    {
    	$vars = array('action'=>'RlcSelfAssignStart', 'term'=>$this->term);

        if(isset($this->roommateRequestId) && $this->roommateRequestId != null) {
        	$vars['roommateRequestId'] = $this->roommateRequestId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'RlcSelfAssignStartView.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $term = $context->get('term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        $housingApp = HousingApplicationFactory::getAppByStudent($student, $term);
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $term);

        $errorCmd = CommandFactory::getCommand('ShowStudentMenu');

        // Double check that the student has an RLC application, and that it's in the 'invited' state
        if($rlcAssignment == null){
            \NQ::simple('hms', NotificationView::ERROR, "You're not eligible for RLC self-selection because you have not been assigned to a Learning Community.");
            $errorCmd->redirect();
        }

        if($rlcAssignment->getStateName() != 'selfselect-invite')
        {
        	\NQ::simple('hms', NotificationView::ERROR, "You're not eligible for RLC self-selection because you have not been invited for self-selection.");
            $errorCmd->redirect();
        }

        $roommateRequestId = $context->get('roommateRequestId');

    	$view = new RlcSelfAssignStartView($student, $term, $rlcAssignment, $housingApp, $roommateRequestId);
        $context->setContent($view->show());
    }
}
