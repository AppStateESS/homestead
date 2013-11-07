<?php

/**
 * Controller class for removing/un-assigning a student.
 *
 * @author jbooker
 * @package hms
 */
class UnassignStudentCommand extends Command {

    /**
     *
     * @return multitype:string
     */
    public function getRequestVars()
    {
        return array(
                'action' => 'UnassignStudent'
        );
    }

    /**
     *
     * @param CommandContext $context
     * @throws PermissionException
     */
    public function execute(CommandContext $context)
    {
        if (!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $username = $context->get('username');
        $unassignReason = $context->get('unassignment_type');

        $cmd = CommandFactory::getCommand('ShowUnassignStudent');
        // $cmd->setUsername($username);

        if (!isset($username) || is_null($username)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid or missing username.');
            $cmd->redirect();
        }

        // Make sure a valid reason was chosen
        if (!isset($unassignReason) || $unassignReason == -1) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please choose a valid reason.');
            $cmd->setUsername($username);
            $cmd->redirect();
        }

        $term = Term::getSelectedTerm();
        $student = StudentFactory::getStudentByUsername($username, $term);
        $notes = $context->get('note');

        try {
            $result = HMS_Assignment::unassignStudent($student, $term, $notes, $unassignReason);
        } catch (Exception $e) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: ' . $e->getMessage());
            $cmd->setUsername($username);
            $cmd->redirect();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Successfully unassigned ' . $student->getFullName());
        $cmd->redirect();
    }
}

?>
