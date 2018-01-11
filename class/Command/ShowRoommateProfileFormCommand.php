<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\RoommateProfileFactory;
use \Homestead\RoommateProfileFormView;

class ShowRoommateProfileFormCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action'=>'ShowRoommateProfileForm', 'term'=>$this->term);
    }

    public function execute(CommandContext $context)
    {
        // TODO make sure the application feature is really enabled

        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $profile = RoommateProfileFactory::getProfile($student->getBannerID(), $term);

        $view = new RoommateProfileFormView($profile, $term);
        $context->setContent($view->show());
    }
}
