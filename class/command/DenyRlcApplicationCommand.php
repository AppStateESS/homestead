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

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id', $context->get('applicationId'));
        $db->addValue('denied', 1);

        $result = $db->update();

        $app = HMS_RLC_Application::getApplicationById($context->get('applicationId'), Term::getSelectedTerm());

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($app->user_id, 28, Current_User::getUsername(), 'Application Denied');

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Application denied.');
        
        $context->goBack();
    }
}

?>