<?php

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

        $view = new RoomChangeRequestStudentApprovalView($student, $request, $participants, $thisParticipant);

        $context->setContent($view->show());
    }
}

?>
