<?php
PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_Permission extends HMS_Item {
    public $id;
    public $name;
    public $full_name;

    public function __construct($name='', $full_name=''){
        $this->name = $name;
        $this->full_name = $full_name;
    }

    public static function getDb(){
        return new PHPWS_DB('hms_permission');
    }

    public static function getMembership($permission=null, $object=null, $username=null, $display_name=false){
        $db = self::getDb();
        $db->addJoin('left outer', 'hms_permission', 'hms_role_perm', 'id', 'permission');
        $db->addJoin('left outer', 'hms_role_perm', 'hms_role', 'role', 'id');
        $db->addJoin('left outer', 'hms_role', 'hms_user_role', 'id', 'role');
        $db->addJoin('left outer', 'hms_user_role', 'users', 'user_id', 'id');

        if(!is_null($permission)){
            $db->addWhere('hms_permission.name', $permission);
        }

        if(!is_null($username)){
            $db->addWhere('users.username', $username);
        } else {
            $db->addWhere('users.username', NULL, '!=');
        }

        if(!is_null($object)){
            $db->addWhere('hms_user_role.class', strtolower(get_class($object)));
            $db->addWhere('hms_user_role.instance', $object->getId());
        }

        $db->addColumn('users.username');

        if($display_name){
            $db->addColumn('users.display_name');
        }

        $db->addColumn('hms_permission.name', null, 'permission');
        $db->addColumn('hms_user_role.class');
        $db->addColumn('hms_user_role.instance');
        $db->addColumn('hms_role.name');
        $db->addColumn('hms_role.id', null, 'role_id');

        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public static function getUserRolesForInstance($instance)
    {
        $db = new PHPWS_DB('hms_user_role');

        $db->addWhere('hms_user_role.class', strtolower(get_class($instance)));
        $db->addWhere('hms_user_role.instance', $instance->id);

        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public static function getUsersInRoleForInstance($roleName, $instance)
    {
    	PHPWS_Core::initModClass('hms', 'PdoFactory.php');

        $pdo = PdoFactory::getPdoInstance();

        $query = "SELECT user_id FROM hms_user_role JOIN hms_role ON hms_user_role.role = hms_role.id WHERE hms_role.name = :roleName AND class = :className AND instance = :instanceId";

        $stmt = $pdo->prepare($query);

        $params = array('roleName' => $roleName,
                        'className' => strtolower(get_class($instance)),
                        'instanceId' => $instance->getId()
                        );
        $stmt->execute($params);

        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 'user_id');

        if(sizeof($userIds) <= 0){
        	return null;
        }

        $users = array();
        foreach ($userIds as $id) {
        	$users[] = new PHPWS_User($id);
        }

        return $users;
    }

    public function verify($username, $object, $otherPermission=null){
        if(!isset($object->id) || is_null($object->id) || !is_numeric($object->id) || is_null($username)){
            return false;
        }

        try{
            $result = $this->getMembership(is_null($otherPermission) ? $this->name : $otherPermission, $object, $username);
        }catch(DatabaseException $e){
            return false;
        }

        return sizeof($result) > 0;
    }
}
