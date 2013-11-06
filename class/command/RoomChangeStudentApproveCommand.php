<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class RoomChangeStudentApproveCommand extends Command {

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
        return array('action'           => 'RoomChangeStudentApprove',
                     'requestId'        => $this->requestId,
                     'participantId'    => $this->participantId);
    }

    public function execute(CommandContext $context)
    {
        // Get input
        $requestId = $context->get('requestId');
        $participantId = $context->get('participantId');

        // Command for showing the request, redirected to on success/error
        $cmd = CommandFactory::getCommand('ShowManageRoomChange');
        $cmd->setRequestId($requestId);

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // Load the participant
        $participant = RoomChangeParticipantFactory::getParticipantById($participantId);

        // Load the Student
        $student = StudentFactory::getStudentByBannerId($participant->getBannerId(), $request->getTerm());

        // Check permissions. Must be the participant or an admin
        if(UserStatus::getUsername() != $student->getUsername()
            && !Current_User::allow('hms', 'admin_approve_room_change')) {
            throw new PermissionException('You do not have permission to appove this room change.');
        }

        // Transition to StudentApproved state
        $participant->transitionTo(new ParticipantStateStudentApproved($participant, time(), null, UserStatus::getUsername()));

        // TODO If all students have approved, notify RDs

        $cmd->redirect();
    }
}

?>