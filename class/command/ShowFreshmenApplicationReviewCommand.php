<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');

class ShowFreshmenApplicationReviewCommand extends Command {

    private $term;
    private $mealOption;
    private $lifestyleOption;
    private $preferredBedtime;
    private $smoking_preference;
    private $roomCondition;
    private $rlcInterest;


    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getRequestVars()
    {
        //$vars = $_REQUEST; // Carry forward the existing context

        // Overwrite the old action
        //unset($vars['module']);
        $vars['action'] = 'ShowFreshmenApplicationReview';
        $vars['term']	= $this->term;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        // If we're coming from the special needs page, save any special needs flags the student selected
        if(array_key_exists('special_needs', $context->getParams()))
        {
            $this->saveSpecialNeeds($context);
        }

        // If they haven't agreed, redirect to the agreement
        // TODO: actually check via docusign API
        $event = $context->get('event');
        if(is_null($event) || !isset($event) || ($event != 'signing_complete' && $event != 'viewing_complete'))
        {
            $returnCmd = CommandFactory::getCommand('ShowFreshmenApplicationReview');
            $returnCmd->setTerm($term);

            $agreementCmd = CommandFactory::getCommand('ShowTermsAgreement');
            $agreementCmd->setTerm($term);
            $agreementCmd->setAgreedCommand($returnCmd);
            $agreementCmd->redirect();
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $errorCmd = CommandFactory::getCommand('ShowHousingApplicationForm');
        $errorCmd->setTerm($term);

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

            $application = HousingApplicationFactory::getApplicationFromSession($_SESSION['application_data'], $term, $student, $appType);
        }catch(Exception $e){
            NQ::simple('hms', hms\NotificationView::ERROR, $e->getMessage());
            $errorCmd->redirect();
        }


        PHPWS_Core::initModClass('hms', 'FreshmenApplicationReview.php');
        $view = new FreshmenApplicationReview($student, $term, $application);
        $context->setContent($view->show());
    }

    public function saveSpecialNeeds(CommandContext $context)
    {
      if(array_key_exists('physical_disability', $context->get('special_needs')))
      {
        $_SESSION['application_data']['special_needs']['physical_disability'] = 'physical_disability';
      }
      if(array_key_exists('psych_disability', $context->get('special_needs')))
      {
        $_SESSION['application_data']['special_needs']['psych_disability'] = 'psych_disability';
      }
      if(array_key_exists('medical_need', $context->get('special_needs')))
      {
        $_SESSION['application_data']['special_needs']['medical_need'] = 'medical_need';
      }
      if(array_key_exists('gender_need', $context->get('special_needs')))
      {
        $_SESSION['application_data']['special_needs']['gender_need'] = 'gender_need';
      }
    }

}
