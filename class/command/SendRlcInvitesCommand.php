<?php

/**
 * SendRlcInvitesComands
 * 
 * Transitions all new rlc assignments to the 'Invited' state.
 * 
 * @author jbooker
 * @package HMS
 */
class SendRlcInvitesCommand extends Command {
    
    public function getRequestVars()
    {
        return array('action'=>'SendRlcInvites');
    }
    
    public function execute(CommandContext $context)
    {
        $resultCmd = CommandFactory::getCommand('ShowSendRlcInvites');
        
        $respondByDate = $context->get('respond_by_date');
        $respondByTime = $context->get('time');
        
        if(!isset($respondByDate) || $respondByDate == ''){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please choose a \'respond by\' date.');
            $resultCmd->redirect();            
        }

        $dateParts = explode('/', $respondByDate);
        $respondByTimestamp = mktime($respondByTime, null, null, $dateParts[0], $dateParts[1], $dateParts[2]);
        
        $term = Term::getSelectedTerm();
        
        PHPWS_Core::initModClass('hms', 'RlcAssignmentFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcAssignmentInvitedState.php');
        
        $assignments = RlcAssignmentFactory::getAssignmentsByTermStateType($term, 'new', 'freshmen');
        
        if(sizeof($assignments) == 0){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'No invites needed to be sent.');
            $resultCmd->redirect();
        }
        
        foreach($assignments as $assign){
            $assign->changeState(new RlcAssignmentInvitedState($assign, $respondByTimestamp));
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Freshmen RLC invites sent.');
        $resultCmd->redirect();
    }
}

?>
