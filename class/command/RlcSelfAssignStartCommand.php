<?php

class RlcSelfAssignStartCommand extends Command {
	
    private $term;
    
    public function setTerm($term)
    {
    	$this->term = $term;
    }
    
    public function getRequestVars()
    {
    	return array('action'=>'RlcSelfAssignStart', 'term'=>$this->term);
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
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "You're not eligible for RLC self-selection because you have not been assigned to a Learning Community.");
            $errorCmd->redirect();
        }
        
        if($rlcAssignment->getStateName() != 'selfselect-invite')
        {
        	NQ::simple('hms', HMS_NOTIFICATION_ERROR, "You're not eligible for RLC self-selection because you have not been invited for self-selection.");
            $errorCmd->redirect();
        }
        
    	$view = new RlcSelfAssignStartView($student, $term, $rlcAssignment, $housingApp);
        $context->setContent($view->show());
    }
}