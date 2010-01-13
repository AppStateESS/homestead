<?php
/**
 * ShowHallNotificationEditCommand
 *
 *     Shows the interface for editing notification messages
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */

PHPWS_Core::initModClass('hms', 'ShowHallNotificationEditView.php');

class ShowHallNotificationEditCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'  => 'ShowHallNotificationEdit');

        foreach(array('anonymous', 'subject', 'body', 'hall') as $key){
            if( !is_null($this->context) && !is_null($this->context->get($key)) ){
                $vars[$key] = $this->context->get($key);
            }
        }

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
        $view      = new ShowHallNotificationEditView($subject, $body, $anonymous, $halls);

        $context->setContent($view->show());
    }
}
?>
