<?php
PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

/*
 * ShowRlcApplicationView
 *
 *   Introductory page to the RLC Application.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package mod
 * @subpackage hms
 *
 * @see ShowRlCApplicationPage1View
 * @see ShowRlcApplicationPage2View
 */

class RlcApplicationView extends View {

    public function show(){
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());
        $cmd     = CommandFactory::getCommand('ShowStudentMenu');
        $feature = ApplicationFeature::getInstanceByNameAndTerm('RlcApplication', $student->getApplicationTerm());

        if( is_null($feature) || !$feature->isEnabled() ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, RLC applications are not avaialable for this term.");
            $cmd->redirect();
        }

        # Check feature deadlines
        if( $feature->getStartDate() > mktime() ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, it is too soon to fill out an RLC application.");
            $cmd->redirect();
        }else if( $feature->getEndDate() < mktime() ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, the RLC application deadline has already passed. Please contact Housing & Residence Life if you are interested in applying for a RLC.");
            $cmd->redirect();
        }
        
        $cmd = CommandFactory::getCommand('ShowRlcApplicationPage1View');
        $cmd->redirect();
    }
}
?>
