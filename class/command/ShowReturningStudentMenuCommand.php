<?php

class ShowReturningStudentMenuCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowReturningStudentMenu');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $lotteryTerm = PHPWS_Settings::get('hms', 'lottery_term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $lotteryTerm);

        PHPWS_Core::initModClass('hms', 'ReturningMainMenuView.php');
        $view = new ReturningMainMenuView($student, $lotteryTerm);

        $context->setContent($view->show());
    }
}

?>