<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'RoomChangeView.php');

class RDRoomChangeCommand extends Command {
    public $username;

    public function getRequestVars(){
        $vars = array('action'=>'RDRoomChange');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    public function execute(CommandContext $context){
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