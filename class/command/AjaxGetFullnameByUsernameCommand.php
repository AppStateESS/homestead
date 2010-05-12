<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'Term.php');
class AjaxGetFullnameByUsernameCommand extends Command {
    
    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'search')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to lookup student names!');
        }

        $username = $context->get('username');
        if(is_null($username) || empty($username)){
            echo "";
            exit;
        }
        
        $student = StudentFactory::getStudentByUsername($username, Term::getSelectedTerm());

        echo $student->getFullName();
        exit;
    }
}
?>