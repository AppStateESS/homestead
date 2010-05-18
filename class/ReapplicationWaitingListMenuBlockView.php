<?php

class ReapplicationWaitingListMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $application;

    public function __construct($term, $startDate, $endDate, LotteryApplication $application = NULL)
    {
        $this->term = $term;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->application = $application;
    }
    
    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl = array();
        
        $now = time();
        
        if($this->startDate > $now){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::get_long_date_time($this->startDate);
        }else if($this->endDate < $now){
            $tpl['END_DEADLINE'] = HMS_Util::get_long_date_time($this->endDate);
        }else if (isset($this->application) && $this->application->waiting_list_hide == 1){
            $tpl['OPTED_OUT'] = " ";
        }else{
            $optOutCmd = CommandFactory::getCommand('LotteryShowWaitingListOptOut');
            $tpl['OUT_OUT_LINK'] = $optOutCmd->getLink('Click here to opt-out of the waiting list');
        }

        Layout::addPageTitle("Re-Application Waiting List");
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/reApplicationWaitingListMenuBlock.tpl');
    }
}

?>