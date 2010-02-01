<?php

class HousingApplicationWelcomeView extends View {

	private $student;
	private $submitCmd;
	private $requiredTerms;
	
	public function __construct(Student $student, Command $submitCmd, $requiredTerms)
	{
		$this->student		= $student;
		$this->submitCmd	= $submitCmd;
		$this->requiredTerms= $requiredTerms;
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['ENTRY_TERM'] = Term::toString($this->student->getApplicationTerm());
		$tpl['REQUIRED_TERMS'] = array();
		
		foreach($this->requiredTerms as $t){
			if($t['required'] == 0){
				continue;
			}
			
			if(Term::getTermSem($t['term']) == TERM_FALL){
				$tpl['REQUIRED_TERMS'][] = array('REQ_TERM'=>Term::toString($t['term']) . ' - ' . Term::toString(Term::getNextTerm($t['term'])));
			}else{
				$tpl['REQUIRED_TERMS'][] = array('REQ_TERM'=>Term::toString($t['term']));
			}
		}

        # Get the applications the user has on file so we can mark them off
        $appsOnFile = HousingApplication::getAllApplications($this->student->getUsername());

        $tpl['APPLIED_TERMS'] = array();
        foreach($appsOnFile as $t) {
            if(Term::getTermSem($t->getTerm()) == TERM_FALL) {
                $tpl['APPLIED_TERMS'][] = array('APP_TERM'=>Term::toString($t->getTerm()) . ' - ' . Term::toString(Term::getNextTerm($t->getTerm())));
            } else {
                $tpl['APPLIED_TERMS'][] = array('APP_TERM'=>Term::toString($t->getTerm()));
            }
        }

		$contactCmd = CommandFactory::getCommand('ShowContactForm');
		
		$tpl['CONTACT_LINK'] = $contactCmd->getLink('contact us');
		
		# Setup the form for the 'continue' button.
		$form = new PHPWS_Form;
		$this->submitCmd->initForm($form);
		
		$form->addSubmit('submit', 'Continue');
		$form->setExtra('submit', 'class="hms-application-submit-button"');

		$form->mergeTemplate($tpl);
		$tpl = $form->getTemplate();

		$studentType = $this->student->getType();

        if(count($appsOnFile) > 0) {
            // User is now past step one.  No longer just welcoming, we are now welcoming back.
            return PHPWS_Template::process($tpl, 'hms', 'student/welcome_back_screen.tpl');
        }
		
		# Application deadline has not passed, so show welcome page
		if($studentType == TYPE_FRESHMEN || $studentType == TYPE_NONDEGREE){
			return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_freshmen.tpl');
		}else{
			return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_transfer.tpl');
		}
	}
}

?>
