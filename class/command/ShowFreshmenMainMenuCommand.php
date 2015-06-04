<?php

class ShowFreshmenMainMenuCommand extends Command {
    
    public function getRequestVars()
    {
        $vars = array('action'=>'ShowFreshmenMainMenu');
        
        return $vars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'FreshmenMainMenuView.php');
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());
        
        $view = new FreshmenMainMenuView($student);
        $context->setContent($view->show());
    }
    
}


