<?php

class StudentMenuProfileView extends Homestead\View{
    
    private $student;
    private $profile;
    private $term;
    
    private $startDate;
    private $endDate;
    
    public function __construct(Student $student, $startDate, $endDate, $term, RoommateProfile $profile = NULL)
    {
        $this->student = $student;
        $this->profile = $profile;
        $this->term = $term;
        
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";
        
        if (isset($this->profile) && !is_null($this->profile)) {
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $editCmd = CommandFactory::getCommand('ShowRoommateProfileForm');
            $editCmd->setTerm($this->term);
            $tpl['EDIT_PROFILE'] = $editCmd->getLink('view and edit your profile');
        } else if (time() < $this->startDate) {
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        } else if (time() > $this->endDate) {
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
        } else {
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $createCmd = CommandFactory::getCommand('ShowRoommateProfileForm');
            $createCmd->setTerm($this->term);
            $tpl['CREATE_PROFILE'] = $createCmd->getLink('Create your profile');
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/StudentProfileMenuBlock.tpl');
    }
}