<?php

  /**
   * RemoveDenyRlcAssignment
   *
   * This is basically a macro command for RemoveRlcAssignmentCommand 
   * and DenyRlcApplicationCommand.
   *
   * @author Robert Bost <bostrt at tux dot appstate dot edu>
   */

class RemoveDenyRlcAssignmentCommand extends Command
{
    private $appId;
    private $assignId;
    public function getRequestVars()
    {
        return array('action'   => 'RemoveDenyRlcAssignment',
                     'appId'    => $this->appId,
                     'assignId' => $this->assignId);
    }

    public function setAppId($id){
        $this->appId =$id;
    }
    public function setAssignmentId($id){
        $this->assignId = $id;
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve/deny RLC applications.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        // Remove assignment
        $assignment = HMS_RLC_Assignment::getAssignmentById($context->get('assignId'));

        //Remove assignment
         if(!is_null($assignment)){
            $assignment->delete();
        }else{
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not find an RLC assignment with that id.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_RLC_APPLICATION_DELETED, Current_User::getUsername(), 'Assignment Removed');
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Assignment deleted.');

        // Deny application
        $app = HMS_RLC_Application::getApplicationById($context->get('appId'));
        if(is_null($app)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not find an RLC application with that id.');
            return;
        }
        $app->denied = 1;
        $app->save();

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Application denied.');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($app->username, 28, Current_User::getUsername(), 'Application Denied');

        $context->goBack();
    }

}
?>