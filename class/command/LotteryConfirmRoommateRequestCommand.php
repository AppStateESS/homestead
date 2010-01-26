<?php

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
        $vars['mealPlan']   = $this->mealPlan;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $requestId = $context->get('requestId');
        $mealPlan = $context->get('mealPlan');

        $errorCmd = CommandFactory::getCommand('LotteryShowConfirmRoommateRequest');
        $errorCmd->setRequestId($requestId);

        # Confirm the captcha
        PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE);
        if($captcha === FALSE){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'The words you entered were incorrect. Please try again.');
            $errorCmd->redirect();
        }

        # Try to actually make the assignment
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        try{
            $result = HMS_Lottery::confirm_roommate_request(UserStatus::getUsername(), $requestId, $mealPlan);
        }catch(Exception $e){
            NQ::smiple('hms', HMS_NOTIFICATION_ERROR,'Sorry, there was an error confirming your roommate invitation. Please contact Housing & Residence Life.');
            $errorCmd->redirect();
        }

        # Log the fact that the roommate was accepted and successfully assigned
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_CONFIRMED_ROOMMATE,UserStatus::getUsername(), "Captcha: \"$captcha\"");

        $invite = HMS_Lottery::get_lottery_roommate_invite_by_id($requestId);
        $bed = new HMS_Bed($invite['bed_id']);
        
        $successCmd = CommandFactory::getCommand('LotteryShowConfirmedRoommateThanks');
        $successCmd->setRequestId($requestId);
        $successCmd->redirect();
    }
}

?>