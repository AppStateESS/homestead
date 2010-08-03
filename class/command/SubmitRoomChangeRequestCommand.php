<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');

class SubmitRoomChangeRequestCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRoomChangeRequest');
    }

    public function execute(CommandContext $context){
        $request = RoomChangeRequest::getNew();
        $request->username = UserStatus::getUsername();
        $request->cell_number = $context->get('cell_num');  //opt out is kinda silly when it's nullable anyway...
        $request->reason = $context->get('reason');
        $request->change(new PendingRoomChangeRequest);
        $request->save();

        $cmd = CommandFactory::getCommand('StudentRoomChange');
        $cmd->redirect();
    }
}
?>
