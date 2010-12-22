<?php

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
            throw new InvalidArgumentException('Must set roommateId');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $roommate = new HMS_Roommate($id);
        if($roommate->id == 0) {
            throw new InvalidArgumentException('Invalid roommateId ' . $id);
        }

        $username = UserStatus::getUsername();
        if($username != $roommate->requestor) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("$username tried to break roommate pairing {$roommate->id}");
        }

        $roommate->delete();

        $other = StudentFactory::getStudentByUsername($roommate->get_other_guy($username), $roommate->term);

        HMS_Activity_Log::log_activity($other->getUsername(),
                                       ACTIVITY_STUDENT_CANCELLED_ROOMMATE_REQUEST,
                                       $username);

        // Email both parties
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::send_cancel_emails($roommate);

        $name = $other->getFullName();
        NQ::Simple('hms', HMS_NOTIFICATION_SUCCESS, "You have cancelled your request for $name as a roommate.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}

?>
