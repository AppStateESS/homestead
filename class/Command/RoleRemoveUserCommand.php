<?php

namespace Homestead\Command;

use \Homestead\HMS_Role;
use \Homestead\Exception\PermissionException;

class RoleRemoveUserCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        if(!\Current_User::allow('hms', 'edit_role_members')){
            throw new PermissionException('You do not have permission to edit role members.');
        }

        $username = $context->get('username');
        $rolename = $context->get('role');
        $class    = $context->get('className');
        $instance = $context->get('instance');

        if(is_null($username) || is_null($rolename)){
            echo json_encode(false);
            exit;
        }

        $db = new \PHPWS_DB('hms_role');
        $db->addWhere('name', $rolename);
        $result = $db->select('row');

        if(\PHPWS_Error::logIfError($result) || is_null($result['id'])){
            echo json_encode(false);
            exit;
        }

        $role_id = $result['id'];

        $role = new HMS_Role();
        $role->id = $role_id;
        if($role->load()){
            echo json_encode($role->removeUser($username, $class, $instance));
            exit;
        }
        echo json_encode(false);
        exit;
    }
}
