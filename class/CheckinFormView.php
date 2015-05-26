<?php

class CheckinFormView extends hms\View {

    private $student;
    private $assignment;
    private $hall;
    private $floor;
    private $room;
    private $checkin;

    public function __construct(Student $student, HMS_Assignment $assignment, HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room, Checkin $checkin = null)
    {
        $this->student      = $student;
        $this->assignment   = $assignment;
        $this->hall         = $hall;
        $this->floor        = $floor;
        $this->room         = $room;
        $this->checkin      = $checkin;
    }

    public function show()
    {
        $tpl = array();

        $tpl['NAME']		= $this->student->getName();
        $tpl['ASSIGNMENT']	= $this->assignment->where_am_i();
        $tpl['BANNER_ID'] 	= $this->student->getBannerId();

        $form = new PHPWS_Form('checkin_form');

        $submitCmd = CommandFactory::getCommand('CheckinFormSubmit');
        $submitCmd->setBannerId($this->student->getBannerId());
        $submitCmd->setHallId($this->hall->getId());
        $submitCmd->initForm($form);

        // Key code
        $form->addText('key_code');
        $form->setLabel('key_code', 'Key Code &#35;');
        $form->setExtra('key_code', 'autofocus');

        if (!is_null($this->checkin)) {
            $form->setValue('key_code', $this->checkin->getKeyCode());
        }

        $form->addSubmit('submit', 'Continue');
        $form->setClass('submit', 'btn btn-primary');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/checkinForm.tpl');
    }

}
