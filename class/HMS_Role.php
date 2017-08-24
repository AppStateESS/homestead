<?php

namespace Homestead;

use \Homestead\exception\DatabaseException;
use \PHPWS_Error;
use \PHPWS_DB;

class HMS_Role extends HMS_Item {
    public $id;
    public $name;

    public function getDb(){
        return new PHPWS_DB('hms_role');
    }

    public function __construct($name=''){
        $this->name = $name;
    }

    /*
     * Will return false on subsequent attempts to add a permission to a role.
    * ie. you can only add it once.
    */
    public function addPermission(HMS_Permission $permission){
        $db = new PHPWS_DB('hms_role_perm');
        $db->addValue('role', $this->id);
        $db->addValue('permission', $permission->id);
        $result = $db->insert();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }

    public function removePermission(HMS_Permission $permission){
        $db = new PHPWS_DB('hms_role_perm');
        $db->addWhere('role', $this->id);
        $db->addWhere('permission', $permission->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }

    /*
     * Will return false if the user is already in this role.  User only has
    * access to the object of type $classname with the id $intance (none if
    * null).
    */
    //TODO: Invalid documentation. No where does this function return false. It throws database
    //  exceptions due to duplicate key violations......
    public function addUser($username, $classname, $instance=null){
        $db = new PHPWS_DB('users');
        $db->addWhere('username', $username);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if(is_null($result['id'])){
            throw new \InvalidArgumentException('User name "' . $username . '" does not exist.');
        }

        $user_id = $result['id'];

        $db = new PHPWS_DB('hms_user_role');
        $db->addValue('user_id', $user_id);
        $db->addValue('role', $this->id);
        $db->addValue('class', strtolower($classname));
        $db->addValue('instance', $instance);
        $result = $db->insert();

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function removeUser($username, $classname=null, $instanceId=null){
        $db = new PHPWS_DB('users');
        $db->addWhere('username', $username);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result) || is_null($result['id'])){
            return false;
        }

        $user_id = $result['id'];

        $db = new PHPWS_DB('hms_user_role');
        $db->addWhere('user_id', $user_id);
        $db->addWhere('role', $this->id);
        if(!is_null($classname)){
            $db->addWhere('class', strtolower($classname));
        }
        if(!is_null($instanceId)){
            $db->addWhere('instance', $instanceId);
        }
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return true;
    }
}
