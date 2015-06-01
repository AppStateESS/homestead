<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class FreshmenApplicationReview extends hms\View {

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
        $tpl['GENDER']          = $this->student->getPrintableGender();
        $tpl['ENTRY_TERM']      = Term::toString($this->term);
        $tpl['CLASSIFICATION_FOR_TERM_LBL'] = $this->student->getPrintableClass();
        $tpl['STUDENT_STATUS_LBL']          = $this->student->getPrintableType();

        // TODO: This, right
        $sem = Term::getTermSem($this->term);
        if($sem == TERM_SPRING || $sem == TERM_FALL) {
            $tpl['LIFESTYLE_OPTION']    = $this->app->getLifestyleOption()	== 1?'Single gender':'Co-ed';
            $tpl['PREFERRED_BEDTIME']   = $this->app->getPreferredBedtime()	== 1?'Early':'Late';
            $tpl['ROOM_CONDITION']      = $this->app->getRoomCondition()	== 1?'Neat':'Cluttered';
        } else if($sem == 20 || $sem == 30) {
            $tpl['ROOM_TYPE'] = $this->app->getRoomType() == 0?'Two person':'Private (if available)';
        }

        $tpl['MEAL_OPTION']         = HMS_Util::formatMealOption($this->app->getMealPlan());

        /* Cell Phone */
        $tpl['CELLPHONE']   = is_null($this->app->getCellPhone())?"(not provided)":$this->app->getCellPhone();

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


        /* Special Needs */
        $special_needs = "";
        if(isset($this->app->physical_disability)){
            $special_needs = 'Physical disability<br />';
        }
        if(isset($this->app->psych_disability)){
            $special_needs .= 'Psychological disability<br />';
        }
        if(isset($this->app->medical_need)){
            $special_needs .= 'Medical need<br />';
        }
        if(isset($this->app->gender_need)){
            $special_needs .= 'Gender need<br />';
        }

        if($special_needs == ''){
            $special_needs = 'None';
        }
        $tpl['SPECIAL_NEEDS_RESULT'] = $special_needs;

        /* RLC Interest */
        if(Term::getTermSem($this->term) == TERM_FALL){
            $tpl['RLC_REVIEW'] = $this->app->rlc_interest == 0?'No':'Yes';
        }

        $form = new PHPWS_Form('hidden_form');
        $submitCmd = CommandFactory::getCommand('HousingApplicationConfirm');
        $submitCmd->setVars($_REQUEST);

        $submitCmd->initForm($form);

        $tpl['CONFIRM_BTN'] = ''; // Dummy template var to turn on confirm button

        $redoCmd = CommandFactory::getCommand('ShowHousingApplicationForm');
        $redoCmd->setTerm($this->term);
        $redoCmd->setVars($_REQUEST);

        $tpl['REDO_BUTTON'] = $redoCmd->getURI();

        $form->mergeTemplate($tpl);

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/student_application.tpl');
    }
}
