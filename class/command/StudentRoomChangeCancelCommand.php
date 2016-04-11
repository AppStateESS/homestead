<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');

class StudentRoomChangeCancelCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'StudentRoomChangeCancel');
    }

    public function execute(CommandContext $context)
    {
        $requestId = $context->get('requestId');
        $reason = "Room change cancelled by student via menu.";

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // Command for redirecting back to the request view on success or error
        $cmd = CommandFactory::getCommand('ShowReturningStudentMenu');

        // Set the denied reason
        $request->setDeniedReasonPublic($reason);
        $request->save();

        // Transition request to cancelled status
        $request->transitionTo(new RoomChangeStateCancelled($request, time(), null, UserStatus::getUsername()));

        // Transition all participants to cancelled
        // TODO... Do this in the cancelled transition?
        $participants = $request->getParticipants();

        foreach ($participants as $p) {
            $p->transitionTo(new ParticipantStateCancelled($p, time(), null, UserStatus::getUsername()));

            //Release the bed reservation, if any
            $bedId = $p->getToBed();
            if ($bedId != null) {
                $bed = new HMS_Bed($bedId);
                $bed->clearRoomChangeReserved();
                $bed->save();
            }
        }

        // Notify everyone involved
        try {
            PHPWS_Core::initModClass('hms', 'StudentFactory.php');
            $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $request->getTerm());
        } catch(StudentNotFoundException $e) {
            $student = null;
        }
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::sendRoomChangeCancelledNotice($request, $student);

        $cmd->redirect();
    }
}
