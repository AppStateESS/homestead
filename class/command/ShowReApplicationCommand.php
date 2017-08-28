<?php

namespace Homestead\command;

use \Homestead\Command;

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
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');

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

        PHPWS_Core::initModClass('hms', 'ReApplicationFormView.php');
        $view = new ReApplicationFormView($student, $term);

        $context->setContent($view->show());
    }
}
