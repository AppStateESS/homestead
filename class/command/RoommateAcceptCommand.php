<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RoommateAcceptCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'RoommateAccept');

        if(isset($this->roommateId)) {
            $vars['roommateId'] = $this->roommateId;
        }

        return $vars;
    }

    public function setRoommateId($id)
    {
        $this->roommateId = $id;
    }

    public function execute(CommandContext $context)
    {
        $id = $context->get('roommateId');
        if(is_null($id)) {
            throw new InvalidArgumentException('Must set roommateId');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $roommate = new HMS_Roommate($id);
        if($roommate->id == 0) {
            throw new InvalidArgumentException('Invalid roommateId ' . $id);
        }

        $username = UserStatus::getUsername();
        if($username != $roommate->requestee) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("$username tried to confirm roommate pairing {$roommate->id}");
        }

        $err = CommandFactory::getCommand('ShowRoommateConfirmAccept');
        $err->setRoommateId($id);

        PHPWS_Core::initCoreClass('Captcha.php');
        $verified = Captcha::verify(TRUE);
        if($verified === FALSE || is_null($verified)) {
            NQ::Simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, please try again.');
            $err->redirect();
        }

        try {
            $roommate->confirm();
        } catch(RoommateCompatibilityException $rce) {
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, $rce->getMessage());
            $err->redirect();
        }

        $roommate->save();

        HMS_Activity_Log::log_activity($roommate->requestor,
                                       ACTIVITY_ACCEPTED_AS_ROOMMATE,
                                       $roommate->requestee,
                                       "CAPTCHA: $verified");

        // Email both parties
        // $request->send_confirm_emails();

        // Remove any other requests for the requestor
        HMS_Roommate::removeOutstandingRequests($roommate->requestor, $roommate->term);

        // Remove any other requests for the requestee
        HMS_Roommate::removeOutstandingRequests($roommate->requestee, $roommate->term);

        $requestor = StudentFactory::getStudentByUsername($roommate->requestor, $roommate->term);
        $name = $requestor->getFullName();
        NQ::Simple('hms', HMS_NOTIFICATION_SUCCESS, "You and $name are confirmed as roommates.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}

?>
