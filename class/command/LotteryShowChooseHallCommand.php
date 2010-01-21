<?php

class LotteryShowChooseHalLCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'LotteryShowChooseHall');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'LotteryChooseHallView.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $view = new LotteryChooseHallView($student, $term);
        
        $context->setContent($view->show());
    }
}

?>