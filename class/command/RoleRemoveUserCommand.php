<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'HMS_Role.php');

class RoleRemoveUserCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $user_id = $context->get('user_id');
        $role_id = $context->get('role');
        if(is_null($user_id) || is_null($role_id)){
            echo json_encode(false);
            exit;
        }

        $role = new HMS_Role();
        $role->id = $role_id;
        if($role->load()){
            echo json_encode($role->removeUser($user_id));
            exit;
        }
        echo json_encode(false);
        exit;
    }
}

?>
