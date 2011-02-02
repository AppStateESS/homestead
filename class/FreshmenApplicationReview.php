<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class FreshmenApplicationReview extends View {

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
        if($this->student->getType() != TYPE_FRESHMEN){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Cannot fill out a freshman application as a non-freshman!');
            $cmd = CommandFactory::getCommand('ShowContactForm');
            $cmd->redirect();
        }

        $tpl = array();
        $tpl['REVIEW_MSG']      = ''; // set this to show the review message

        $tpl['STUDENT_NAME']    = $this->student->getFullName();
        $tpl['GENDER']          = $this->student->getPrintableGender();
        $tpl['ENTRY_TERM']      = Term::toString($this->term);
        $tpl['CLASSIFICATION_FOR_TERM_LBL'] = $this->student->getPrintableClass();
        $tpl['STUDENT_STATUS_LBL']          = $this->student->getPrintableType();

        // TODO: This, right
        $sem = substr($this->term, 4, 2);
        if($sem == 10 || $sem == 40) {
            $tpl['MEAL_OPTION']         = HMS_Util::formatMealOption($this->app->getMealPlan());
            $tpl['LIFESTYLE_OPTION']    = $this->app->getLifestyleOption()	== 1?'Single gender':'Co-ed';
            $tpl['PREFERRED_BEDTIME']   = $this->app->getPreferredBedtime()	== 1?'Early':'Late';
            $tpl['ROOM_CONDITION']      = $this->app->getRoomCondition()	== 1?'Neat':'Cluttered';
        } else if($sem == 20 || $sem == 30) {
            $tpl['ROOM_TYPE'] = $this->app->getRoomType() == 0?'Two person':'Private (if available)';
        }

        $tpl['CELLPHONE']   = is_null($this->app->getCellPhone())?"(not provided)":$this->app->getCellPhone();

        //Special Needs
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

        if(Term::getTermSem($this->term) == TERM_FALL){
            $tpl['RLC_REVIEW'] = $this->app->rlc_interest == 0?'No':'Yes';
        }

        $form = new PHPWS_Form('hidden_form');
        $submitCmd = CommandFactory::getCommand('HousingApplicationConfirm');
        $submitCmd->setVars($_REQUEST);

        $submitCmd->initForm($form);

        $form->addSubmit('submit', 'Confirm & Continue');
        $form->setExtra('submit', 'class="hms-application-submit-button"');

        $redoCmd = CommandFactory::getCommand('ShowHousingApplicationForm');
        $redoCmd->setTerm($this->term);
        $redoCmd->setAgreedToTerms(1);
        $redoCmd->setVars($_REQUEST);

		$tpl['REDO_BUTTON'] = $redoCmd->getLink('modify your application');

        $form->mergeTemplate($tpl);

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/student_application.tpl');
	}
}

?>
