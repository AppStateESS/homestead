<?php

  /**
   * DeleteRlcApplication
   *
   * This command allows a student to delete their
   * RLC Application.
   */

class DeleteRlcApplicationCommand extends Command
{
    private $term;

    public function getRequestVars()
    {
        return array('action' => 'DeleteRlcApplication', 'term'=>$this->term);
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setApplicationId($id)
    {
        $this->applicationId = $id;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $term = $context->get('term');

        // Application must exist
        $app = HMS_RLC_Application::getApplicationByUsername(UserStatus::getUsername(), $term);
        if(is_null($app)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'No RLC application exists.');
            $context->goBack();

        }
        // Assignemnt must NOT exist
        else if(!HMS_RLC_Assignment::checkForAssignment(UserStatus::getUsername(), $term)){
            // Delete the app
            $app->delete();
            
            // Log it
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_RLC_APPLICATION_DELETED, UserStatus::getUsername());
            
            // Show a notification and go back
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'RLC application deleted.');
            $context->goBack();
        }
        else {
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'You have already been assigned to an RLC.');
            $context->goBack();
        }

    }
}

?>