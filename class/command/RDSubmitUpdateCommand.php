<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');

class RDSubmitUpdateCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'RDSubmitUpdate');
    }

    public function execute(CommandContext $context){
        $rc = new RoomChangeRequest;
        $rc = $rc->search(UserStatus::getUsername());

        if(is_null($context->get('approve_deny'))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must either approve or deny the request!');
        }

        $approve = $context->get('approve_deny') == 'approve' ? true : false;

        if($approve){
            $rc->change(new RDApprovedChangeRequest);
        } else {
            $rc->change(new DeniedChangeRequest);
            $rc->denied_reason = $context->get('reason');
        }

        $rc->save();

        $cmd = CommandFactory::getCommand('RDRoomChange');
        $cmd->redirect();
    }
}
?>