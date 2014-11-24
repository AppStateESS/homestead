<?php

class UpdateEmergencyContactMenuBlockView extends hms\View{

    private $student;
    private $startDate;
    private $endDate;
    private $application;

    public function __construct(Student $student, $startDate, $endDate, $application)
    {
        $this->student      = $student;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->application  = $application;
    }

    public function show()
    {
        $tpl = array();
        
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        if (is_null($this->application)) {      // No application
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['NOT_APP'] = "";   // this needs to be here to trigger the line in the template
        } else if (time() < $this->startDate) { // too early
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
        } else if (time() > $this->endDate) {   // too late
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        } else {
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $cmd = CommandFactory::getCommand('ShowEmergencyContactForm');
            $cmd->setTerm($this->application->getTerm());
            $tpl['UPDATE_CONTACT'] = $cmd->getLink('update your emergency contact info');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/updateEmergencyContactMenuBlock.tpl');
    }
}

?>
