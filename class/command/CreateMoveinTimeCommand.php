<?php
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class CreateMoveinTimeCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'CreateMoveinTime');
    }

    public function execute(CommandContext $context){
        $cmd   = CommandFactory::getCommand('ShowMoveinTimesView');
        $begin = mktime($context->get('begin_hour'), 0, 0, $context->get('begin_month'), $context->get('begin_day'), $context->get('begin_year'));
        $end   = mktime($context->get('end_hour'), 0, 0, $context->get('end_month'), $context->get('end_day'), $context->get('end_year'));

        if(is_null($begin) || is_null($end) || !is_numeric($begin) || !is_numeric($end) || $end < $begin){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid range specified for new movein time.');
            $cmd->redirect();
        }

        $movein_time                  = new HMS_Movein_Time();
        $movein_time->begin_timestamp = $begin;
        $movein_time->end_timestamp   = $end;
        $movein_time->term            = Term::getSelectedTerm();

        $result = $movein_time->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error saving the move-in time.');
        } else {
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Move-in time saved successfully.');
        }

        $cmd->redirect();
    }

}

?>