<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\UnassignStudentView;
use \Homestead\Term;
use \Homestead\Exception\PermissionException;

class ShowUnassignStudentCommand extends Command {

    private $username;

    public function setUsername($username){
        $this->username = $username;
    }

    public function getRequestVars(){
        $vars = array();

        $vars['action'] = 'ShowUnassignStudent';

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'assignment_maintenance')){
            throw new PermissionException('You do not have permission to unassign students.');
        }

        $username = $context->get('username');

        if(isset($username) && !is_null($username) && $username != ''){
            $student = StudentFactory::getStudentByUsername($username, Term::getSelectedTerm());
        }else{
            $student = NULL;
        }

        $unassignView = new UnassignStudentView($student);

        $context->setContent($unassignView->show());
    }
}
