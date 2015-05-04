<?php

/**
 * Handles display of the RLC application menu block on the student main menu.
 *
 * @package HMS
 * @author Jeremy Booker
 */

class RlcApplicationMenuView extends hms\View {

    private $term;
    private $student;
    private $startDate;
    private $editDate;
    private $endDate;
    private $application;
    private $assignment;

    public function __construct($term, Student $student, $startDate, $editDate, $endDate, HMS_RLC_Application $application = NULL, HMS_RLC_Assignment $assignment = NULL)
    {
        $this->term         = $term;
        $this->student      = $student;
        $this->startDate    = $startDate;
        $this->editDate     = $editDate;
        $this->endDate      = $endDate;
        $this->application  = $application;
        $this->assignment   = $assignment;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
         
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";

        if(isset($this->assignment) && $this->assignment->getStateName() == 'declined'){
            // Student declined the invite
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['DECLINED'] = ""; //dummy tag
        }else if(isset($this->application) && !is_null($this->application->id) && isset($this->assignment) && $this->assignment->getStateName() == 'confirmed') {
            // Student has applied, been accepted, been invited, and confirmed that invitation to a particular community. The student can no longer view/edit the application.
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $tpl['CONFIRMED_RLC_NAME'] = $this->assignment->getRlcName();

        }else if(isset($this->assignment) && $this->assignment->getStateName() == 'invited'){
            // Studnet has applied, been assigned, and been sent an invite email
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            
            $tpl['INVITED_COMMUNITY_NAME'] = $this->assignment->getRlcName();
            
            $acceptCmd = CommandFactory::getCommand('ShowAcceptRlcInvite');
            $acceptCmd->setTerm($this->term);
            $tpl['INVITED_CONFIRM_LINK'] = $acceptCmd->getLink('accept or decline your invitation');
            
        }else if(isset($this->application) && !is_null($this->application->id)) {
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            // Let student view their application
            $viewCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
            $viewCmd->setAppId($this->application->getId());
            $tpl['VIEW_APP'] = $viewCmd->getLink('view your application');

            // The student can also delete their application if
            // they aren't already assigned
            PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
            if(!HMS_RLC_Assignment::checkForAssignment(UserStatus::getUsername(), $this->term)){

                $delCmd = CommandFactory::getCommand('DeleteRlcApplication');
                $delCmd->setTerm($this->term);

                $confCmd = CommandFactory::getCommand('JSConfirm');
                $confCmd->setLink('delete your application');
                $confCmd->setTitle('delete your application');
                $confCmd->setQuestion('Are you sure you want to delete your RLC Application?');
                $confCmd->setOnConfirmCommand($delCmd);
                $tpl['DELETE_TEXT'] = 'You may also ';
                $tpl['DELETE_APP'] = $confCmd->getLink('delete your application').'.';
            }

            if(time() < $this->editDate) {
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
