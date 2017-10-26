<?php

namespace Homestead\Command;

use \Homestead\HMS_Roommate;
use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_Email;
use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RoommateRequestCancelCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'RoommateRequestCancel');

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
        if($username != $roommate->requestor) {
            throw new PermissionException("$username tried to break roommate pairing {$roommate->id}");
        }

        $roommate->delete();

        $other = StudentFactory::getStudentByUsername($roommate->get_other_guy($username), $roommate->term);

        HMS_Activity_Log::log_activity($other->getUsername(),
                                       ACTIVITY_STUDENT_CANCELLED_ROOMMATE_REQUEST,
                                       $username,
                                       "$username cancelled roommate request");
        HMS_Activity_Log::log_activity($username,
                                       ACTIVITY_STUDENT_CANCELLED_ROOMMATE_REQUEST,
                                       $other->getUsername(),
                                       "$username cancelled roommate request");

        // Email both parties
        HMS_Email::send_cancel_emails($roommate);

        $name = $other->getFullName();
        \NQ::Simple('hms', NotificationView::SUCCESS, "You have cancelled your roommate request for $name.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}
