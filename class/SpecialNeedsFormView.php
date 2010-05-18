<?php

class SpecialNeedsFormView extends View {

	private $term;
	
	private $specialNeeds;
	private $submitCmd;
	
	public function __construct($term, $specialNeeds, Command $submitCmd)
	{
		$this->term = $term;
		
		$this->specialNeeds	= $specialNeeds;
		$this->submitCmd = $submitCmd;
	}

	public function show()
	{
		$form = new PHPWS_Form();
		$this->submitCmd->initForm($form);
		
		$form->dropElement('special_needs');
		
		$form->addCheck('special_needs', array('physical_disability','psych_disability','medical_need','gender_need'));
		$form->setLabel('special_needs', array('Physical disability', 'Psychological disability', 'Medical need', 'Transgender housing'));
		
		$checked = Array();
		
		if(isset($this->specialNeeds['physical_disability'])){
			$checked[] = 'physical_disability';
		}
		
		if(isset($this->specialNeeds['psych_disability'])){
			$checked[] = 'psych_disability';
		}
		
		if(isset($this->specialNeeds['medical_need'])){
			$checked[] = 'medical_need';
		}
		
		if(isset($this->specialNeeds['gender_need'])){
			$checked[] = 'gender_need';
		}
		
		$form->setMatch('special_needs', $checked);
		
		$form->addSubmit('submit', 'Continue');
		$form->setExtra('submit', 'class="hms-application-submit-button"');

        Layout::addPageTitle("Special Needs Form");
		
		return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/special_needs.tpl');
	}
}

?>