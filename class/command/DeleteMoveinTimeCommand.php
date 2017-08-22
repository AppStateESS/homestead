<?php

namespace Homestead\command;

use \Homestead\Command;

class DeleteMoveinTimeCommand extends Command {
    protected $id;

    public function getRequestVars(){
        return array('action' => 'DeleteMoveinTime',
                     'id'     => $this->id
                     );
    }

    public function setId($id){
        if(is_numeric($id))
            $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function execute(CommandContext $context){
        $cmd = CommandFactory::getCommand('ShowMoveinTimesView');
        $id = $context->get('id');
        if(is_null($id) || !is_numeric($id)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Invalid id selected for deletion.');
            $cmd->redirect();
        }

        $movein_time = new HMS_Movein_Time();
        $movein_time->id = $id;
        $result = $movein_time->delete();

        if(!$result || \PHPWS_Error::logIfError($result)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Database error while attempting to delete movein time.');
        } else {
            NQ::simple('hms', hms\NotificationView::SUCCESS, 'Movein time deleted successfully.');
        }

        $cmd->redirect();
    }
}
