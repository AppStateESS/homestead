<?php

namespace Homestead\command;

use \Homestead\Command;

class LotteryShowConfirmCommand extends Command {

    private $roomId;
    private $roommates;
    private $mealPlan;

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
        $vars = array('action'=>'LotteryShowConfirm');

        $vars['roomId'] = $this->roomId;
        $vars['mealPlan'] = $this->mealPlan;
        $vars['roommates'] = $this->roommates;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $roomId = $context->get('roomId');
        $roommates = $context->get('roommates');
        $mealPlan = $context->get('mealPlan');

        $term = PHPWS_Settings::get('hms', 'lottery_term');

        PHPWS_Core::initModClass('hms', 'LotteryConfirmView.php');
        $view = new LotteryConfirmView($roomId, $mealPlan, $roommates, $term);

        $context->setContent($view->show());
    }

}
