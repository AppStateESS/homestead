<?php
PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Permission extends HMS_Item {
    public $id;
    public $name;
    public $full_name;

    public function getDb(){
        return new PHPWS_DB('hms_permission');
    }

    public function __construct($name='', $full_name=''){
        $this->name = $name;
        $this->full_name = $full_name;
    }

    public function getMembership($permission, $object=null, $username=null){
        $db = $this->getDb();
        $db->addJoin('left outer', 'hms_permission', 'hms_role_perm', 'id', 'permission');
        $db->addJoin('left outer', 'hms_role_perm', 'hms_role', 'role', 'id');
        $db->addJoin('left outer', 'hms_role', 'hms_user_role', 'id', 'role');
        $db->addJoin('left outer', 'hms_user_role', 'users', 'user_id', 'id');
        $db->addWhere('hms_permission.name', $permission);
        if(!is_null($username)){
            $db->addWhere('users.username', $username);
        } else {
            $db->addWhere('users.username', NULL, '!=');
        }
        if(!is_null($object)){
            $db->addWhere('hms_user_role.class', strtolower(get_class($object)));
            $db->addWhere('hms_user_role.instance', $object->id);
        }
        $db->addColumn('users.username');
        $db->addColumn('hms_permission.name', null, 'permission');
        $db->addColumn('hms_user_role.class');
        $db->addColumn('hms_user_role.instance');
        $db->addColumn('hms_role.name');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    public function verify($username, $object, $otherPermission=null){
        if(!isset($object->id) || is_null($object->id) || !is_numeric($object->id) || is_null($username)){
            return false;
        }

        $result = $this->getMembership(is_null($otherPermission) ? $this->name : $otherPermission, $object, $username);

        return sizeof($result) > 0;
    }
}

?>
