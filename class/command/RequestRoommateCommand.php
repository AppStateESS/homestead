<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RequestRoommateCommand extends Command
{
    private $term;
    private $rlcConfirm;

    public function getRequestVars()
    {
        $vars = array('action' => 'RequestRoommate');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        if(isset($this->rlcConfirm)) {
            $vars['rlcConfirm'] = $this->rlcConfirm;
        }

        return $vars;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setRlcConfirm($rlcConfirm)
    {
        $this->rlcConfirm = $rlcConfirm;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isUser()) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to request a roommate.');
        }

        $term = $context->get('term');
        $requestee = $context->get('username');
        $requestor = UserStatus::getUsername();
        $rlcConfirm = $context->get('rlcConfirm');

        if(empty($term)) {
            throw new InvalidArgumentException('Term was not specified.');
        }

        $err = CommandFactory::getCommand('ShowRequestRoommate');
        $err->setTerm($term);

        if(empty($requestee)) {
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'You did not enter a username.');
            $err->redirect();
        }

        if(!PHPWS_Text::isValidInput($requestee)) {
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'You entered an invalid username.  Please use letters and numbers only.');
            $err->redirect();
        }

        // Did they say go ahead and trash the RLC application?
        if(!empty($rlcConfirm)) {
            PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
            $rlcapp = new HMS_RLC_Application($requestor, $term);
            $rlcapp->delete();
        }

        // Attempt to Create Roommate Request
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $request = new HMS_Roommate();
        try {
            $request->request($requestor, $requestee, $term);
        } catch (RoommateCompatibilityException $rre) {
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, $rre->getMessage());
            $err->redirect();
        }

        $request->save();

        HMS_Activity_Log::log_activity($requestee, ACTIVITY_REQUESTED_AS_ROOMMATE, $requestor);

        // Email both parties
        $request->send_request_emails();

        // Notify
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $student = StudentFactory::getStudentByUsername($requestee, $term);
        $name = $student->getName();
        $fname = $student->getFirstName();
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "You have requested $name to be your roommate.  $fname has been emailed, and will need to log into HMS and approve your roommate request.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();

    }
}

?>
