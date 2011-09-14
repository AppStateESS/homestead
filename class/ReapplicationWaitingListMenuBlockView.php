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
        $tpl['STATUS'] = "";

        if($this->startDate > $now){
            // Too early!
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::get_long_date_time($this->startDate);
        }else if($this->endDate < $now){
            // Too late
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::get_long_date_time($this->endDate);
        }else if(isset($this->application) && $this->application->waiting_list_hide == 1){
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $tpl['OPTED_OUT'] = " ";
        }else if(!is_null($this->application)){
            $tpl['ICON'] = FEATURE_OPEN_ICON;

            // Get this student's position on the wait list
            $tpl['POSITION']    = $this->application->getWaitListPosition();
            $tpl['TOTAL']       = HMS_Lottery::getSizeOfOnCampusWaitList();

            $optOutCmd  = CommandFactory::getCommand('LotteryShowWaitingListOptOut');
            $tpl['OPT_OUT_LINK'] = $optOutCmd->getLink('click here to opt-out of the waiting list');
        }else{
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['STATUS'] = "locked";
            $tpl['DID_NOT_APPLY'] = "";
        }

        Layout::addPageTitle("Re-Application Waiting List");

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/reApplicationWaitingListMenuBlock.tpl');
    }
}

?>
