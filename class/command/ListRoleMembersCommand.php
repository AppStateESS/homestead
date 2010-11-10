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
        if(!Current_User::allow('hms', 'view_role_members')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view role members.');
        }

        $class    = $context->get('type');
        $instance = $context->get('instance');

        $class     = new $class;
        $class->id = $instance;
        $members = HMS_Permission::getMembership('email', $class, null, true);

        echo json_encode($members);
        exit();
    }
}
?>
