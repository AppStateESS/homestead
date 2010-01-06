<?php

class ShowAssignStudentCommand extends Command {

    private $username;
    private $bedId;

    function setUsername($username){
        $this->username = $username;
    }

    function setBedId($id){
        $this->bedId = $id;
    }

    function getRequestVars(){
        $vars = array();

        $vars['action'] = 'ShowAssignStudent';

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        if(isset($this->bedId)){
            $vars['bedId'] = $this->bedId;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to assign students.');
        }
         
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'AssignStudentView.php');

        $username	= $context->get('username');
        $bed		= $context->get('bedId');

        if(isset($username)){
            $student = StudentFactory::getStudentByUsername($context->get('username'), Term::getSelectedTerm());
        }else{
            $student = NULL;
        }

        $assignView = new AssignStudentView($student, $bed);

        $context->setContent($assignView->show());
    }
}

?>
