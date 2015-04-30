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
            NQ::simple('hms', hms\NotificationView::ERROR, 'Invalid or missing username.');
            $cmd->redirect();
        }

        // Make sure a valid reason was chosen
        if (!isset($unassignReason) || $unassignReason == -1) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please choose a valid reason.');
            $cmd->setUsername($username);
            $cmd->redirect();
        }

        // Check refund percentage field
        $refund = $context->get('refund');

        // Is a required field
        if(!isset($refund) || $refund == '') {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please enter a refund percentage.');
            $cmd->redirect();
        }

        // Must be numeric
        if(!is_numeric($refund) || $refund < 0 || $refund > 100) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'The refund percentage must be between 0 and 100 percent.');
            $cmd->redirect();
        }

        // Must be whole number
        if (is_float($refund)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Only whole number refund percentages are supported, no decimal place is allowed.');
            $cmd->redirect();
        }
            

        $term = Term::getSelectedTerm();
        $student = StudentFactory::getStudentByUsername($username, $term);
        $notes = $context->get('note');

        try {
            $result = HMS_Assignment::unassignStudent($student, $term, $notes, $unassignReason, $refund);
        } catch (Exception $e) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Error: ' . $e->getMessage());
            $cmd->setUsername($username);
            $cmd->redirect();
        }

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Successfully unassigned ' . $student->getFullName());
        $cmd->redirect();
    }
}

?>
