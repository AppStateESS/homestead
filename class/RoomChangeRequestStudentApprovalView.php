<?php

namespace Homestead;

class RoomChangeRequestStudentApprovalView {

    private $student;
    private $request;
    private $participants;
    private $thisParticipant;
    private $term;

    public function __construct(Student $student, RoomChangeRequest $request, Array $participants, RoomChangeParticipant $thisParticipant, $term)
    {
        $this->student = $student;
        $this->request = $request;
        $this->participants = $participants;
        $this->thisParticipant = $thisParticipant;
        $this->term = $term;
    }

    public function show()
    {
        $approveCmd = CommandFactory::getCommand('RoomChangeStudentApprove');
        $approveCmd->setParticipantId($this->thisParticipant->getId());
        $approveCmd->setRequestId($this->request->getId());


        $declineCmd = CommandFactory::getCommand('RoomChangeStudentDecline');
        $declineCmd->setParticipantId($this->thisParticipant->getId());
        $declineCmd->setRequestId($this->request->getId());


        $form = new \PHPWS_Form('roomchange_student_approve');
        $form->addHidden('foo', 'bar');

        $tpl = $form->getTemplate();
        $tpl['APPROVE_URI'] = $approveCmd->getURI();
        $tpl['DECLINE_URI'] = $declineCmd->getURI();

        $requestor = StudentFactory::getStudentByUsername($this->request->getState()->getCommittedBy(), $this->term);
        $tpl['REQUESTOR'] = $requestor->getName();

        // Build the table showing who is moving from/to which beds
        $participantRows = array();
        foreach($this->participants as $p){
            $row = array();

            $student = StudentFactory::getStudentByBannerId($p->getBannerId(), $this->term);
            $row['NAME'] = $student->getName();

            // If this participant is the person logged in, bold their name
            if ($student->getBannerId() == $this->thisParticipant->getBannerId()) {
                $row['STRONG_STYLE'] = 'success';
            } else {
                $row['STRONG_STYLE'] = '';
            }

            $fromBed = new HMS_Bed($p->getFromBed());
            $toBed   = new HMS_Bed($p->getToBed());

            $row['FROM_BED'] = $fromBed->where_am_i();
            $row['TO_BED']   = $toBed->where_am_i();

            $participantRows[] = $row;
        }

        $tpl['PARTICIPANTS'] = $participantRows;

        \PHPWS_Core::initCoreClass('Captcha.php');
        $tpl['CAPTCHA'] = \Captcha::get();

        return \PHPWS_Template::process($tpl, 'hms', 'student/roomChangeRequestStudentApprove.tpl');
    }
}
