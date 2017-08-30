<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\RlcApplicationPage1View;

class ShowRlcApplicationPage1ViewCommand extends Command {

    private $term;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getRequestVars(){
        return array('action'=>'ShowRlcApplicationPage1View', 'term' => $this->term);
    }

    public function execute(CommandContext $context){

        $term = $context->get('term');

        if(!isset($term) || is_null($term) || empty($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $view = new RlcApplicationPage1View($context, $student);

        $context->setContent($view->show());
    }
}
