<?php

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
            throw new PermissionException("$username tried to display confirmation screen for pairing {$roommate->id}");
        }

        $acceptForm = new PHPWS_Form;
        $acceptForm->setMethod('get');
        $acceptCmd = CommandFactory::getCommand('ShowRoommateConfirmAccept');
        $acceptCmd->setRoommateId($roommate->id);
        $acceptCmd->initForm($acceptForm);
        $acceptForm->addSubmit('Accept Roommate');

        $rejectForm = new PHPWS_Form;
        $rejectForm->setMethod('get');
        $rejectCmd = CommandFactory::getCommand('RoommateReject');
        $rejectCmd->setRoommateId($roommate->id);
        $rejectCmd->initForm($rejectForm);
        $rejectForm->addSubmit('Reject Roommate');

        $cancelForm = new PHPWS_Form;
        $cancelForm->setMethod('get');
        $cancelCmd = CommandFactory::getCommand('ShowStudentMenu');
        $cancelCmd->initForm($cancelForm);
        $cancelForm->addSubmit('Cancel');

        $requestor = StudentFactory::getStudentByUsername($roommate->requestor, $roommate->term);
        $tpl['REQUESTOR_NAME'] = $requestor->getFullName();

        $tpl['ACCEPT'] = PHPWS_Template::process($acceptForm->getTemplate(), 'hms', 'student/roommate_accept_reject_form.tpl');
        $tpl['REJECT'] = PHPWS_Template::process($rejectForm->getTemplate(), 'hms', 'student/roommate_accept_reject_form.tpl');
        $tpl['CANCEL'] = PHPWS_Template::process($cancelForm->getTemplate(), 'hms', 'student/roommate_accept_reject_form.tpl');

        $context->setContent(PHPWS_Template::process($tpl, 'hms', 'student/roommate_accept_reject_screen.tpl'));
    }
}


