<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\HousingApplicationFactory;
use \Homestead\HousingApplication;
use \Homestead\HMS_Assignment;
use \Homestead\NotificationView;
use \Homestead\HMS_RLC_Assignment;
use \Homestead\HMS_RLC_Application;
use \Homestead\HMS_Activity_Log;
use \Homestead\Term;
use \Homestead\Exception\PermissionException;

/**
 * CancelHousingApplicationCommand
 *
 * Cancels a housing application and optionally un-assigns the student.
 * Inteded to be called via ajax.
 *
 * @author jbooker
 */
class CancelHousingApplicationCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'CancelHousingApplication');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'cancel_housing_application')){
            throw new PermissionException('You do not have permission to cancel housing applications.');
        }
               
        // Check for a housing application id
        $applicationId = $context->get('applicationId');

        if(!isset($applicationId) || is_null($applicationId)){
            throw new \InvalidArgumentException('Missing housing application id.');
        }

        // Check for a cancellation reason
        $cancelReason = $context->get('cancel_reason');
        if(!isset($cancelReason) || is_null($cancelReason)){
            throw new \InvalidArgumentException('Missing cancellation reason.');
        }

        // Load the housing application
        $application = HousingApplicationFactory::getApplicationById($applicationId);

        // Load the student
        $student = $application->getStudent();
        $username = $student->getUsername();
        $term = $application->getTerm();

        // Load the cancellation reasons
        $reasons = HousingApplication::getCancellationReasons();

        // Check for an assignment and remove it

        // Decide which term to use - If this application is in a past fall term, then use the current term
        if($term < Term::getCurrentTerm() && Term::getTermSem($term) == TERM_FALL){
            $assignmentTerm = Term::getCurrentTerm();
        }else{
            $assignmentTerm = $term;
        }

        $assignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $assignmentTerm);

        if(isset($assignment)){
            // TODO: Don't hard code cancellation refund percentage
            HMS_Assignment::unassignStudent($student, $assignmentTerm, 'Application cancellation: ' . $reasons[$cancelReason], UNASSIGN_CANCEL, 100);
        }

        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($username, $term);

        if(!is_null($rlcAssignment)){
            $rlcAssignment->delete();
        }

        $rlcApplication = HMS_RLC_Application::getApplicationByUsername($username, $term);

        if(!is_null($rlcApplication)){
          $rlcApplication->denied = 1;
          $rlcApplication->save();

          HMS_Activity_Log::log_activity($username, ACTIVITY_DENIED_RLC_APPLICATION, \Current_User::getUsername(), Term::toString($term) . ' Denied RLC Application due to Contract Cancellation');
        }

        // Cancel the application
        $application->cancel($cancelReason);
        $application->save();

        \NQ::simple('hms', NotificationView::SUCCESS, 'Application successfully cancelled.');
        $viewCmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
        $viewCmd->redirect();
        
    }
}
