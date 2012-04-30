<?php

PHPWS_Core::initModClass('hms', 'RlcApplicationPage1View.php');
PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

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
            throw new InvalidArgumentException('Missing term.');
        }

        $cmd     = CommandFactory::getCommand('ShowStudentMenu');
        $feature = ApplicationFeature::getInstanceByNameAndTerm('RlcApplication', $term);

        // Make sure the RLC application feature is enabled
        if( is_null($feature) || !$feature->isEnabled() ) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, RLC applications are not avaialable for this term.");
            $cmd->redirect();
        }

        // Check feature's deadlines
        if( $feature->getStartDate() > mktime() ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, it is too soon to fill out an RLC application.");
            $cmd->redirect();
        } else if( $feature->getEndDate() < mktime() ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, the RLC application deadline has already passed. Please contact University Housing if you are interested in applying for a RLC.");
            $cmd->redirect();
        }

        // Get the Student object
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());
        
        // Make sure the student is eligible for an RLC
        if($student->getCreditHours() > 15) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, you are not eligible for a Residential Learning Community for Underclassmen. Please visit the <a href="http://housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=293" target="_blank">Residential Learning Communities for Upperclassmen website</a> for information on applying for Residential Learning Communities for Upperclassmen.');
            $cmd = CommandFactory::getCommand('ShowStudentMenu');
            $cmd->redirect();
        }
        
        $view = new RlcApplicationPage1View($context, $student);

        $context->setContent($view->show());
    }
}

?>
