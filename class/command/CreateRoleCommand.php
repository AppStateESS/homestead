<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'HMS_Role.php');

class CreateRoleCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $name = $context->get('name');
        if(is_null($name)){
            echo json_encode(false);
            exit;
        }

        $role = new HMS_Role($name);
        $role->save();
        echo json_encode($role->id);
        exit;
    }
}

?>
