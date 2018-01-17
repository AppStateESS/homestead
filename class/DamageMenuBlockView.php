<?php

namespace Homestead;

class DamageMenuBlockView extends View {

    private $student;
    private $startDate;
    private $endDate;
    private $assignment;
    private $changeRequest;

    public function __construct(Student $student, $term, $startDate, $endDate, HMS_Assignment $assignment = null)
    {
        $this->student          = $student;
        $this->term             = $term;
        $this->startDate        = $startDate;
        $this->endDate          = $endDate;
        $this->assignment       = $assignment;
    }

    public function show()
    {
        $checkin = CheckinFactory::getCheckinByBannerId($this->student->getBannerId(), $this->term);

        // If there's no fall checkin, check for a checkin in the following Spring term (could be that we're looking a student menu for Fall that shows both fall-spring assignments)
        if($checkin === null && Term::getTermSem($this->term) == TERM_FALL){
            $checkin = CheckinFactory::getCheckinByBannerId($this->student->getBannerId(), Term::getNextTerm($this->term));

            // If that worked, change the term to Spring
            if($checkin !== null){
                $this->term = Term::getNextTerm($this->term);
            }
        }

        $tpl = array();
        $end = 0;

        if($checkin != null) {
            $end = strtotime(RoomDamage::SELF_REPORT_DEADLINE, $checkin->getCheckinDate());
        }

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        if($checkin === null) { // Student has not checked in yet.
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            if($end === 0) {
                $tpl['NO_CHECKIN'] = ''; // Dummy template var, text is in template
            } else {
                $tpl['END_DEADLINE'] = HMS_Util::get_long_date_time($end);
            }
        } else if (time() > $end) { // too late
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            if($end === 0) {
                $tpl['NO_CHECKIN'] = ''; // Dummy template var, text is in template
            } else {
                $tpl['END_DEADLINE'] = HMS_Util::get_long_date_time($end);
            }

        } else { // Damage reporting available
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $addRoomDmgsCmd = CommandFactory::getCommand('ShowStudentAddRoomDamages');
            $addRoomDmgsCmd->setTerm($this->term);

            $tpl['NEW_REQUEST'] = $addRoomDmgsCmd->getLink('add room damages');
            $tpl['DEADLINE'] = HMS_Util::get_long_date_time($end);
        }

        return \PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/roomDamageMenuBlock.tpl');
    }
}
