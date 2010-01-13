<?php
/**
 * ShowHallNotificationSelectCommand
 *
 *     Shows the interface for selecting hall(s) to notify.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */

PHPWS_Core::initModClass('hms', 'ShowHallNotificationSelectView.php');

class ShowHallNotificationSelectCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'=>'ShowHallNotificationSelect');

        return $vars;
    }

    public function execute(CommandContext $context){
        if(!Current_User::allow('hms', 'email_hall') && !Current_User::allow('hms', 'email_all')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to send messages.');
        }

        $view = new ShowHallNotificationSelectView();
        $context->setContent($view->show());
    }
}
?>
