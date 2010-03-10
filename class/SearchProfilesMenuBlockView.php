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

        if(time() < $this->startDate){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else if(time() > $this->endDate){
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else if(is_null($this->profile)){
            $tpl['NO_PROFILE'] = '';
        }else{
            $searchCmd = CommandFactory::getCommand('ShowRoommateProfileSearch');
            $tpl['SEARCH_ROOMMATES'] = $searchCmd->getLink('Search roommate profiles.');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/searchProfilesMenuBlock.tpl');
    }

}

?>