<?php

class OffCampusWaitingListMenuBlockView extends hms\View {

    private $term;
    private $startDate;
    private $endDate;
    private $application;

    public function __construct($term, $startDate, $endDate, $application = null)
    {
        $this->term         = $term;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->application  = $application;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";

        if(!is_null($this->application) && $this->application->getApplicationType() == 'offcampus_waiting_list'){
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $tpl['ALREADY_APPLIED'] = "";
        }else if(time() < $this->startDate){
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else if(time() > $this->endDate){
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else{
            //TODO
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $waitListCommand = CommandFactory::getCommand('ShowOffCampusWaitListApplication');
            $waitListCommand->setTerm($this->term);
            $tpl['WAIT_LIST_LINK'] = $waitListCommand->getLink('Apply to the waiting list');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/OffCampusWaitingListMenuBlock.tpl');
    }
}

?>
