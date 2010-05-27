<?php

class StudentMenuProfileView extends View {
    
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
        
        if(isset($this->profile) && !is_null($this->profile)){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>';
            $editCmd = CommandFactory::getCommand('ShowRoommateProfileForm');
            $editCmd->setTerm($this->term);
            $tpl['EDIT_PROFILE'] = $editCmd->getLink('view and edit your profile');
        } else if(time() < $this->startDate){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else if(time() > $this->endDate){
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
        }else{
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/arrow.png" alt="Open"/>';            
            $createCmd = CommandFactory::getCommand('ShowRoommateProfileForm');
            $createCmd->setTerm($this->term);
            $tpl['CREATE_PROFILE'] = $createCmd->getLink('Create your profile');
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/StudentProfileMenuBlock.tpl');
    }
}