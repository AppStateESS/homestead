<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');

class RoomChangeDenyCommand extends Command {

    public function getRequestVars()
    {
        return array('action' => 'RoomChangeDeny');
    }

    public function execute(CommandContext $context)
    {
        $requestId = $context->get('requestId');

        $publicReason   = $context->get('deny-reason-public');
        $privateReason  = $context->get('deny-reason-private');

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // TODO Check permissions, based on state


        // Command for redirecting back to the request view on success or error
        $cmd = CommandFactory::getCommand('ShowManageRoomChange');
        $cmd->setRequestId($request->getId());

        // Make sure user gave a reason
        if(!isset($publicReason) or $publicReason == ''){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please enter a denial reason.');
            $cmd->redirect();
        }


        // Set the denied reason
        $request->setDeniedReasonPublic($publicReason);
        $request->setDeniedReasonPrivate($privateReason);
        $request->save();

        // Transition request to cancelled status
        $request->transitionTo(new RoomChangeStateDenied($request, time(), null, UserStatus::getUsername()));

        // Transition all participants to cancelled
        // TODO... Do this in the cancelled transition?
        $participants = $request->getParticipants();

        foreach ($participants as $p) {
            $p->transitionTo(new ParticipantStateDenied($p, time(), null, UserStatus::getUsername()));
        }

        // Notify everyone involved
        HMS_Email::sendRoomChangeDeniedNotice($request);

        $cmd->redirect();
    }
}

?>