<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\ReviewHallNotificationMessageView;

/**
 * ReviewHallNotificationMessageCommand
 *
 *  Show's the interface for reviewing notification messages.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */

class ReviewHallNotificationMessageCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'  => 'ReviewHallNotificationMessage');

        return $vars;
    }

    public function execute(CommandContext $context){
        /*
        if(!\Current_User::allow('hms', 'email_hall') && !\Current_User::allow('hms', 'email_all')){
            throw new PermissionException('You do not have permission to send messages.');
        }
        */
        if(is_null($context->get('hall')) && is_null($context->get('floor'))){
            \NQ::simple('hms', NotificationView::ERROR, 'You must select a hall to continue!');
            $cmd = CommandFactory::getCommand('ShowHallNotificationSelect');
            $cmd->redirect();
        }

        $subject   = $context->get('subject');
        $body      = $context->get('body');
        $anonymous = !is_null($context->get('anonymous')) ? $context->get('anonymous') : false;
        $halls     = $context->get('hall');
        $floors    = $context->get('floor');

        $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
        if(empty($subject)){
            \NQ::simple('hms', NotificationView::ERROR, 'You must fill in the subject line of the email.');
            $cmd->loadContext($context);
            $cmd->redirect();
        } else if(empty($body)){
            \NQ::simple('hms', NotificationView::ERROR, 'You must fill in the message to be sent.');
            $cmd->loadContext($context);
            $cmd->redirect();
        }

        $view = new ReviewHallNotificationMessageView($subject, $body, $anonymous, $halls, $floors);

        $context->setContent($view->show());
    }
}
