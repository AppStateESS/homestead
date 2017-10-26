<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\HousingApplicationFactory;
use \Homestead\Term;
use \Homestead\TermsAgreementView;
use \Homestead\UserStatus;

class ShowTermsAgreementCommand extends Command {

    private $term;
    private $agreedCommand;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setAgreedCommand(Command $cmd){
        $this->agreedCommand = $cmd;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowTermsAgreement', 'term'=>$this->term);

        if(!isset($this->agreedCommand)){
            return $vars;
        }

        // Get the action to do when someone agrees to the terms
        $onAgreeVars = $this->agreedCommand->getRequestVars();
        $onAgreeAction = $onAgreeVars['action'];

        // Unset it so it doesn't conlict
        unset($onAgreeVars['action']);

        // Reset it under a different name
        $onAgreeVars['onAgreeAction'] = $onAgreeAction;

        return array_merge($vars, $onAgreeVars);
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Recreate the agreedToCommand
        $agreedCmd = CommandFactory::getCommand($context->get('onAgreeAction'));
        $agreedCmd->setTerm($term);


        $roommateRequestId = $context->get('roommateRequestId');
        if(isset($roommateRequestId) && $roommateRequestId != null) {
        	$agreedCmd->setRoommateRequestId($roommateRequestId);
        }

        $mealPlan = $context->get('meal_plan');
        if(isset($mealPlan) && $mealPlan !== null){
            $agreedCmd->setMealPlan($mealPlan);
        }

        //$submitCmd = CommandFactory::getCommand('AgreeToTerms');
        //$submitCmd->setTerm($term);
        //$submitCmd->setAgreedCmd($agreedCmd);

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

        $application = HousingApplicationFactory::getApplicationFromSession($_SESSION['application_data'], $term, $student, $appType);

        $docusignCmd = CommandFactory::getCommand('BeginDocusign');
        $docusignCmd->setTerm($term);
        $docusignCmd->setReturnCmd($agreedCmd);
        $docusignCmd->setParentName($application->getEmergencyContactName());
        $docusignCmd->setParentEmail($application->getEmergencyContactEmail());

        $agreementView = new TermsAgreementView($term, $docusignCmd, $student);

        $context->setContent($agreementView->show());
    }
}
