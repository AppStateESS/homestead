<?php
PHPWS_Core::initModClass('hms', 'HMS_Item.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

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
    public function addUser($user_id, $classname, $instance=null){
        $db = new PHPWS_DB('hms_user_role');
        $db->addValue('user_id', $user_id);
        $db->addValue('role', $this->id);
        $db->addValue('class', strtolower($classname));
        $db->addValue('instance', $instance);
        $result = $db->insert();

        if(PHPWS_Error::logIfError($result)){
            $db->addWhere('user_id', $user_id);
            $db->addWhere('role', $this->id);
            return !PHPWS_Error::logIfError($db->update());
        }

        return true;
    }

    public function removeUser($user_id){
        $db = new PHPWS_DB('hms_user_role');
        $db->addValue('user_id', $user_id);
        $db->addValue('role', $this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }
}

?>
