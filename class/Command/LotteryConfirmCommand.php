<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Room;
use \Homestead\Bed;
use \Homestead\HMS_Lottery;
use \Homestead\HMS_Assignment;
use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_Util;
use \Homestead\HMS_Email;
use \Homestead\RlcMembershipFactory;
use \Homestead\RlcAssignmentSelfAssignedState;
use \Homestead\HousingApplicationFactory;
use \Homestead\MealPlanFactory;
use \Homestead\Term;
use \Homestead\Exception\StudentNotFoundException;


class LotteryConfirmCommand extends Command {

    private $roomId;
    private $mealPlan;

    // TODO: Also defined in HMS_Lottery. Choose one of the other.
    const INVITE_TTL_HRS = 48;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function setRoommates($roommates){
        $this->roommates = $roommates;
    }

    public function setMealPlan($plan){
        $this->mealPlan = $plan;
    }

    public function getRequestVars(){
        $vars = array('action'=>'LotteryConfirm');

        $vars['roomId'] = $this->roomId;
        $vars['mealPlan'] = $this->mealPlan;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $roomId = $context->get('roomId');
        $roommates = $context->get('roommates');

	$mealPlanCode = $context->get('mealPlan');
	if(empty($mealPlanCode) && !empty($context->get('meal_plan'))){
		$mealPlanCode = $context->get('meal_plan');
	}else{
		$mealPlanCode = '1';
	}

        $term = \PHPWS_Settings::get('hms', 'lottery_term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $errorCmd = CommandFactory::getCommand('LotteryShowConfirm');
        $errorCmd->setRoomId($roomId);
        $errorCmd->setRoommates($roommates);
        $errorCmd->setMealPlan($mealPlanCode);

        $successCmd = CommandFactory::getCommand('LotteryShowConfirmed');
        $successCmd->setRoomId($roomId);

        \PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = \Captcha::verify(TRUE); // returns the words entered if correct, FALSE otherwise
        //$captcha = TRUE;
        if($captcha === FALSE) {
            \NQ::simple('hms', NotificationView::ERROR, 'Sorry, the words you eneted were incorrect. Please try again.');
            $errorCmd->redirect();
        }

        $room = new Room($roomId);

        // Check for an RLC assignment in the self-select status
        $rlcAssignment = RlcMembershipFactory::getMembership($student, $term);

        // Check roommates for validity
        foreach($roommates as $bed_id => $username){
            // Double check the student is valid
            try{
                $roommate = StudentFactory::getStudentByUsername($username, $term);
            }catch(StudentNotFoundException $e){
                \NQ::simple('hms', NotificationView::ERROR, "$username is not a valid student. Please choose a different roommate.");
                $errorCmd->redirect();
            }

            // Make sure the bed is still empty
            $bed = new Bed($bed_id);

            if($bed->has_vacancy() != TRUE){
                \NQ::simple('hms', NotificationView::ERROR, 'One or more of the beds in the room you selected is no longer available. Please try again.');
                $errorCmd->redirect();
            }

            // Make sure none of the needed beds are reserved
            if($bed->is_lottery_reserved()){
                \NQ::simple('hms', NotificationView::ERROR, 'One or more of the beds in the room you selected is no longer available. Please try again.');
                $errorCmd->redirect();
            }

            // Double check the genders are all the same as the person logged in
            if($student->getGender() != $roommate->getGender()){
                \NQ::simple('hms', NotificationView::ERROR, "$username is a different gender. Please choose a roommate of the same gender.");
                $errorCmd->redirect();
            }

            // Double check the genders are the same as the room (as long as the room isn't AUTO)
            if($room->gender_type != AUTO && $roommate->getGender() != $room->gender_type){
                \NQ::simple('hms', NotificationView::ERROR, "$username is a different gender. Please choose a roommate of the same gender.");
                $errorCmd->redirect();
            }

            // If this student is an RLC-self-selection, then each roommate must be in the same RLC and in the selfselect-invite state too
            if($rlcAssignment != null && $rlcAssignment->getStateName() == 'selfselect-invite') {
                // This student is an RLC-self-select, so check the roommate's RLC status
                $roommateRlcAssign = RlcMembershipFactory::getMembership($roommate, $term);
                // Make sure the roommate is a member of the same RLC and is eligible for self-selection
                if($roommateRlcAssign == null || $roommateRlcAssign->getStateName() != 'selfselect-invite' || $rlcAssignment->getRlc()->getId() != $roommateRlcAssign->getRlc()->getId()) {
                    \NQ::simple('hms', NotificationView::ERROR, "$roommate must be a member of the same learning community as you, and must also be eligible for self-selction.");
                    $errorCmd->redirect();
                }

            // Otherwise (if not RLC members), make sure each roommate is eligible
            } else if(HMS_Lottery::determineEligibility($username) !== TRUE){
                \NQ::simple('hms', NotificationView::ERROR, "$username is not eligible for assignment.");
                $errorCmd->redirect();
            }

            // If this student is a self-select RLC member, then this student must also be a self-select RLC member of the same RLC
            if($rlcAssignment != null && $rlcAssignment->getStateName() == 'selfselect-invite')
            {
            	$roommateRlcAssign = RlcMembershipFactory::getMembership($roommate, $term);
                if($roommateRlcAssign == null || $roommateRlcAssign->getStateName() != 'selfselect-invite' || $rlcAssignment->getRlc()->getId() != $roommateRlcAssign->getRlc()->getId()) {
                	\NQ::simple('hms', NotificationView::ERROR, "$username must be a member of the same learning community as you, and must also be eligible for self-selction.");
                    $errorCmd->redirect();
                }
            }
        }

        // If the room's gender is 'AUTO' and no one is assigned to it yet, switch it to the student's gender
        if($room->gender_type == AUTO && $room->get_number_of_assignees() == 0){
            $room->gender_type = $student->getGender();
            $room->save();
        }

        // Assign the student to the requested bed
        $bed_id = array_search(UserStatus::getUsername(), $roommates); // Find the bed id of the student who's logged in

        try{
            $result = HMS_Assignment::assignStudent($student, \PHPWS_Settings::get('hms', 'lottery_term'), NULL, $bed_id, 'Confirmed lottery invite', TRUE, ASSIGN_LOTTERY);
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, 'Sorry, there was an error creating your room assignment. Please try again or contact University Housing.');
            $errorCmd->redirect();
        }

        // Log the assignment
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_ROOM_CHOSEN, UserStatus::getUsername(), 'Captcha: ' . $captcha);

