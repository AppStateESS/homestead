<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'RoomChangeView.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

class RDRoomChangeCommand extends Command {
    public $username;
    public $_memberships = array();

    public function getRequestVars(){
        $vars = array('action'=>'RDRoomChange');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    public function execute(CommandContext $context){
        $memberships = HMS_Permission::getMembership('room_change_approve', NULL, UserStatus::getUsername());

        if(empty($memberships)){
            throw new PermissionException("You can't do that");
        }

        $this->_memberships = $memberships;

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