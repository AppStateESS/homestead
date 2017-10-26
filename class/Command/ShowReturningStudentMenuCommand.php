<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\ReturningMainMenuView;
use \Homestead\Exception\InvalidConfigurationException;

class ShowReturningStudentMenuCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowReturningStudentMenu');
    }

    public function execute(CommandContext $context)
    {
        $lotteryTerm = \PHPWS_Settings::get('hms', 'lottery_term');

        if(is_null($lotteryTerm)){
            throw new InvalidConfigurationException('Lottery term is not configured.');
        }

        if($lotteryTerm < Term::getCurrentTerm()){
            throw new InvalidConfigurationException('Lottery term must be in the future. You probably forgot to update it.');
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $lotteryTerm);

        $view = new ReturningMainMenuView($student, $lotteryTerm);

        $context->setContent($view->show());
    }
}
