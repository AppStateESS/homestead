<?php

namespace Homestead\Command;

use \Homestead\HMS_Lottery;
use \Homestead\LotteryDenyRoommateRequestView;

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
        $request = HMS_Lottery::get_lottery_roommate_invite_by_id($context->get('requestId'));
        $term = \PHPWS_Settings::get('hms', 'lottery_term');

        $view = new LotteryDenyRoommateRequestView($request, $term);
        $context->setContent($view->show());
    }
}
