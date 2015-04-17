<?php

class VerifyAssignmentMenuBlockView extends hms\View {

    private $student;
    private $startDate;
    private $endDate;

    public function __construct(Student $student, $startDate, $endDate)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        // Don't show the app-feature if it's not time
        if($this->startDate <= mktime()){
            $cmd = CommandFactory::getCommand('ShowVerifyAssignment');
            $cmd->setUsername($this->student->getUsername());
            $tpl['VIEW_APP'] = $cmd->getLink('here');
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/verifyAssignmentMenuBlock.tpl');
        } else {
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/verifyAssignmentMenuBlock.tpl');
        }
    }
}

?>