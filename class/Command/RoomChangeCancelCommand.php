<?php

namespace Homestead\Command;

use \Homestead\RoomChangeRequestFactory;
use \Homestead\RoomChangeStateCancelled;
use \Homestead\ParticipantStateCancelled;
use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\Bed;
use \Homestead\HMS_Email;
use \Homestead\NotificationView;
use \Homestead\CommandFactory;
use \Homestead\Exception\StudentNotFoundException;

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
            \NQ::simple('hms', NotificationView::ERROR, 'Please enter a cancellation reason.');
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
                $bed = new Bed($bedId);
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
