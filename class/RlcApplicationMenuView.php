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

        if(isset($this->application) && !is_null($this->application->id)) {
            $viewCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
            $viewCmd->setUsername($this->student->getUsername());
            $tpl['VIEW_APP'] = $viewCmd->getLink('view your application');
            $newCmd = CommandFactory::getCommand('ShowRlcApplicationView');
            $tpl['NEW_APP'] = $newCmd->getLink('submit a new application');
        }else if(time() < $this->startDate){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate); 
        }else if (time() > $this->endDate){
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else{
            $applyCmd = CommandFactory::getCommand('ShowRlcApplicationView');
            $tpl['APP_NOW'] = $applyCmd->getLink('Apply for a Residential Learning Community now.');
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/RlcApplicationMenuBlock.tpl');
    }
}

?>
