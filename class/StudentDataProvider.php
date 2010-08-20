<?php

abstract class StudentDataProvider {

    // For singleton pattern
    public static $instance;

    // Default settings for concrete instances
    protected static $defaultTtl = 86400;

    // Member variables
    protected $fallbackProvider;
    protected $ttl;

    private function __construct(StudentDataProvider $fallbackProvider = NULL, $ttl = NULL){

        if(is_null($ttl)){
            $this->ttl = self::$defaultTtl;
        }else{
            $this->ttl = $ttl;
        }

        $this->fallbackProvider = $fallbackProvider;
    }

    public static function getInstance()
    {
        if(!is_null(self::$instance)){
            return self::$instance;
        }

        PHPWS_Core::initModClass('hms', 'SOAPDataProvider.php');
        PHPWS_Core::initModClass('hms', 'LocalCacheDataProvider.php');
        self::$instance = new LocalCacheDataProvider(new SOAPDataProvider(), 120);

        return self::$instance;
    }

    abstract public function getStudentByUsername($username, $term);

    abstract public function getStudentById($id, $term);

    /**
     * Clears any cached results this DataProvider may be storing
     */
    abstract public function clearCache();


    protected function getFallbackProvider()
    {
        if(!isset($this->fallbackProvider) || is_null($this->fallbackProvider)){
            // No fallback provider is set, so throw an exception
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException();
        }else{
            return $this->fallbackProvider;
        }
    }
}
?>