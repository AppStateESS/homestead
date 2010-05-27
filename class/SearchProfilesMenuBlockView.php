<?php

class SearchProfilesMenuBlockView extends View {

    private $student;
    private $startDate;
    private $endDate;
    private $profile;

    public function __construct(Student $student, $startDate, $endDate, $profile = NULL)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->profile = $profile;
    }

    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        if(time() < $this->startDate){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else if(time() > $this->endDate){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else if(is_null($this->profile)){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/arrow.png" alt="Open"/>';            
            $tpl['NO_PROFILE'] = '';
        }else{
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>';
            $searchCmd = CommandFactory::getCommand('ShowRoommateProfileSearch');
            $tpl['SEARCH_ROOMMATES'] = $searchCmd->getLink('Search roommate profiles.');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/searchProfilesMenuBlock.tpl');
    }

}

?>