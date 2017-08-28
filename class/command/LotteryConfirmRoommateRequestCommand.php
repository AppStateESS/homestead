<?php

namespace Homestead\command;

use \Homestead\Command;

class LotteryConfirmRoommateRequestCommand extends Command {

    private $requestId;
    private $mealPlan;

    public function setRequestId($id){
        $this->requestId = $id;
    }

    public function setMealPlan($plan){
        $this->mealPlan = $plan;
    }

    public function getRequestVars(){
        $vars = array('action'=>'LotteryConfirmRoommateRequest');

        $vars['requestId'] = $this->requestId;
        $vars['meal_plan']   = $this->mealPlan;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcMembershipFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcAssignmentSelfAssignedState.php');

        $requestId = $context->get('requestId');
        $mealPlanCode = $context->get('meal_plan');

        $errorCmd = CommandFactory::getCommand('LotteryShowConfirmRoommateRequest');
        $errorCmd->setRoommateRequestId($requestId);
        $errorCmd->setMealPlan($mealPlanCode);

        // Confirm the captcha
        \PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE);
        if($captcha === FALSE){
            \NQ::simple('hms', NotificationView::ERROR, 'The words you entered were incorrect. Please try again.');
            $errorCmd->redirect();
        }

        // Check for a meal plan
        if(!isset($mealPlanCode) || $mealPlanCode == '') {
        	\NQ::simple('hms', NotificationView::ERROR, 'Please choose a meal plan.');
            $errorCmd->redirect();
        }

        $term = \PHPWS_Settings::get('hms', 'lottery_term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Update the meal plan field on the application
        $app = HousingApplication::getApplicationByUser(UserStatus::getUsername(), $term);

        $app->setMealPlan($mealPlanCode);
        $app->save();

        // Create a meal plan based on that application
        $mealPlan = MealPlanFactory::createPlan($student, $term, $app);
        MealPlanFactory::saveMealPlan($mealPlan); // Just put it in the queue, don't send to Banner right away

        // Try to actually make the assignment
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        HMS_Lottery::confirm_roommate_request(UserStatus::getUsername(), $requestId);

        // Log the fact that the roommate was accepted and successfully assigned
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_CONFIRMED_ROOMMATE,UserStatus::getUsername(), "Captcha: \"$captcha\"");


        // Check for an RLC membership and update status if necessary
        // If this student was an RLC self-select, update the RLC memberhsip state
        $rlcAssignment = RlcMembershipFactory::getMembership($student, $term);
        if($rlcAssignment != null && $rlcAssignment->getStateName() == 'selfselect-invite') {
            $rlcAssignment->changeState(new RlcAssignmentSelfAssignedState($rlcAssignment));
        }

        $invite = HMS_Lottery::get_lottery_roommate_invite_by_id($requestId);

        $successCmd = CommandFactory::getCommand('LotteryShowConfirmedRoommateThanks');
        $successCmd->setRequestId($requestId);
        $successCmd->redirect();
    }
}
