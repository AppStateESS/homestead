<?php

class LotteryShowDenyRoommateRequestCommand extends Command {

    private $requestId;

    public function setRequestId($id){
        $this->requestId = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'LotteryShowDenyRoommateRequest', 'requestId'=>$this->requestId);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryDenyRoommateRequestView.php');

        $request = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('requestId'));
        $term = PHPWS_Settings::get('hms', 'lottery_term');

        $view = new LotteryDenyRoommateRequestView($request, $term);
        $context->setContent($view->show());
    }
}

?>