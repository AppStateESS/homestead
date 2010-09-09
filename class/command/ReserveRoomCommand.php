<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

class ReserveRoomCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'=>'ReserveRoom');
    }

    public function execute(CommandContext $context){
        $bed = $context->get('bed');
        $bed = new HMS_Bed($bed);
        $bed->loadAssignment();

        if(!is_null($context->get('clear'))){
            $status = is_null($context->get('clear')) ? 1 : 0;
            $bed->room_change_reserved = $status;
            $bed->save();

            if($status == 1){
                NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The bed has been reserved!');
            } else {
                NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The reserved flag has been cleared!');
            }
            return;
        }

        if($bed->_curr_assignment instanceof HMS_Assignment || is_null($context->get('clear')) && $bed->room_change_reserved != 0) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'That bed is unavailable!');
            $cmd = CommandFactory::getCommand($context->get('last_command'));
            return $cmd;
        }
    }
}

?>