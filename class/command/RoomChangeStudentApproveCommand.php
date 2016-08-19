<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

PHPWS_Core::initCoreClass('Captcha.php');

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

        if(!UserStatus::isAdmin())
        {
          // Check for CAPTCHA if this is the student; admins don't need a CAPTCHA
          $captchaResult = Captcha::verify(true);
          if (UserStatus::getUsername() == $student->getUsername() && $captchaResult === false)
          {
              // Failed the captcha
              NQ::simple('hms', hms\NotificationView::ERROR, "You didn't type the magic words correctly. Please try again.");
              $cmd = CommandFactory::getCommand('ShowRoomChangeRequestApproval');
              $cmd->redirect();
          }

          // If there was a captcha, then log the activity
          if($captchaResult !== false){
              HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_ROOM_CHANGE_AGREED, UserStatus::getUsername(FALSE), 'Request id: ' . $requestId . ' Captcha: ' . $captchaResult);
          }
        }

        // Transition to StudentApproved state
        $participant->transitionTo(new ParticipantStateStudentApproved($participant, time(), null, UserStatus::getUsername()));

        // If all students have approved, notify RDs
        if($request->isApprovedByAllParticipants()) {
            HMS_Email::sendRoomChangeCurrRDNotice($request);
        }

        // If the student is logged in, redirect to the main menu, other wise go back to the room change management view
        if(UserStatus::getUsername() == $student->getUsername()) {
            NQ::simple('hms', hms\NotificationView::SUCCESS, 'You have agreed to the room change request. You will be notified by email when the reqeust is approved or denied.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }else{
            $cmd->redirect();
        }
    }
}
