<?php

class UnassignStudentCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'UnassignStudent');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $username = $context->get('username');
        $unassign_reason = $context->get('unassignment_type');

        $cmd = CommandFactory::getCommand('ShowUnassignStudent');
        $cmd->setUsername($username);

        if(!isset($username) || is_null($username)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid or missing username.');
            $errorCmd->redirect();
        }

        $term = Term::getSelectedTerm();
        $student = StudentFactory::getStudentByUsername($username, $term);
        $notes = $context->get('note');

        try{
            $result = HMS_Assignment::unassignStudent($student, $term, $notes, $unassign_reason);
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: ' . $e->getMessage());
            $cmd->setUsername($username);
            $cmd->redirect();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Successfully unassigned ' . $student->getFullName());
        $cmd->redirect();
    }
}

?>
