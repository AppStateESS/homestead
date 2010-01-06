<?php

class ShowHousingApplicationWelcomeCommand extends Command {
	
	private $term;
	
	public function setTerm($term){
		$this->term = $term;
	}
	
	public function getRequestVars(){
		return array('action'=>'ShowHousingApplicationWelcome', 'term'=>$this->term);
	}
	
	public function execute(CommandContext $context)
	{
		PHPWS_Core::initModClass('hms', 'StudentFactory.php');
		PHPWS_Core::initModClass('hms', 'HousingApplication.php');
		PHPWS_Core::initModClass('hms', 'HousingApplicationWelcomeView.php');
		
		$term = $context->get('term');
		
		$student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
		$submitCmd = CommandFactory::getCommand('ShowHousingApplicationForm');
		$submitCmd->setTerm($term);
		
		$requiredTerms = HousingApplication::getValidApplicationTerms($student->getApplicationTerm());
		
		$view = new HousingApplicationWelcomeView($student, $submitCmd, $requiredTerms);
		
		$context->setContent($view->show());
	}
}

?>