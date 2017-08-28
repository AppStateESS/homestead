<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

class ListRoleMembersCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        if(!\Current_User::allow('hms', 'view_role_members')){
            //PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            //throw new PermissionException('You do not have permission to view role members.');

            // Can't throw an exception here, since there's nothing to catch it and this is called
            // even when the user doesn't have permissions to do it
            // TODO: fix the interface so this isn't called unless the user has permissions
            // See Trac #664

            echo '';
            exit();
        }

        $class    = $context->get('type');
        $instance = $context->get('instance');

        $class     = new $class;
        $class->id = $instance;
        $hms_perm = new HMS_Permission();
        $members = $hms_perm->getMembership('email', $class, null, true);

        echo json_encode($members);
        exit();
    }
}
