<?php

  /**
   * DeleteRlcApplication
   *
   * This command allows a student to delete their
   * RLC Application.
   */

class DeleteRlcApplicationCommand extends Command
{
    public function getRequestVars()
    {
        return array('action' => 'DeleteRlcApplication');
    }

    public function setApplicationId($id)
    {
        $this->applicationId = $id;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        // Application must exist
        $app = HMS_RLC_Application::getApplicationByUsername(UserStatus::getUsername(), Term::getSelectedTerm());
        if(is_null($app)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'No RLC application exists.');
            $context->goBack();

        } 
        // Assignemnt must NOT exist
        else if(!HMS_RLC_Assignment::checkForAssignment(UserStatus::getUsername(), Term::getSelectedTerm())){
            // Delete the app
            $app->delete();
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