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

        // Determine the application type, based on the term
        $sem = Term::getTermSem($term);

        switch ($sem){
            case TERM_FALL:
                $appType = 'fall';
                break;
            case TERM_SPRING:
                $appType = 'spring';
                break;
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $appType = 'summer';
                break;
        }

        try{
            $application = HousingApplicationFactory::getApplicationFromContext($context, $term, $student, $appType);
        }catch(Exception $e){
            NQ::simple('hms', hms\NotificationView::ERROR, $e->getMessage());
            $errorCmd->redirect();
        }

        PHPWS_Core::initModClass('hms', 'FreshmenApplicationReview.php');
        $view = new FreshmenApplicationReview($student, $term, $application);
        $context->setContent($view->show());
    }
}

?>
