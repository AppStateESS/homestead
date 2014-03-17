<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');

class RoomChangeCancelCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RoomChangeCancel');
    }

    public function execute(CommandContext $context)
    {
        $requestId = $context->get('requestId');
        $reason = $context->get('cancel-reason');

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // TODO Check permissions, based on state


        // Command for redirecting back to the request view on success or error
        $cmd = CommandFactory::getCommand('ShowManageRoomChange');
        $cmd->setRequestId($request->getId());

        // Make sure user gave a reason
        if(!isset($reason) or $reason == ''){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please enter a cancellation reason.');
            $cmd->redirect();
        }


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

?>
