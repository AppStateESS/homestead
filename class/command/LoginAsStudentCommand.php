<?php

namespace Homestead\command;

use \Homestead\Command;

class LoginAsStudentCommand extends Command {

    private $username;

    public function setUsername($username){
        $this->username = $username;
    }

    public function getRequestVars()
    {
        return array('action'=>'LoginAsStudent', 'username'=>$this->username);
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms','login_as_student')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to login as a student.');
        }

        if(!isset($this->username)){
            $this->username = $context->get('username');
        }

        UserStatus::wearMask($this->username);

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}
