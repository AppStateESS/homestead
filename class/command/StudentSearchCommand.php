<?php

class StudentSearchCommand extends Command {

    function getRequestVars(){
        return array('action'=>'StudentSearch');
    }

    function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'search')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to search for students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'StudentProfile.php');

        $userid = $context->get('banner_id');
        $userid = strtolower(trim($userid));
        $term = Term::getSelectedTerm();

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
