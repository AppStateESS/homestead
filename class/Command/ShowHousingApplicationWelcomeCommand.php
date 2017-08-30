<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\UserStatus;
use \Homestead\ApplicationFeature;
use \Homestead\HousingApplicationWelcomeView;
use \Homestead\HousingApplicationNotAvailableView;

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
        $term = $context->get('term');

        $student   = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        $submitCmd = CommandFactory::getCommand('ShowHousingApplicationForm');
        $submitCmd->setTerm($term);

        //TODO get rid of the magic string
        $feature = ApplicationFeature::getInstanceByNameAndTerm('Application', $term);

        // If there is no feature, or if we're not inside the feature's deadlines...
        if(is_null($feature) || $feature->getStartDate() > time() || $feature->getEndDate() < time() || !$feature->isEnabled()){
            $view = new HousingApplicationNotAvailableView($student, $feature, $term);
        }else{
            $requiredTerms = HousingApplication::getAvailableApplicationTermsForStudent($student);
            $view = new HousingApplicationWelcomeView($student, $submitCmd, $requiredTerms);
        }

        $context->setContent($view->show());
    }
}
