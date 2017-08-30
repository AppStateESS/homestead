<?php

namespace Homestead\Command;

use \Homestead\HMS_Lottery;
use \Homestead\UserStatus;
use \Homestead\NotificationView;
use \Homestead\HousingApplication;
use \Homestead\CommandFactory;
use \Homestead\StudentFactory;
use \Homestead\ReApplicationFormView;

class ShowReApplicationCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowReApplication');

        $vars['term'] = $this->term;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        // Double check that the student is eligible
        if(!HMS_Lottery::determineEligibility(UserStatus::getUsername())){
            \NQ::simple('hms', NotificationView::ERROR, 'You are not eligible to re-apply for on-campus housing for this semester.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }

        // Check if the student has already applied. If so, redirect to the student menu
        $result = HousingApplication::checkForApplication(UserStatus::getUsername(), $term);

        if($result !== FALSE){
            \NQ::simple('hms', NotificationView::WARNING, 'You have already re-applied for on-campus housing for that term.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $view = new ReApplicationFormView($student, $term);

        $context->setContent($view->show());
    }
}
