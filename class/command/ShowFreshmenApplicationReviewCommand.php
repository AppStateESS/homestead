<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');

class ShowFreshmenApplicationReviewCommand extends Command {

	private $term;
	private $mealOption;
	private $lifestyleOption;
	private $preferredBedtime;
	private $roomCondition;
	private $rlcInterest;

	public function setTerm($term)
	{
		$this->term = $term;
	}

	public function getRequestVars()
	{
		$vars = $_REQUEST; // Carry forward the existing context

		// Overwrite the old action
		unset($vars['module']);
		$vars['action'] = 'ShowFreshmenApplicationReview';
		$vars['term']	= $this->term;

		return $vars;
	}

	public function execute(CommandContext $context)
	{
		$term = $context->get('term');
		$student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

		$errorCmd = CommandFactory::getCommand('ShowHousingApplicationForm');
		$errorCmd->setTerm($term);
		$errorCmd->setAgreedToTerms(1);

		try{
			$application = HousingApplicationFactory::getApplicationFromContext($context, $term, $student);
		}catch(Exception $e){
			NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
			$errorCmd->redirect();
		}
		
		//TODO side thingie

		PHPWS_Core::initModClass('hms', 'FreshmenApplicationReview.php');
		$view = new FreshmenApplicationReview($student, $term, $application);
		$context->setContent($view->show());
	}
}

?>