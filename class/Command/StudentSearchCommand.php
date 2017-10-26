<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

class StudentSearchCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'StudentSearch');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'search')){
            throw new PermissionException('You do not have permission to search for students.');
        }

        $userid = $context->get('banner_id');
        $userid = strtolower(trim($userid));
        $profileCmd = CommandFactory::getCommand('ShowStudentProfile');

        // Check to see if the user enterd a Banner ID or a user name
        if(preg_match("/^[0-9]{9}/", $userid)){
            // Looks like a banner ID
            $profileCmd->setBannerId($userid);
        } else {
            // Must be a username
            $profileCmd->setUsername($userid);
        }

        $profileCmd->redirect();
    }
}
