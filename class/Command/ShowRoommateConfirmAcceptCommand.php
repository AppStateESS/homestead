<?php

namespace Homestead\Command;

 

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ShowRoommateConfirmAcceptCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'ShowRoommateConfirmAccept');

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

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $roommate = new HMS_Roommate($id);
        if($roommate->id == 0) {
            throw new \InvalidArgumentException('Invalid roommateId ' . $id);
        }

        $username = UserStatus::getUsername();
        if($username != $roommate->requestee) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("$username tried to display accept confirmation for roommate pairing {$roommate->id}");
        }

        \PHPWS_Core::initCoreClass('Captcha.php');

        $requestor = StudentFactory::getStudentByUsername($roommate->requestor, $roommate->term);

        $form = new \PHPWS_Form;

        $cmd = CommandFactory::getCommand('RoommateAccept');
        $cmd->setRoommateId($id);
        $cmd->initForm($form);

        $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
        $form->addTplTag('NAME', $requestor->getFullName());

        $form->addSubmit('Confirm Request');
        $form->addCssClass('submit', 'btn btn-success btn-lg');

        $context->setContent(\PHPWS_Template::process($form->getTemplate(), 'hms', 'student/roommate_accept_confirm.tpl'));
    }
}
