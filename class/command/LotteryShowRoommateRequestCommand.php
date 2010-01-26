<?php

class LotteryShowRoommateRequestCommand extends Command {
    
    private $requestId;
    
    public function setRequestId($id){
        $this->requestId = $id;
    }
    
    public function getRequestVars(){
        $vars = array('action'=>'LotteryShowRoommateRequest');
        
        $vars['requestId'] = $this->requestId;
        
        return $vars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryRoommateRequestView.php');
        
        $request = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('requestId'));
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        
        $view = new LotteryRoommateRequestView($request, $term);
        $context->setContent($view->show());
    }
}

?>