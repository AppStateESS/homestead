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
        PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        
        $term = Term::getSelectedTerm();
        
        // Get the total damages assessed for each student
        $damages = RoomDamageFactory::getAssessedDamagesStudentTotals($term);
        
        var_dump($damages);
        
        foreach($damages as $dmg)
        {
        	$student = StudentFactory::getStudentByBannerId($dmg['banner_id'], $term);
            HMS_Email::sendDamageNotification($student, $term, $dmg['sum']);
        }
        
        // Show a success message and redirect back to the main admin menu
    	NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Room damage noties sent.');
        $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
        $cmd->redirect();
    }
}
?>
