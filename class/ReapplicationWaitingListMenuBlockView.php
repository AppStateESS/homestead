<?php

class ReapplicationWaitingListMenuBlockView extends hms\View{

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

        if ($this->startDate > $now) {
            // Too early!
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::get_long_date_time($this->startDate);
        } else if ($this->endDate < $now) {
            // Too late
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::get_long_date_time($this->endDate);
        } else if (!isset($this->application)) {
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['STATUS'] = "locked";
            $tpl['DID_NOT_APPLY'] = "";
        } else if (isset($this->application) && isset($this->application->waiting_list_date)){
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $tpl['SIGNED_UP'] = "";
            
        } else if (isset($this->application)) {
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $cmd = CommandFactory::getCommand('ShowWaitingListSignup');
            $cmd->setTerm($this->term);
            $tpl['APPLY_LINK'] = $cmd->getLink("apply to the On-campus Housing Waiting List");
        } else {
            
        }

        Layout::addPageTitle("Re-Application Waiting List");

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/reApplicationWaitingListMenuBlock.tpl');
    }
}

?>
