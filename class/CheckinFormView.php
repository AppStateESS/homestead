<?php

class CheckinFormView extends View {

    private $student;
    private $assignment;
    private $application;
    private $hall;
    private $floor;
    private $room;

    public function __construct(Student $student, HMS_Assignment $assignment, HousingApplication $application = null, HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room)
    {
        $this->student      = $student;
        $this->assignment   = $assignment;
        $this->application  = $application;
        $this->hall         = $hall;
        $this->floor        = $floor;
        $this->room         = $room;
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

        // Cell Phone
        $form->addText('cell_phone');
        $form->setLabel('cell_phone', 'Cell Phone');

        if(!is_null($this->application)){
            $form->setValue('cell_phone', $this->application->getCellPhone());
        }

        /*********************
         * Emergency Contact *
        *********************/
        $form->addText('emergency_contact_name');
        $form->addText('emergency_contact_relationship');
        $form->addText('emergency_contact_phone');
        $form->addText('emergency_contact_email');
        $form->addTextArea('emergency_medical_condition');

        if(!is_null($this->application)){
            $form->setValue('emergency_contact_name', $this->application->getEmergencyContactName());
            $form->setValue('emergency_contact_relationship', $this->application->getEmergencyContactRelationship());
            $form->setValue('emergency_contact_phone', $this->application->getEmergencyContactPhone());
            $form->setValue('emergency_contact_email', $this->application->getEmergencyContactEmail());
            $form->setValue('emergency_medical_condition', $this->application->getEmergencyMedicalCondition());
        }

        $form->setLabel('emergency_contact_name', 'Name');
        $form->setLabel('emergency_contact_relationship', 'Relationship');
        $form->setLabel('emergency_contact_phone', 'Phone #');
        $form->setLabel('emergency_contact_email', 'Email');

        /******************
         * Missing Person *
        ******************/

        $form->addText('missing_person_name');
        $form->addText('missing_person_relationship');
        $form->addText('missing_person_phone');
        $form->addText('missing_person_email');

        if(!is_null($this->application)){
            $form->setValue('missing_person_name', $this->application->getMissingPersonName());
            $form->setValue('missing_person_relationship', $this->application->getMissingPersonRelationship());
            $form->setValue('missing_person_phone', $this->application->getMissingPersonPhone());
            $form->setValue('missing_person_email', $this->application->getMissingPersonEmail());
        }

        $form->setLabel('missing_person_name', 'Name');
        $form->setLabel('missing_person_relationship', 'Relationship');
        $form->setLabel('missing_person_phone', 'Phone #');
        $form->setLabel('missing_person_email', 'Email');

        // Key code
        $form->addText('key_code');
        $form->setLabel('key_code', 'Key Code &#35;');

        $form->addSubmit('submit', 'Continue');
        $form->setClass('submit', 'btn btn-primary');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/checkinForm.tpl');
    }

}

?>
