<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\Term;
use \Homestead\FreshmenMainMenuView;

class ShowFreshmenMainMenuCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowFreshmenMainMenu');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());

        $view = new FreshmenMainMenuView($student);
        $context->setContent($view->show());
    }

}
