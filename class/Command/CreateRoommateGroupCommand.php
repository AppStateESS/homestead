<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\HMS_Roommate;
use \Homestead\HMS_Activity_Log;
use \Homestead\Term;
use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;
use \Homestead\Exception\StudentNotFoundException;

/**
 * Handles administrately creating roommate groups
 */

class CreateRoommateGroupCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'CreateRoommateGroup');
    }

    public function execute(CommandContext $context){

        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'roommate_maintenance')){
            throw new PermissionException('You do not have permission to create/edit roommate groups.');
        }

        $term = Term::getSelectedTerm();

        # Check for reasonable input
        $roommate1 = trim($context->get('roommate1'));
        $roommate2 = trim($context->get('roommate2'));

        $viewCmd = CommandFactory::getCommand('CreateRoommateGroupView');
        $viewCmd->setRoommate1($roommate1);
        $viewCmd->setRoommate2($roommate2);

        if(is_null($roommate1) || empty($roommate1) || is_null($roommate2) || empty($roommate2)){
            \NQ::simple('hms', NotificationView::ERROR, 'Invalid user names.');
            $viewCmd->redirect();
        }

        try{
            $student1 = StudentFactory::getStudentByUsername($roommate1, $term);
            $student2 = StudentFactory::getStudentByUsername($roommate2, $term);
        } catch (StudentNotFoundException $e) {
            \NQ::simple('hms', NotificationView::ERROR, $e->getMessage());
            $viewCmd->redirect();
        }


        try{
            # Check if these two can live together
            HMS_Roommate::canLiveTogetherAdmin($student1, $student2, $term);
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, 'Could not create roommate group: ' . $e->getMessage());
            $viewCmd->redirect();
        }

        # Check for pending requests for either roommate and break them
        if(HMS_Roommate::countPendingRequests($roommate1, $term) > 0){
            \NQ::simple('hms', NotificationView::WARNING, "Warning: Pending roommate requests for $roommate1 were deleted.");
        }
        $result = HMS_Roommate::removeOutstandingRequests($roommate1, $term);
        if(!$result){
            \NQ::simple('hms', NotificationView::ERROR, "Error removing pending requests for $roommate1, roommate group was not created.");
            $viewCmd->redirect();
        }

        if(HMS_Roommate::countPendingRequests($roommate2, $term) > 0){
            \NQ::simple('hms', NotificationView::WARNING, "Warning: Pending roommate requests for $roommate2 were deleted.");
        }
        $result = HMS_Roommate::removeOutstandingRequests($roommate2, $term);
        if(!$result){
            \NQ::simple('hms', NotificationView::ERROR, "Error removing pending requests for $roommate2, roommate group was not created.");
            $viewCmd->redirect();
        }

        # Create the roommate group and save it
        $roommate_group                 = new HMS_Roommate();
        $roommate_group->term           = $term;
        $roommate_group->requestor      = $roommate1;
        $roommate_group->requestee      = $roommate2;
        $roommate_group->confirmed      = 1;
        $roommate_group->requested_on   = time();
        $roommate_group->confirmed_on   = time();

        $result = $roommate_group->save();

        if(!$result){
            \NQ::simple('hms', NotificationView::ERROR, 'Error saving roommate group.');
            $viewCmd->redirect();
        }else{
            HMS_Activity_Log::log_activity($roommate1, ACTIVITY_ADMIN_ASSIGNED_ROOMMATE, UserStatus::getUsername(), $roommate2);
            HMS_Activity_Log::log_activity($roommate2, ACTIVITY_ADMIN_ASSIGNED_ROOMMATE, UserStatus::getUsername(), $roommate1);

            \NQ::simple('hms', NotificationView::SUCCESS, 'Roommate group created successfully.');
            $viewCmd->redirect();
        }
    }
}
