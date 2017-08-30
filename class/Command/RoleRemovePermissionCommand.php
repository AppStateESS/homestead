<?php

namespace Homestead\Command;

use \Homestead\HMS_Role;
use \Homestead\HMS_Permission;

class RoleRemovePermissionCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $role_id = $context->get('role');
        $perm_id = $context->get('permission');

        if(is_null($role_id) || is_null($perm_id)){
            echo json_encode(false);
            exit;
        }

        $role = new HMS_Role();
        $role->id = $role_id;
        $perm = new HMS_Permission();
        $perm->id = $perm_id;

        if($role->load() && $perm->load()){
            echo json_encode($role->removePermission($perm));
            exit;
        }
        echo json_encode(false);
        exit;
    }
}
