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

        $first  = $context->get('first_choice');
        $second = $context->get('second_choice');

        // Check that a cell phone number was provided, or that the opt-out box was checked.
        if((!isset($cellNum) || empty($cellNum)) && !isset($optOut)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell phone number or check the box indicating you do not wish to provide it.');
            $cmd->redirect();
        }

        $reason = $context->get('reason');

        // Make sure a 'reason' was provided.
        if(!isset($reason) || empty($reason)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a brief explaniation of why you are requesting a room change.');
            $cmd->redirect();
        }

        $request = RoomChangeRequest::getNew();
        $request->username = UserStatus::getUsername();
        $request->cell_phone = $context->get('cell_num');
        $request->reason = $context->get('reason');
        $request->change(new PendingRoomChangeRequest);
        if(!empty($first))
            $request->addPreference($first);
        if(!empty($second))
            $request->addPreference($second);

        $request->save();

        $cmd->redirect();
    }
}
?>
