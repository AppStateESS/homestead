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

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id', $context->get('applicationId'));
        $db->addValue('denied', 0);

        $result = $db->update();

        $result = $db->select('row');
        
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($result['user_id'], 29, UserStatus::getUsername(), "Application un-denied");

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Application un-denied.');

        $successCmd = CommandFactory::getCommand('ShowDeniedRlcApplicants');
        $successCmd->redirect();
    }
}

?>