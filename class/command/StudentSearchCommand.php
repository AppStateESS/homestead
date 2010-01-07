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

        $userid = $context->get('username');
        $term = Term::getSelectedTerm();

        try {
            # Check to see if the user enterd a Banner ID or a user name
            if(preg_match("/^[0-9]{9}/", $userid)){
                # Looks like a banner ID
                $student = StudentFactory::getStudentByBannerId($userid, $term);
            } else {
                $student = StudentFactory::getStudentByUsername($userid, $term);
            }
        }catch (StudentNotFoundException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
            $cmd = CommandFactory::getCommand('ShowStudentSearch');
            $cmd->setUsername($userid);
            $cmd->redirect();
        }

        $profile = new StudentProfile($student, $term);

        $context->setContent($profile->getProfileView()->show());
    }
}
