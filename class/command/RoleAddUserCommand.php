<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'HMS_Role.php');

class RoleAddUserCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $username  = $context->get('username');
        $role_id   = $context->get('role');
        $classname = $context->get('class');
        $instance  = $context->get('instance');
        if(is_null($username) || is_null($role_id)){
            echo json_encode(false);
            exit;
        }

        $role = new HMS_Role();
        $role->id = $role_id;
        if($role->load()){
            echo json_encode($role->addUser($username, $classname, $instance));
            exit;
        }
        echo json_encode(false);
        exit;
    }
}

?>
