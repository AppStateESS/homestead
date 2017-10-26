<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

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
