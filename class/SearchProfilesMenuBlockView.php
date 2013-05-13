<?php

class SearchProfilesMenuBlockView extends View {

    private $student;
    private $startDate;
    private $endDate;
    private $profile;
    private $term;

    public function __construct(Student $student, $startDate, $endDate, $profile = NULL, $term)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->profile = $profile;
        $this->term    = $term;
    }

    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";

        if(time() < $this->startDate){
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else if(time() > $this->endDate){
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else if(is_null($this->profile)){
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $tpl['NO_PROFILE'] = '';
        }else{
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $searchCmd = CommandFactory::getCommand('ShowRoommateProfileSearch');
            $searchCmd->setTerm($this->term);
            $tpl['SEARCH_ROOMMATES'] = $searchCmd->getLink('Search roommate profiles.');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/searchProfilesMenuBlock.tpl');
    }

}

?>