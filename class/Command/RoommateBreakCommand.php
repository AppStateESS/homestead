<?php

namespace Homestead\Command;

use \Homestead\HMS_Roommate;
use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_Email;
use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\StudentFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RoommateBreakCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'RoommateBreak');

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
        if($username != $roommate->requestor && $username != $roommate->requestee) {
            throw new PermissionException("$username tried to break roommate pairing {$roommate->id}");
        }

        $err = CommandFactory::getCommand('ShowRoommateBreak');
        $err->setRoommateId($id);

        \PHPWS_Core::initCoreClass('Captcha.php');
        $verified = \Captcha::verify(TRUE);
        if($verified === FALSE || is_null($verified)) {
            \NQ::Simple('hms', NotificationView::ERROR, 'Sorry, please try again.');
            $err->redirect();
        }

        $roommate->delete();

        $other = StudentFactory::getStudentByUsername($roommate->get_other_guy($username), $roommate->term);;

        HMS_Activity_Log::log_activity($other->getUsername(),
                                       ACTIVITY_STUDENT_BROKE_ROOMMATE,
                                       $username,
                                       "$username broke pairing, CAPTCHA: $verified");
        HMS_Activity_Log::log_activity($username,
                                       ACTIVITY_STUDENT_BROKE_ROOMMATE,
                                       $other->getUsername(),
                                       "$username broke pairing, CAPTCHA: $verified");

        // Email both parties
        HMS_Email::send_break_emails($roommate, $username);

        $name = $other->getFullName();
        \NQ::Simple('hms', NotificationView::SUCCESS, "You have removed your roommate request for $name.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }

}
