<?php
/**
 * SendRoomDamageNotificationsCommand.php
 *
 * Sends email notifications to students with assessed room damages
 * reported in the selected term.
 *
 * @author jbooker
 * @package homestead
 */
 
class SendRoomDamageNotificationsCommand extends Command {
	
    public function getRequestVars()
    {
    	return array('action'=>'SendRoomDamageNotifications');
    }
    
    public function execute(CommandContext $context)
    {
        
        
    	NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Room damage noties sent.');
        $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
        $cmd->redirect();
    }
}
?>
