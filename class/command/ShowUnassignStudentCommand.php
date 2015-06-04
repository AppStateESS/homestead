<?php

class ShowUnassignStudentCommand extends Command {

    private $username;

    function setUsername($username){
        $this->username = $username;
    }

    function getRequestVars(){
        $vars = array();

        $vars['action'] = 'ShowUnassignStudent';

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'UnassignStudentView.php');

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


