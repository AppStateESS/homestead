<?php

class DenyRlcApplicationCommand extends Command {

    private $applicationId;

    public function setApplicationId($id){
        $this->applicationId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'DenyRlcApplication', 'applicationId'=>$this->applicationId);
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve/deny RLC applications.');
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $app = HMS_RLC_Application::getApplicationById($context->get('applicationId'));
        $app->denied = 1;
        $app->save();

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($app->username, 28, Current_User::getUsername(), 'Application Denied');

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Application denied.');
        
        $context->goBack();
    }
}

