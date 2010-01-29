<?php

class LotteryShowWaitingListOptOutCommand extends Command {
    
    public function getRequestVars()
    {
        $vars = array('action'=>'LotteryShowWaitingListOptOut');
        
        return $vars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'LotteryWaitingListOptOutView.php');
        
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $view = new LotteryWaitingListOptOutView($student, $term);
        
        $context->setContent($view->show());
    }
}

?>