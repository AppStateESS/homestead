<?php

/**
 * Singleton Factory class for creating PDO objects based on
 * PHPWS database configuration. Somewhat of a wrapper
 * class for our current situation.
 * 
 * @author jbooker
 * @package homestead
 */
class PdoFactory {
    
    static $factory;
    
    private $pdo;
    
    /**
     * Returns a PdoFactory object 
     * @return $pdo An instance of PdoFactory
     */
    public static function getInstance()
    {
        if (!isset(self::$factory)) {
            self::$factory = new PdoFactory(); 
        }
        
        return self::$factory;
    }
   
    /**
     * Returns a PDO object, connected to the database
     * @return $pdo A PDO instance, connected to the current DB
     */
    public static function getPdoInstance()
    {
        $pdoFactory = self::getInstance();

        return $pdoFactory->getPdo();
    }

    
    private function __construct()
    {
        $phpwsDSN = PHPWS_DSN;
        
        if (!isset($phpwsDSN)) {
            throw new Exception('Database connection DSN is not set.');
        }
        
        $dbTypeSplit = explode('://', $phpwsDSN);
        $dbType = $dbTypeSplit[0];
        $userSplit = explode(':', $dbTypeSplit[1]);
        $username = $userSplit[0];
        $passwordSplit = explode('@', $userSplit[1]);
        $password = $passwordSplit[0];
        $hostSplit = explode('/', $passwordSplit[1]);
        $host = $hostSplit[0];
        $dbName = $hostSplit[1];
        
        $dsn = $this->createDsn($dbType, $host, $dbName);
        
        $this->pdo = new PDO($dsn, $username, $password, array(PDO::ATTR_PERSISTENT => true));
    }
    
    public function getPdo()
    {
        return $this->pdo;
    }
    
    private function createDsn($dbType, $host, $dbName)
    {
        return "$dbType:" . ($host != '' ? "host=$host" : '') . ";dbname=$dbName";
    }
}

?>
