<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');
PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class EmergencyContactFormView extends Homestead\View{

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
        
        // Wrap up the business
        $form->addSubmit('submit', _('Continue'));
        $form->setExtra('submit', 'class="hms-application-submit-button"');
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Emergency Contact Form");

        return PHPWS_Template::process($tpl,'hms','student/emergency_contact_form.tpl');
    }
}

?>
