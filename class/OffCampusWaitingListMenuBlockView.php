<?php

class OffCampusWaitingListMenuBlockView extends View {

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
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        if(!is_null($this->application)){
                $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>';
            $tpl['ALREADY_APPLIED'] = "";
        }else if(time() < $this->startDate){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else if(time() > $this->endDate){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else{
            //TODO
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/arrow.png" alt="Open"/>';            
            $waitListCommand = CommandFactory::getCommand('ShowOffCampusWaitListApplication');
            $waitListCommand->setTerm($this->term);
            $tpl['WAIT_LIST_LINK'] = $waitListCommand->getLink('Apply to the waiting list');
        }

        Layout::addPageTitle("Off Campus Waiting List");

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/OffCampusWaitingListMenuBlock.tpl');
    }
}

?>