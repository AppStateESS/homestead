<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');
PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class EmergencyContactFormView extends hms\View {

    private $student;
    private $application;
    private $term;

    public function __construct(Student $student, $term, HousingApplication $application = NULL)
    {
        $this->student		= $student;
        $this->term			= $term;
        $this->application  = $application;
    }

    public function show()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('EmergencyContactFormSubmit');
        $submitCmd->setTerm($this->term);
        $submitCmd->initForm($form);

        $tpl = array();

        /****************
         * Display Info *
        ****************/
        $tpl['TERM']			= Term::toString($this->term);
        $tpl['STUDENT_NAME']    = $this->student->getFullName();
        $form->addText('cell_phone');
        $form->addCssClass('cell_phone', 'form-control');

        /*********************
         * Emergency Contact *
         *********************/
        $form->addText('emergency_contact_name');
        $form->addCssClass('emergency_contact_name', 'form-control');
        $form->addText('emergency_contact_relationship');
        $form->addCssClass('emergency_contact_relationship', 'form-control');
        $form->addText('emergency_contact_phone');
        $form->addCssClass('emergency_contact_phone', 'form-control');
        $form->addText('emergency_contact_email');
        $form->addCssClass('emergency_contact_email', 'form-control');
        $form->addTextArea('emergency_medical_condition');
        $form->addCssClass('emergency_medical_condition', 'form-control');

        if(!is_null($this->application)){
            $form->setValue('cell_phone', $this->application->getCellPhone());
            $form->setValue('emergency_contact_name', $this->application->getEmergencyContactName());
            $form->setValue('emergency_contact_relationship', $this->application->getEmergencyContactRelationship());
            $form->setValue('emergency_contact_phone', $this->application->getEmergencyContactPhone());
            $form->setValue('emergency_contact_email', $this->application->getEmergencyContactEmail());
            $form->setValue('emergency_medical_condition', $this->application->getEmergencyMedicalCondition());
        }

        /******************
         * Missing Person *
         ******************/
        $form->addText('missing_person_name');
        $form->addCssClass('missing_person_name', 'form-control');
        $form->addText('missing_person_relationship');
        $form->addCssClass('missing_person_relationship', 'form-control');
        $form->addText('missing_person_phone');
        $form->addCssClass('missing_person_phone', 'form-control');
        $form->addText('missing_person_email');
        $form->addCssClass('missing_person_email', 'form-control');

        if(!is_null($this->application)){
            $form->setValue('missing_person_name', $this->application->getMissingPersonName());
            $form->setValue('missing_person_relationship', $this->application->getMissingPersonRelationship());
            $form->setValue('missing_person_phone', $this->application->getMissingPersonPhone());
            $form->setValue('missing_person_email', $this->application->getMissingPersonEmail());
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Emergency Contact Form");

        return PHPWS_Template::process($tpl,'hms','student/emergency_contact_form.tpl');
    }
}
