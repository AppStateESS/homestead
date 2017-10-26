<?php

namespace Homestead\Command;

use \Homestead\HMS_Roommate;
use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ShowRoommateConfirmationCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'ShowRoommateConfirmation');

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
            throw new PermissionException("$username tried to display confirmation screen for pairing {$roommate->id}");
        }

        $tpl = array();

        $acceptCmd = CommandFactory::getCommand('ShowRoommateConfirmAccept');
        $acceptCmd->setRoommateId($roommate->id);
        $tpl['ACCEPT'] = $acceptCmd->getURI();

        $rejectCmd = CommandFactory::getCommand('RoommateReject');
        $rejectCmd->setRoommateId($roommate->id);
        $tpl['DECLINE'] = $rejectCmd->getURI();

        $cancelCmd = CommandFactory::getCommand('ShowStudentMenu');
        $tpl['CANCEL'] = $cancelCmd->getURI();

        $requestor = StudentFactory::getStudentByUsername($roommate->requestor, $roommate->term);
        $tpl['REQUESTOR_NAME'] = $requestor->getFullName();

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'student/roommate_accept_reject_screen.tpl'));
    }
}
