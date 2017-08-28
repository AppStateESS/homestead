<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ShowRoommateBreakCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'ShowRoommateBreak');

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
        if($roommate->id = 0) {
            throw new \InvalidArgumentException('Invalid roommateId ' . $id);
        }

        $username = UserStatus::getUsername();
        if($username != $roommate->requestor && $username != $roommate->requestee) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("$username tried to break roommate pairing {$roommate->id}");
        }

        \PHPWS_Core::initCoreClass('Captcha.php');

        // get other roommate
        $other = StudentFactory::getStudentByUsername($roommate->get_other_guy($username), $roommate->term);

        $form = new \PHPWS_Form;

        $cmd = CommandFactory::getCommand('RoommateBreak');
        $cmd->setRoommateId($id);
        $cmd->initForm($form);

        $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
        $form->addTplTag('NAME', $other->getFullName());

        $form->addSubmit('Confirm');
        $form->addCssClass('submit', 'btn btn-danger');

        $context->setContent(\PHPWS_Template::process($form->getTemplate(), 'hms', 'student/roommate_break_confirm.tpl'));
    }
}
