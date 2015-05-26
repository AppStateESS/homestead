<?php

$curr_dir  = realpath('.');
$phpws_dir = realpath('../../../');

// HACK!
$_SERVER['HTTP_HOST'] = 'localhost';

chdir($phpws_dir);

include 'config/core/config.php';
require_once 'core/class/Init.php';

PHPWS_Core::initCoreClass('Database.php');
//PHPWS_Core::initCoreClass('Form.php');
//PHPWS_Core::initCoreClass('Template.php');
PHPWS_Core::initModClass('hms', 'Term.php');
PHPWS_Core::initModClass('users', 'Current_User.php');
PHPWS_Core::initModClass('hms', 'StudentDataProvider.php');
PHPWS_Core::initModClass('hms', 'LocalCacheDataProvider.php');
PHPWS_Core::initModClass('hms', 'SOAPDataProvider.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::requireInc('hms', 'defines.php');

//get around visibility problems...
class MyLocalCacheDataProvider extends LocalCacheDataProvider {

    public static function getInstance()
    {
        if(!is_null(self::$instance)){
            return self::$instance;
        }

        PHPWS_Core::initModClass('hms', 'SOAPDataProvider.php');
        PHPWS_Core::initModClass('hms', 'LocalCacheDataProvider.php');
        //set the ttl to zero so it will always update the cache
        self::$instance = new LocalCacheDataProvider(new SOAPDataProvider(), 0);

        return self::$instance;
    }
}

$term     = Term::getSelectedTerm();
$sql      = "select term, username from hms_new_application where term=$term union select term, asu_username as username from hms_assignment where term=$term;";
$db       = new PHPWS_DB();
$results  = $db->select('all', $sql);
$provider = MyLocalCacheDataProvider::getInstance();

foreach($results as $result){
    try{
        //asking for the student updates the cache since the ttl is zero
        $student = StudentFactory::getStudentByUsername($result['username'], $term, $provider);
    } catch(Exception $e){
        echo $e->getMessage()."\n";
    }
}
