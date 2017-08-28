<?php

namespace Homestead\command;

use \Homestead\Command;

class ShowReturningStudentMenuCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowReturningStudentMenu');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $lotteryTerm = \PHPWS_Settings::get('hms', 'lottery_term');

        if(is_null($lotteryTerm)){
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Lottery term is not configured.');
        }

        if($lotteryTerm < Term::getCurrentTerm()){
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Lottery term must be in the future. You probably forgot to update it.');
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $lotteryTerm);

        PHPWS_Core::initModClass('hms', 'ReturningMainMenuView.php');
        $view = new ReturningMainMenuView($student, $lotteryTerm);

        $context->setContent($view->show());
    }
}
