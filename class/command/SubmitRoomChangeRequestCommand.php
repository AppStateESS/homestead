<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');

class SubmitRoomChangeRequestCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRoomChangeRequest');
    }

    public function execute(CommandContext $context){
        // Cmd to redirect to when we're done or upon error.
        $cmd = CommandFactory::getCommand('StudentRoomChange');

        $cellNum = $context->get('cell_num');
        $optOut  = $context->get('cell_opt_out');

        // Check that a cell phone number was provided, or that the opt-out box was checked.
        if((!isset($cellNum) || empty($cellNum)) && !isset($optOut)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell phone number or check the box indicating you do not wish to provide it.');
            $cmd->redirect();
        }

        $reason = $context->get('reason');

        // Make sure a 'reason' was provided.
        if(!isset($reason) || empty($reason)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a breif explaniation of why you are requesting a room change.');
            $cmd->redirect();
        }

        //TODO: hall preferences?

        $request = RoomChangeRequest::getNew();
        $request->username = UserStatus::getUsername();
        $request->cell_number = $context->get('cell_num');
        $request->reason = $context->get('reason');
        $request->change(new PendingRoomChangeRequest);
        $request->save();

        $cmd->redirect();
    }
}
?>
