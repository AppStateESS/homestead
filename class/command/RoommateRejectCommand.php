<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RoommateRejectCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'RoommateReject');

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

        if(UserStatus::getUsername() != $roommate->requestee) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("$username tried to reject roommate pairing {$roommate->id}");
        }

        $requestor = StudentFactory::getStudentByUsername($roommate->requestor, $roommate->term);
        $name = $requestor->getFullName();
        $username = $requestor->getUsername();

        $roommate->delete();

        HMS_Activity_Log::log_activity($roommate->requestor,
                                       ACTIVITY_REJECTED_AS_ROOMMATE,
                                       $roommate->requestee,
                                       "$roommate->requestee rejected $roommate->requestor's request");
        HMS_Activity_Log::log_activity($roommate->requestee,
                                       ACTIVITY_REJECTED_AS_ROOMMATE,
                                       $roommate->requestor,
                                       "$roommate->requestee rejected $roommate->requestor's request");

        // Email both parties
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::send_reject_emails($roommate);

        NQ::Simple('hms', hms\NotificationView::SUCCESS, "<b>You rejected the roommate request from $name.</b>  If this was an error, you may re-request using their username, <b>$username</b>.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}

?>
