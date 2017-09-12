<?php

namespace Homestead\Command;

use \Homestead\HousingApplicationFactory;
use \Homestead\HousingApplicationCancelView;
use \Homestead\HousingApplication;
use \Homestead\HMS_Assignment;
use \Homestead\Term;

class ShowCancelHousingApplicationCommand extends Command {

    private $housingApp;

    public function setHousingApp(HousingApplication $app){
        $this->housingApp = $app;
    }

    public function getRequestVars()
    {
        return array('action'=>'ShowCancelHousingApplication', 'applicationId'=>$this->housingApp->getId());
    }

    public function getLink($text, $target = null, $cssClass = null, $title = null)
    {
        $uri = $this->getURI();
        return "<a href='$uri' class='cancelAppLink $cssClass' onClick='return false;'>$text</a>";
    }

    public function execute(CommandContext $context)
    {
        $applicationId = $context->get('applicationId');

        if(!isset($applicationId)){
            throw new \InvalidArgumentException('Missing application id.');
        }

        $application = HousingApplicationFactory::getApplicationById($applicationId);

        $student = $application->getStudent();

        // Decide which term to use - If this application is in a past fall term, then use the current term
        $term = $application->getTerm();
        if($term < Term::getCurrentTerm() && Term::getTermSem($term) == TERM_FALL){
            $assignmentTerm = Term::getCurrentTerm();
        }else{
            $assignmentTerm = $term;
        }

        $assignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $assignmentTerm);

        $view = new HousingApplicationCancelView($student, $application, $assignment);

        echo $view->show();
        exit;
    }
}
