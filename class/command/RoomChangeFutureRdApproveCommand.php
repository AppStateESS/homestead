<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

/**
 * Command for future RD approval of a room change participant.
 *
 * @author jbooker
 * @package hms
 *
 * //TODO Possibly combine this with RoomChangeCurrRdApproveCommand,
 *        since there's a lot of copy/pasted code.
 */
class RoomChangeFutureRdApproveCommand extends Command {

    private $requestId;
    private $participantId;

    public function setRequestId($id)
    {
        $this->requestId = $id;
    }

    public function setParticipantId($id)
    {
        $this->participantId = $id;
    }

    public function getRequestVars()
    {
        return array('action'           => 'RoomChangeFutureRdApprove',
                     'requestId'        => $this->requestId,
                     'participantId'    => $this->participantId);
    }

    public function execute(CommandContext $context)
    {
        // Get input
        $requestId = $context->get('requestId');
        $participantId = $context->get('participantId');

        // destinationBedId - This can be null for "swap" requests, because it's already known
        $toBedSelected = $context->get('bed_select');


        // Command for showing the request, redirected to on success/error
        $cmd = CommandFactory::getCommand('ShowManageRoomChange');
        $cmd->setRequestId($requestId);

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // Load the participant
        $participant = RoomChangeParticipantFactory::getParticipantById($participantId);

        // Check permissions. Must be an RD for current bed, or an admin
        $rds = $participant->getFutureRdList();
        if (!in_array(UserStatus::getUsername(), $rds) && !Current_User::allow('hms', 'admin_approve_room_change')) {
            throw new PermissionException('You do not have permission to approve this room change.');
        }

        // Transition to CurrRdApproved
        $participant->transitionTo(new ParticipantStateFutureRdApproved($participant, time(), null, UserStatus::getUsername()));

        //TODO If all participants are approved, send notification to Housing
        if($request->isApprovedByAllFutureRDs()) {
            HMS_Email::sendRoomChangeAdministratorNotice($request);
        }

        // Redirect to the manage request page
        $cmd->redirect();
    }
}

?>
