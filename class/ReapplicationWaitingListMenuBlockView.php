<?php

class ReapplicationWaitingListMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $application;

    public function __construct($term, $startDate, $endDate, LotteryApplication $application)
    {
        $this->term = $term;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->application = $application;
    }
    
    public function show()
    {
        $tpl = array();
        
        $now = time();
        
        if($this->startDate > $now){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::get_long_date_time($this->startDate);
        }else if($this->endDate < $now){
            $tpl['END_DEADLINE'] = HMS_Util::get_long_date_time($this->endDate);
        }else if ($this->application->waiting_list_hide == 1){
            $tpl['OPTED_OUT'] = " ";
        }else{
            $optOutCmd = CommandFactory::getCommand('LotteryShowWaitingListOptOut');
            $tpl['OUT_OUT_LINK'] = $optOutCmd->getLink('Click here to opt-out of the waiting list');
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/reApplicationWaitingListMenuBlock.tpl');
    }
}

?>