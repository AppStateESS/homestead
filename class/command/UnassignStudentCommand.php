<?php

class UnassignStudentCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'UnassignStudent');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $username = $context->get('username');

        $errorCmd = CommandFactory::getCommand('ShowUnassignStudent');
        $errorCmd->setUsername($username);

        if(!isset($username) || is_null($username)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid or missing username.');
            $errorCmd->redirect();
        }

        $term = Term::getSelectedTerm();
        $student = StudentFactory::getStudentByUsername($username, $term);
        $notes = $context->get('notes');

        try{
            $result = HMS_Assignment::unassignStudent($student, $term, $notes);
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: ' . $e->getMessage());
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Successfully unassigned ' . $student->getFullName());
        $successCmd = CommandFactory::getCommand('ShowUnassignStudent');
        $successCmd->redirect();
    }
}

?>