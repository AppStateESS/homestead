<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

/**
 * Command for currnet RD Approval of a room change participant.
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeCurrRdApproveCommand extends Command {

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
        return array('action'           => 'RoomChangeCurrRdApprove',
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
        $rds = $participant->getCurrentRdList();
        if (!in_array(UserStatus::getUsername(), $rds) && !Current_User::allow('hms', 'admin_approve_room_change')) {
            throw new PermissionException('You do not have permission to approve this room change.');
        }

        // Check that a destination bed has already been set, or that the RD
        // has just selected a bed
        $toBedId = $participant->getToBed();

        if (is_null($toBedId) && $toBedSelected == '-1') {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please select a destination bed.');
            $cmd->redirect();
        }

        // Set the selected bed, if needed
        if (is_null($toBedId) && $toBedSelected != '-1') {
            $bed = new HMS_Bed($toBedSelected);

            // Check that the bed isn't already reserved for a room change
            if($bed->isRoomChangeReserved()){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'The bed you selected is already reserved for a room change. Please choose a different bed.');
                $cmd->redirect();
            }

            // Reserve the bed for room change
            $bed->setRoomChangeReserved();

            $bed->save();

            // Save the bed to this participant
            $participant->setToBed($bed);
            $participant->save();
        }

        // Transition to CurrRdApproved
        $participant->transitionTo(new ParticipantStateCurrRdApproved($participant, time(), null, UserStatus::getUsername()));

        // If the future RD is the same as the current user Logged in, then go ahead and transition to FutureRdApproved too.
        //TODO

        // If all Current RDs have approved, notify Future RDs
        if($request->isApprovedByAllCurrentRDs()) {
            foreach($request->getParticipants() as $p) {
                foreach($p->getFutureRdList() as $rd) {
                    HMS_Email::sendRoomChangeFutureRDNotice($rd, $p);
                }
            }
        }

        // Redirect to the manage request page
        $cmd->redirect();
    }
}

?>
