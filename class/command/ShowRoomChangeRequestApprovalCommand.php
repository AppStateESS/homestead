<?php

/**
 * Controller class for showing the interface where a student
 * can approve a pending room change request.
 *
 * @author Jeremy Booker
 * @package homestead
 */
class ShowRoomChangeRequestApprovalCommand extends Command {
    
    public function getRequestVars()
    {
        return array('action' => 'ShowRoomChangeRequestApproval');
    }

    public function execute(CommandContext $context) 
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestStudentApprovalView.php');

        $term = Term::getCurrentTerm();

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        $request = RoomChangeRequestFactory::getPendingByStudent($student, $term);
        $participants = RoomChangeParticipantFactory::getParticipantsByRequest($request);
        $thisParticipant = RoomChangeParticipantFactory::getParticipantByRequestStudent($request, $student);

        // If this particpant is not in the New state, then it must have already been approved or there's a problem
        // So redirect back to the main menu
        if (!$thisParticipant->getState() instanceof ParticipantStateNew) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You cannot approve that room change request.');
            $cmd = CommandFactory::getCommand('ShowStudentMenu');
            $cmd->redirect();
        }

        $view = new RoomChangeRequestStudentApprovalView($student, $request, $participants, $thisParticipant, $term);

        $context->setContent($view->show());
    }
}

?>
