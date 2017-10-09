<?php

class VerifyAssignmentMenuBlockView extends hms\View {

    private $student;
    private $startDate;
    private $endDate;
    private $term;

    public function __construct(Student $student, $startDate, $endDate, $term)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->term = $term;
    }

    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        // Don't show the app-feature if it's not time
        if($this->startDate <= time()){
            $cmd = CommandFactory::getCommand('ShowVerifyAssignment');
            $cmd->setTerm($this->term);
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
