<?php

class AjaxDenyRlcApplicationCommand extends Command {

    private $applicationId;

    public function setApplicationId($id){
        $this->applicationId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'AjaxDenyRlcApplication', 'applicationId'=>$this->applicationId);
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            echo json_encode(array('success' => false,
                              'message' => 'You do not have permission to approve/deny RLC applications.'
            ));
            exit;
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $app = HMS_RLC_Application::getApplicationById($context->get('applicationId'));
        $app->denied = 1;
        $app->save();

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($app->username, 28, Current_User::getUsername(), 'Application Denied');

        echo json_encode(array('success' => true,
                               'message' => 'Successfully denied application'
        ));
        exit;
    }
}
