<?php

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
            throw new InvalidArgumentException('Must set roommateId');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $roommate = new HMS_Roommate($id);
        if($roommate->id == 0) {
            throw new InvalidArgumentException('Invalid roommateId ' . $id);
        }

        $username = UserStatus::getUsername();
        if($username != $roommate->requestor && $username != $roommate->requestee) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("$username tried to break roommate pairing {$roommate->id}");
        }

        $err = CommandFactory::getCommand('ShowRoommateBreak');
        $err->setRoommateId($id);

        PHPWS_Core::initCoreClass('Captcha.php');
        $verified = Captcha::verify(TRUE);
        if($verified === FALSE || is_null($verified)) {
            NQ::Simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, please try again.');
            $err->redirect();
        }

        $roommate->delete();

        HMS_Activity_Log::log_activity($roommate->requestee,
                                       ACTIVITY_STUDENT_BROKE_ROOMMATE,
                                       $roommate->requestor,
                                       "CAPTCHA: $verified");

        // Email both parties
        $roommate->send_break_emails();

        $other = StudentFactory::getStudentByUsername($roommate->get_other_guy($username), $roommate->term);;
        $name = $other->getFullName();
        NQ::Simple('hms', HMS_NOTIFICATION_SUCCESS, "You and $name are no longer marked as roommates.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }

}

?>
