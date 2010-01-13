<?php
/**
 * ReviewHallNotificationMessageCommand
 *
 *  Show's the interface for reviewing notification messages.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */

PHPWS_Core::initModClass('hms', 'ReviewHallNotificationMessageView.php');

class ReviewHallNotificationMessageCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'  => 'ReviewHallNotificationMessage');

        return $vars;
    }

    public function execute(CommandContext $context){
        if(!Current_User::allow('hms', 'email_hall') && !Current_User::allow('hms', 'email_all')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to send messages.');
        }


        if(is_null($context->get('hall'))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must select a hall to continue!');
            $cmd = CommandFactory::getCommand('ShowHallNotificationSelect');
            $cmd->redirect();
        }

        $subject   = $context->get('subject');
        $body      = $context->get('body');
        $anonymous = !is_null($context->get('anonymous')) ? $context->get('anonymous') : false;
        $halls     = $context->get('hall');

        if(empty($subject)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must fill in the subject line of the email.');
            $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
            $cmd->loadContext($context);
            $cmd->redirect();
        } else if(empty($body)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must fill in the message to be sent.');
            $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
            $cmd->loadContext($context);
            $cmd->redirect();
        }

        $view = new ReviewHallNotificationMessageView($subject, $body, $anonymous, $halls);

        $context->setContent($view->show());
    }
}
?>
