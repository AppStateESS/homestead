<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RequestRoommateCommand extends Command
{
    private $term;

    public function getRequestVars()
    {
        $vars = array('action' => 'RequestRoommate');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    public function setTerm($term)
    {
        $this->term = $term;
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

        if(empty($term)) {
            throw new InvalidArgumentException('Term was not specified.');
        }

        $err = CommandFactory::getCommand('ShowRequestRoommate');
        $err->setTerm($term);

        if(empty($requestee)) {
            NQ::simple('hms', hms\NotificationView::WARNING, 'You did not enter a username.');
            $err->redirect();
        }

        if(!PHPWS_Text::isValidInput($requestee)) {
            NQ::simple('hms', hms\NotificationView::WARNING, 'You entered an invalid username.  Please use letters and numbers only.');
            $err->redirect();
        }

        // Attempt to Create Roommate Request
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $request = new HMS_Roommate();
        try {
            $request->request($requestor, $requestee, $term);
        } catch (RoommateCompatibilityException $rre) {
            NQ::simple('hms', hms\NotificationView::WARNING, $rre->getMessage());
            $err->redirect();
        }

        $request->save();

        $endTime = $request->calc_req_expiration_date();

        $expirationMsg = " expires on " . date('m/d/Y h:i:s a', $endTime);

        HMS_Activity_Log::log_activity($requestee, ACTIVITY_REQUESTED_AS_ROOMMATE, $requestor, "$requestor requested $requestee" . $expirationMsg);
        HMS_Activity_Log::log_activity($requestor, ACTIVITY_REQUESTED_AS_ROOMMATE, $requestee, "$requestor requested $requestee" . $expirationMsg);

        // Email both parties
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::send_request_emails($request);

        // Notify
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $student = StudentFactory::getStudentByUsername($requestee, $term);
        $name = $student->getName();
        $fname = $student->getFirstName();
        NQ::simple('hms', hms\NotificationView::SUCCESS, "You have requested $name to be your roommate.  $fname has been emailed, and will need to log into HMS and approve your roommate request.");

        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();

    }
}
