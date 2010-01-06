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
		
		# Application deadline has not passed, so show welcome page
		if($studentType == TYPE_FRESHMEN || $studentType == TYPE_NONDEGREE){
			return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_freshmen.tpl');
		}else{
			return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_transfer.tpl');
		}
	}
}

?>