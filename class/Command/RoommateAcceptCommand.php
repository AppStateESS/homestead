<?php

namespace Homestead\Command;

use \Homestead\HMS_Roommate;
use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_Email;
use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\RoommateCompatibilityException;
use \Homestead\Exception\PermissionException;

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
            throw new \InvalidArgumentException('Must set roommateId');
        }

        $roommate = new HMS_Roommate($id);
        if($roommate->id == 0) {
            throw new \InvalidArgumentException('Invalid roommateId ' . $id);
        }

        $username = UserStatus::getUsername();
        if($username != $roommate->requestee) {
            throw new PermissionException("$username tried to confirm roommate pairing {$roommate->id}");
        }

        $err = CommandFactory::getCommand('ShowRoommateConfirmAccept');
        $err->setRoommateId($id);

        \PHPWS_Core::initCoreClass('Captcha.php');
        $verified = \Captcha::verify(TRUE);
        if($verified === FALSE || is_null($verified)) {
            \NQ::Simple('hms', NotificationView::ERROR, 'Sorry, please try again.');
            $err->redirect();
        }

        try {
            $roommate->confirm();
        } catch(RoommateCompatibilityException $rce) {
            \NQ::simple('hms', NotificationView::WARNING, $rce->getMessage());
            $err->redirect();
        }

        $roommate->save();

        HMS_Activity_Log::log_activity($roommate->requestor,
                                       ACTIVITY_ACCEPTED_AS_ROOMMATE,
                                       $roommate->requestee,
                                       "$roommate->requestee accepted request, CAPTCHA: $verified");
        HMS_Activity_Log::log_activity($roommate->requestee,
                                       ACTIVITY_ACCEPTED_AS_ROOMMATE,
                                       $roommate->requestor,
                                       "$roommate->requestee accepted request, CAPTCHA: $verified");

        // Email both parties
        HMS_Email::send_confirm_emails($roommate);

        // Remove any other requests for the requestor
        HMS_Roommate::removeOutstandingRequests($roommate->requestor, $roommate->term);

        // Remove any other requests for the requestee
        HMS_Roommate::removeOutstandingRequests($roommate->requestee, $roommate->term);

        $requestor = StudentFactory::getStudentByUsername($roommate->requestor, $roommate->term);
        $name = $requestor->getFullName();
        \NQ::Simple('hms', NotificationView::SUCCESS, "You and $name are confirmed as roommates.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}
