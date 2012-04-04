<?php

class AcceptDeclineRlcInviteCommand extends Command {
    
    public function getRequestVars(){
        return array('action'=>'AcceptDeclineRlcInvite');
    }
    
    public function execute(CommandContext $context)
    {
        $term = $context->get('term');
        if(!isset($term)){
            throw new InvalidArgumentException('Missing term!');
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'RlcAssignmentConfirmedState.php');
        PHPWS_Core::initModClass('hms', 'RlcAssignmentDeclinedState.php');
        
        test($_REQUEST);
        
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername(UserStatus::getUsername(), $term);
        $rlcApplication = $rlcAssignment->getApplication();
        
        $acceptStatus = $context->get('acceptance');
        
        $termsCheck = $context->get('terms_cond');
        
        if($acceptStatus == 'accept' && !isset($termsCheck)){
            // Student accepted the invite, but didn't check the terms/conditions box
            $errorCmd = CommandFactory::getCommand('ShowAcceptRlcInvite');
            $errorCmd->setTerm($term);
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please check the box indicating that you agree to the learning communitiy terms and conditions.');
            $errorCmd->redirect();
        }else if($acceptStatus == 'accept' && isset($termsCheck)){
            // Student accepted the invite and checked the terms/conditions box
            $rlcAssignment->changeState(new RlcAssignmentConfirmedState($rlcAssignment));
            
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'You have <strong>accepted</strong> your Residential Learning Community invitation.');
            // Log this!
            //TODO
            
            $successCmd = CommandFactory::getCommand('ShowStudentMenu');
            $successCmd->redirect();
        }else if($acceptStatus == 'decline'){
            // student declined
            $rlcAssignment->changeState(new RlcAssignmentDeclinedState($rlcAssignment));
            
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'You have <strong>declined</strong> your Residential Learning Community invitation.');
            // Log this!
            //TODO
            
            $successCmd = CommandFactory::getCommand('ShowStudentMenu');
            $successCmd->redirect();
        }else{
            // Didn't choose
            $errorCmd = CommandFactory::getCommand('ShowAcceptRlcInvite');
            $errorCmd->setTerm($term);
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please choose to either accept or decline your learning community invitation.');
            $errorCmd->redirect();
        }
        
        $context->setContent('confirmed or denied');
    }
}

?>