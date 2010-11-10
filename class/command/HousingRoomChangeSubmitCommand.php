<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');

class HousingRoomChangeSubmitCommand extends Command {
    public $username;

    public function getRequestVars(){
        $vars = array('action'=>'HousingRoomChangeSubmit');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    public function execute(CommandContext $context){
        $rc = new RoomChangeRequest;
        $rc = $rc->search($context->get('username'));

        if(!is_null($context->get('approve_deny'))){
            $approve = $context->get('approve_deny') == 'approve' ? true : false;
        }

        if(!isset($approve)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must either approve or deny the request!');
        }

        if($approve){
            $rc->change(new HousingApprovedChangeRequest);
        } else {
            $rc->change(new DeniedChangeRequest);
            $rc->denied_reason = $context->get('reason');
        }

        $rc->save();

        $cmd = CommandFactory::getCommand('HousingRoomChange');
        $cmd->redirect();
    }
}
?>