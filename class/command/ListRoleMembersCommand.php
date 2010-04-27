<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

class ListRoleMembersCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $class    = $context->get('type');
        $instance = $context->get('instance');
        
        $class     = new $class;
        $class->id = $instance;
        $members = HMS_Permission::getMembership(null, $class);

        echo json_encode($members);
        exit();
    }
}
?>
