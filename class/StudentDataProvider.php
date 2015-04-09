<?php

abstract class StudentDataProvider {

    // For singleton pattern
    public static $instance;

    // Default settings for concrete instances
    protected static $defaultTtl = 86400;

    // Member variables
    protected $fallbackProvider;
    protected $ttl;

    protected function __construct(StudentDataProvider $fallbackProvider = NULL, $ttl = NULL)
    {

        if (is_null($ttl)) {
            $this->ttl = self::$defaultTtl;
        } else {
            $this->ttl = $ttl;
        }

        $this->fallbackProvider = $fallbackProvider;
    }

    public static function getInstance()
    {
        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        PHPWS_Core::initModClass('hms', 'ApcDataProvider.php');
        PHPWS_Core::initModClass('hms', 'SOAPDataProvider.php');
        PHPWS_Core::initModClass('hms', 'LocalCacheDataProvider.php');

        if (extension_loaded('apc')) {
            self::$instance = new ApcDataProvider(new LocalCacheDataProvider(new SOAPDataProvider()));
        } else {
            self::$instance = new LocalCacheDataProvider(new SOAPDataProvider());
        }

        return self::$instance;
    }

    abstract public function getStudentByUsername($username, $term);

    abstract public function getStudentByID($id, $term);

    /**
     * Clears any cached results this DataProvider may be storing
     */
    abstract public function clearCache();

    /**
     * Clears all of the various types of caching.
     */
    public function clearAllCache()
    {
        $instance = self::getInstance();

        // Loop over all the configured data providers,
        // clearing the cache at each level
        while(!is_null($instance)) {
            $instance->clearCache();
            $instance = $instance->fallbackProvider;
        }
    }

    /**
     * Gets the fallback provider for this DataProvider.
     *
     * @return StudentDataProvider
     * @throws StudentNotFoundException
     */
    protected function getFallbackProvider()
    {
        if (!isset($this->fallbackProvider) || is_null($this->fallbackProvider)) {
            // No fallback provider is set, so throw an exception
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException();
        } else {
            return $this->fallbackProvider;
        }
    }
}
?>