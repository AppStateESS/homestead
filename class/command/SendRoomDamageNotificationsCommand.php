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
        PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        $term = Term::getSelectedTerm();

        // Get the total damages assessed for each student
        $damages = RoomDamageFactory::getAssessedDamagesStudentTotals($term);

        foreach($damages as $dmg)
        {
        	 $student = StudentFactory::getStudentByBannerId($dmg['banner_id'], $term);

            // Get the student's last checkout
            // (NB: the damages may be for multiple check-outs,
            // but we'll just take the last one)
            $checkout = CheckinFactory::getLastCheckoutForStudent($student);

            $bed = new HMS_Bed($checkout->getBedId());
            $room = $bed->get_parent();
            $floor = $room->get_parent();
            $hall = $floor->get_parent();

            $coordinators = $hall->getCoordinators();

            if($coordinators != null){
            	$coordinatorName  = $coordinators[0]->getDisplayName();
              $coordinatorEmail = $coordinators[0]->getEmail();
            } else {
            	$coordinatorName  = '(No coordinator set for this hall.)';
              $coordinatorEmail = '(No coordinator set for this hall.)';
            }

            HMS_Email::sendDamageNotification($student, $term, $dmg['sum'], $coordinatorName, $coordinatorEmail);
            HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_ROOM_DAMAGE_NOTIFICATION, $student->getUsername());
        }

        // Show a success message and redirect back to the main admin menu
    	  NQ::simple('hms', hms\NotificationView::SUCCESS, 'Room damage noties sent.');
        $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
        $cmd->redirect();
    }
}
