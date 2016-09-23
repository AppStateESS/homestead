<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class RoomChangeStudentCancelCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RoomChangeStudentCancel');
    }

    public function execute(CommandContext $context)
    {
        $requestId = $context->get('requestId');

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // Command for redirecting back to the request view on success or error
        $cmd = CommandFactory::getCommand('ShowReturningStudentMenu');

        // Set the denied reason
        $request->setDeniedReasonPublic("Room change cancelled by student via menu.");
        $request->save();

        // Transition request to cancelled status
        $request->transitionTo(new RoomChangeStateCancelled($request, time(), null, UserStatus::getUsername()));

        // Transition all participants to cancelled
        $participants = $request->getParticipants();

        foreach ($participants as $p) {
            $p->transitionTo(new ParticipantStateCancelled($p, time(), null, UserStatus::getUsername()));

            // Release the bed reservation, if any
            $bedId = $p->getToBed();
            if ($bedId != null) {
                $bed = new HMS_Bed($bedId);
                $bed->clearRoomChangeReserved();
                $bed->save();
            }
        }

        // Notify everyone involved
        try {
            $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $request->getTerm());
        } catch(StudentNotFoundException $e) {
            $student = null;
        }
        HMS_Email::sendRoomChangeCancelledNotice($request, $student);

        $cmd->redirect();
    }
}