        // Update the student's meal plan in the housing application, just for future reference
        $app = HousingApplicationFactory::getAppByStudent($student, $term);
        $app->setMealPlan($mealPlanCode);
        $app->save();

        // Create a meal plan based on that application
        $mealPlan = MealPlanFactory::createPlan($student, $term, $app);
        MealPlanFactory::saveMealPlan($mealPlan); // Just put it in the queue, don't send to Banner right away

        // If this student was an RLC self-select, update the RLC memberhsip state
        if($rlcAssignment != null && $rlcAssignment->getStateName() == 'selfselect-invite') {
        	$rlcAssignment->changeState(new RlcAssignmentSelfAssignedState($rlcAssignment));
        }

        foreach($roommates as $bed_id => $username){
            // Skip the current user
            if($username == $student->getUsername()){
                continue;
            }

            # Reserve the bed for the roommate
            $expires_on = time() + (self::INVITE_TTL_HRS * 3600);
            $bed = new Bed($bed_id);
            if(!$bed->lottery_reserve($username, $student->getUsername(), $expires_on)){
                \NQ::smiple('hms', NotificationView::WARNING, "You were assigned, but there was a problem reserving space for your roommates. Please contact University Housing.");
                $successCmd->redirect();
            }

            HMS_Activity_Log::log_activity($username, ACTIVITY_LOTTERY_REQUESTED_AS_ROOMMATE, $student->getUsername(), 'Expires: ' . HMS_Util::get_long_date_time($expires_on));

            # Invite the selected roommates
            $roomie = StudentFactory::getStudentByUsername($username, $term);
            $term = \PHPWS_Settings::get('hms', 'lottery_term');
            $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));
            HMS_Email::send_lottery_roommate_invite($roomie, $student, $expires_on, $room->where_am_i(), $year);
        }

        HMS_Email::send_lottery_assignment_confirmation($student, $room->where_am_i(), $term);

        $successCmd->redirect();
    }
}
