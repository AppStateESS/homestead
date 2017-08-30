<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\RlcApplicationPage1View;
use \Homestead\ApplicationFeature;
use \Homestead\StudentFactory;

class ShowRlcApplicationViewCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars(){
        return array('action'=>'ShowRlcApplicationView', 'term'=>$this->term);
    }

    public function execute(CommandContext $context){
        $term = $context->get('term');

        if(!isset($term) || is_null($term) || empty($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $cmd     = CommandFactory::getCommand('ShowStudentMenu');
        $feature = ApplicationFeature::getInstanceByNameAndTerm('RlcApplication', $term);

        // Make sure the RLC application feature is enabled
        if( is_null($feature) || !$feature->isEnabled() ) {
            \NQ::simple('hms', NotificationView::ERROR, "Sorry, RLC applications are not avaialable for this term.");
            $cmd->redirect();
        }

        // Check feature's deadlines
        if( $feature->getStartDate() > time() ){
            \NQ::simple('hms', NotificationView::ERROR, "Sorry, it is too soon to fill out an RLC application.");
            $cmd->redirect();
        } else if( $feature->getEndDate() < time() ){
            \NQ::simple('hms', NotificationView::ERROR, "Sorry, the RLC application deadline has already passed. Please contact University Housing if you are interested in applying for a RLC.");
            $cmd->redirect();
        }

        // Get the Student object
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $view = new RlcApplicationPage1View($context, $student);

        $context->setContent($view->show());
    }
}
