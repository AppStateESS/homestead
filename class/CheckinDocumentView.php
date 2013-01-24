<?php 

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

class CheckinDocumentView extends View {

    private $checkin;

    public function __construct(Checkin $checkin)
    {
        $this->checkin = $checkin;
    }

    public function show()
    {
        $term = Term::getCurrentTerm();
        $student = StudentFactory::getStudentByBannerId($this->checkin->getBannerId(), $term);

        $assignment = HMS_Assignment::getAssignmentByBannerId($this->checkin->getBannerId(), $term);

        $tpl = array();

        $tpl['NAME'] = $student->getName();
        $tpl['ASSIGNMENT'] = $assignment->where_am_i();

        $pdfCmd = CommandFactory::getCommand('GenerateInfoCard');
        $pdfCmd->setBannerId($student->getBannerId());

        $tpl['INFO_CARD_LINK'] = $pdfCmd->getLink('Resident Information Card');

        return PHPWS_Template::process($tpl, 'hms', 'admin/checkinComplete.tpl');
    }
}

?>