<?php

class LotteryShowConfirmRoommateRequestCommand extends Command {
    
    private $requestId;
    private $mealPlan;
    
    public function setRequestId($id){
        $this->requestId = $id;
    }
    
    public function setMealPlan($plan){
        $this->mealPlan = $plan;
    }
    
    public function getRequestVars(){
        $vars = array('action'=>'LotteryShowConfirmRoommateRequest');
        $vars['requestId'] = $this->requestId;
        $vars['mealPlan']   = $this->mealPlan;
        
        return $vars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryConfirmRoommateRequestView.php');
        
        $request = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('requestId'));
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $mealPlan = $context->get('meal_plan');
        
        $view = new LotteryConfirmRoommateRequestView($request, $term, $mealPlan);
        $context->setContent($view->show());
    }
}

?>