<?php

namespace Homestead\command;

use \Homestead\Command;

class ShowHousingApplicationFormCommand extends Command {

    private $term;
    private $vars;

    public function setTerm($term){
        $this->term = $term;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    public function getRequestVars()
    {
        $vars = $this->vars;

        $vars['action'] = 'ShowHousingApplicationForm';
        unset($vars['module']);


        if(isset($this->term)){
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFormView.php');

        // Make sure we have a valid term
        $term = $context->get('term');

        if(is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Missing term.');
        }

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

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Make sure the student agreed to the terms, if not, send them back to the terms & agreement command
        //$event = $context->get('event');

        // If they haven't agreed, redirect to the agreement
        // TODO: actually check via docusign API
        /*
        if(is_null($event) || !isset($event) || ($event != 'signing_complete' && $event != 'viewing_complete')){
            $agreementCmd = CommandFactory::getCommand('ShowTermsAgreement');
            $agreementCmd->setTerm($term);
            $agreementCmd->setAgreedCommand(CommandFactory::getCommand('ShowHousingApplicationForm'));
            $agreementCmd->redirect();
        }
        */

        // Check to see if the student's PIN is enabled. Don't let the student apply if the PIN is disabled.
        if($student->pinDisabled()){
            $pinCmd = CommandFactory::getCommand('ShowPinDisabled');
            $pinCmd->redirect();
        }

        // Check to see if the user has an existing application for the term in question
        $existingApplication = HousingApplication::getApplicationByUser($student->getUsername(), $term);


        // Check for an in-progress application on the context, ignore any exceptions (in case there isn't an application on the context)
        try {
            //TODO check to see if it looks like there might be something on the context before trying this
            $existingApplication = HousingApplicationFactory::getApplicationFromContext($context, $term, $student, $appType);
        }catch(Exception $e){
            // ignored
            $contextApplication = NULL;
        }

        $existingMealPlan = MealPlanFactory::getMealByBannerIdTerm($student->getBannerId(), $term);

        $appView = new HousingApplicationFormView($student, $term, $existingApplication, $existingMealPlan);

        $context->setContent($appView->show());
    }
}
