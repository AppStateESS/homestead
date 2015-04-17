<?php

class EmergencyContactReview extends hms\View {

    private $student;
    private $term;
    private $app;

    public function __construct(Student $student, $term, HousingApplication $app)
    {
        $this->student	= $student;
        $this->term		= $term;
        $this->app		= $app;
    }

    public function show()
    {
        $tpl = array();
        $tpl['REVIEW_MSG']      = ''; // set this to show the review message

        $tpl['STUDENT_NAME']    = $this->student->getFullName();
        $tpl['TERM']      = Term::toString($this->term);

        /* Emergency Contact */
        $tpl['EMERGENCY_CONTACT_NAME'] 			= $this->app->getEmergencyContactName();
        $tpl['EMERGENCY_CONTACT_RELATIONSHIP']	= $this->app->getEmergencyContactRelationship();
        $tpl['EMERGENCY_CONTACT_PHONE'] 		= $this->app->getEmergencyContactPhone();
        $tpl['EMERGENCY_CONTACT_EMAIL'] 		= $this->app->getEmergencyContactEmail();
        
        $tpl['EMERGENCY_MEDICAL_CONDITION'] = $this->app->getEmergencyMedicalCondition();
        
        /* Missing Person */
        $tpl['MISSING_PERSON_NAME'] 		= $this->app->getMissingPersonName();
        $tpl['MISSING_PERSON_RELATIONSHIP']	= $this->app->getMissingPersonRelationship();
        $tpl['MISSING_PERSON_PHONE'] 		= $this->app->getMissingPersonPhone();
        $tpl['MISSING_PERSON_EMAIL'] 		= $this->app->getMissingPersonEmail();
        

        $form = new PHPWS_Form('hidden_form');
        $submitCmd = CommandFactory::getCommand('EmergencyContactConfirm');
        $submitCmd->setVars($_REQUEST);
        $submitCmd->initForm($form);

        $form->addSubmit('submit', 'Confirm & Continue');
        $form->setExtra('submit', 'class="hms-application-submit-button"');

        $redoCmd = CommandFactory::getCommand('ShowEmergencyContactForm');
        $redoCmd->setTerm($this->term);
        $redoCmd->setVars($_REQUEST);

        $tpl['REDO_BUTTON'] = $redoCmd->getLink('modify your information');

        $form->mergeTemplate($tpl);

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/emergency_contact_form.tpl');
    }
}

?>
