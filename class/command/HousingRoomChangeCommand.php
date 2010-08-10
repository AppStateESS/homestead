<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'RoomChangeView.php');

class HousingRoomChangeCommand extends Command {
    public $username;
    public $tab;

    public function getRequestVars(){
        $vars = array('action'=>'HousingRoomChange');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        if(isset($this->tab)){
            $vars['tab'] = $this->tab;
        }

        return $vars;
    }

    public function execute(CommandContext $context){
        if(!Current_User::allow('admin_approve_room_change')){
            throw new Exception("I'm sorry, I can't do that Dave.");
        }

        if(!is_null($context->get('username'))){
            $rc = new RoomChangeRequest;
            $rc = $rc->search($context->get('username'));
        } else {
            $rc = NULL;
        }

        $view = new RoomChangeView($this, $rc);

        $context->setContent($view->show());
    }
}
?>