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

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        if($this->startDate > $now){
            // Too early!        
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['BEGIN_DEADLINE'] = HMS_Util::get_long_date_time($this->startDate);
        }else if($this->endDate < $now){
            // Too late 
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['END_DEADLINE'] = HMS_Util::get_long_date_time($this->endDate);
        }else if (isset($this->application) && $this->application->waiting_list_hide == 1){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>';
            $tpl['OPTED_OUT'] = " ";
        }else{
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/arrow.png" alt="Open"/>';            
            $optOutCmd = CommandFactory::getCommand('LotteryShowWaitingListOptOut');
            $tpl['OUT_OUT_LINK'] = $optOutCmd->getLink('Click here to opt-out of the waiting list');
        }

        Layout::addPageTitle("Re-Application Waiting List");
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/reApplicationWaitingListMenuBlock.tpl');
    }
}

?>