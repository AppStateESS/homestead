<?php

class RlcApplicationMenuView extends View {
    
    private $student;
    private $startDate;
    private $endDate;
    private $application;
    
    public function __construct(Student $student, $startDate, $endDate, HMS_RLC_Application $application = NULL)
    {
        $this->student      = $student;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->application  = $application;
    }
    
    public function show()
    {
        $tpl = array();
        
        if(isset($this->application) && !is_null($this->application)) {
            $viewCmd = 'view your application';
            $tpl['VIEW_APP'] = $viewCmd;
            $newCmd = CommandFactory::getCommand('ShowRlcApplicationPage1View');
            $tpl['NEW_APP'] = $newCmd->getLink('submit a new application');
        }else if(time() < $this->startDate){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate); 
        }else if (time() > $this->endDate){
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else{
            $applyCmd = CommandFactory::getCommand('ShowRlcApplicationPage1View');
            $tpl['APP_NOW'] = $applyCmd->getLink('Apply for a Residential Learning Community now.');
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/RlcApplicationMenuBlock.tpl');
    }
}

?>