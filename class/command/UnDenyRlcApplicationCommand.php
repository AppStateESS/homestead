<?php

class UnDenyRlcApplicationCommand extends Command {

    private $applicationId;

    public function setApplicationId($id){
        $this->applicationId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'UnDenyRlcApplication', 'applicationId'=>$this->applicationId);
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve/deny RLC applications.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        
        $app = HMS_RLC_Application::getApplicationById($context->get('applicationId'));
        $app->denied = 0;
        $app->save();

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($app->username, 29, UserStatus::getUsername(), "Application un-denied");

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Application un-denied.');

        $successCmd = CommandFactory::getCommand('ShowDeniedRlcApplicants');
        $successCmd->redirect();
    }
}

