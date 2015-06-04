<?php

class ShowAcceptRlcInviteCommand extends Command {
    
    private $term;
    
    public function setTerm($term){
        $this->term = $term;
    }
    
    public function getRequestVars()
    {
        return array('action'=>'ShowAcceptRlcInvite', 'term'=>$this->term);
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        
        $term = $context->get('term');
        
        if(!isset($term)){
            throw new InvalidArgumentException('Missing term!');
        }
        
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername(UserStatus::getUsername(), $term);
        $rlcApplication = $rlcAssignment->getApplication();
        
        PHPWS_Core::initModClass('hms', 'AcceptRlcInviteView.php');
        $view = new AcceptRlcInviteView($rlcApplication, $rlcAssignment, $term);
        
        $context->setContent($view->show());
    }
}

