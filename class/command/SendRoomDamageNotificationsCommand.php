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
    	$context->setContent('room damage notifications...');
    }
}
?>
