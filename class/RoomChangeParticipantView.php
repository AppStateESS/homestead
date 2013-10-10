<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

/**
 * View class that represents a single participant in the
 * RoomChangeManageView
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeParticipantView extends View {

    private $participant;

    private $student;

    public function __construct(RoomChangeParticipant $participant)
    {
        $this->participant = $participant;

        $this->student = StudentFactory::getStudentByBannerId($this->participant->getBannerId(), Term::getSelectedTerm());
    }

    public function show()
    {
        $tpl = array();

        // Student info
        $tpl['NAME'] = $this->student->getName();
        $tpl['BANNER_ID'] = $this->student->getBannerId();

        // From bed
        $fromBed = new HMS_Bed($this->participant->getFromBed());
        $tpl['FROM_ROOM'] = $fromBed->where_am_i();

        // To bed
        $toBedId = $this->participant->getToBed();

        if (isset($toBedId)) {
            // If set, show the selected bed
            $toBed = new HMS_Bed($toBedId);
            $tpl['TO_ROOM'] = $toBed->where_am_i();

            // Show the edit link if user has permissions, and status allows it
            // TODO
        } else{
            // Room selection is for room switchs only, not swaps. For swaps, the destination bed
            // is already known.

            // If user is student's current RD, allow
            // Show the "select a bed" dialog
            $tpl['BED_SELECT'] = "<a class='showSelectBed' href=''>Select a Bed</a>"; // dummy tag to show the link
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/roomChangeParticipantView.tpl');
    }
}

?>