<?php

/**
 * Handles administrately creating roommate groups
 */

class CreateRoommateGroupCommand extends Command {

	public function getRequestVars()
	{
		return array('action'=>'CreateRoommateGroup');
	}

	public function execute(CommandContext $context){

		if(!Current_User::allow('hms', 'roommate_maintenance')){
			PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
			throw new PermissionException('You do not have permission to create/edit roommate groups.');
		}

		PHPWS_Core::initModClass('hms', 'StudentFactory.php');
		PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
		
		$term = Term::getSelectedTerm();

		# Check for reasonable input
		$roommate1 = trim($context->get('roommate1'));
		$roommate2 = trim($context->get('roommate2'));

		$viewCmd = CommandFactory::getCommand('CreateRoommateGroupView');
		$viewCmd->setRoommate1($roommate1);
		$viewCmd->setRoommate2($roommate2);

		if(is_null($roommate1) || empty($roommate1) || is_null($roommate2) || empty($roommate2)){
			NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid user names.');
			$viewCmd->redirect();
		}

		$student1 = StudentFactory::getStudentByUsername($roommate1, $term);
		$student2 = StudentFactory::getStudentByUsername($roommate2, $term);

		try{
			# Check if these two can live together
			$result = HMS_Roommate::canLiveTogetherAdmin($student1, $student2, $term);
		}catch(Exception $e){
			NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not create roommate group: ' . $e->getMessage());
			$viewCmd->redirect();
		}

		# Check for pending requests for either roommate and break them
		if(HMS_Roommate::countPendingRequests($roommate1, $term) > 0){
			NQ::simple('hms', HMS_NOTIFICATION_WARNING, "Warning: Pending roommate requests for $roommate1 were deleted.");
		}
		$result = HMS_Roommate::removeOutstandingRequests($roommate1, $term);
		if(!$result){
			NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Error removing pending requests for $roommate1, roommate group was not created.");
			$viewCmd->redirect();
		}

		if(HMS_Roommate::countPendingRequests($roommate2, $term) > 0){
			NQ::simple('hms', HMS_NOTIFICATION_WARNING, "Warning: Pending roommate requests for $roommate2 were deleted.");
		}
		$result = HMS_Roommate::removeOutstandingRequests($roommate2, $term);
		if(!$result){
			NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Error removing pending requests for $roommate2, roommate group was not created.");
			$viewCmd->redirect();
		}

		# Create the roommate group and save it
		$roommate_group                 = new HMS_Roommate();
		$roommate_group->term           = $term;
		$roommate_group->requestor      = $roommate1;
		$roommate_group->requestee      = $roommate2;
		$roommate_group->confirmed      = 1;
		$roommate_group->requested_on   = mktime();
		$roommate_group->confirmed_on   = mktime();

		$result = $roommate_group->save();
		
		if(!$result){
			NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error saving roommate group.');
			$viewCmd->redirect();
		}else{
			PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
			HMS_Activity_Log::log_activity($roommate1, ACTIVITY_ADMIN_ASSIGNED_ROOMMATE, UserStatus::getUsername(), $roommate2);
			HMS_Activity_Log::log_activity($roommate2, ACTIVITY_ADMIN_ASSIGNED_ROOMMATE, UserStatus::getUsername(), $roommate1);
			
			NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Roommate group created successfully.');
			$viewCmd->redirect();
		}
	}
}

?>
