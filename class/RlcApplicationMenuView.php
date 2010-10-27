<?php

class RlcApplicationMenuView extends View {

    private $term;
    private $student;
    private $startDate;
    private $editDate;
    private $endDate;
    private $application;

    public function __construct($term, Student $student, $startDate, $editDate, $endDate, HMS_RLC_Application $application = NULL)
    {
        $this->term         = $term;
        $this->student      = $student;
        $this->startDate    = $startDate;
        $this->editDate     = $editDate;
        $this->endDate      = $endDate;
        $this->application  = $application;
    }

    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";

        if(isset($this->application) && !is_null($this->application->id)) {
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            // Let student view their application
            $viewCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
            $viewCmd->setAppId($this->application->getId());
            $tpl['VIEW_APP'] = $viewCmd->getLink('view your application');

            // The student can also delete their application if
            // they aren't already assigned
            PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
            if(HMS_RLC_Application::checkForApplication(UserStatus::getUsername(), Term::getSelectedTerm()) &&
               !HMS_RLC_Assignment::checkForAssignment(UserStatus::getUsername(), Term::getSelectedTerm())){
                $delCmd = CommandFactory::getCommand('JSConfirm');
                $delCmd->setLink('delete your application');
                $delCmd->setTitle('delete your application');
                $delCmd->setQuestion('Are you sure you want to delete your RLC Application?');
                $delCmd->setOnConfirmCommand(CommandFactory::getCommand('DeleteRlcApplication'));
                $tpl['DELETE_TEXT'] = 'You may also ';
                $tpl['DELETE_APP'] = $delCmd->getLink().'.';
            }
            
            if(time() < $this->editDate){
                $newCmd = CommandFactory::getCommand('ShowRlcApplicationView');
                $newCmd->setTerm($this->term);
                $tpl['NEW_APP'] = $newCmd->getLink('submit a new application');
            }
        }else if(time() < $this->startDate){
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate); 
        }else if (time() > $this->endDate){
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else{
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $applyCmd = CommandFactory::getCommand('ShowRlcApplicationView');
            $applyCmd->setTerm($this->term);
            $tpl['APP_NOW'] = $applyCmd->getLink('Apply for a Residential Learning Community now.');
        }

        Layout::addPageTitle("RLC Application Menu");

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/RlcApplicationMenuBlock.tpl');
    }
}

?>
