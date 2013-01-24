<?php

/**
 * RecentStudentSearchList
 * Implements a model to hold recent student profile searches.
 *
 * @author Jeremy Booker
 * @package HMS
 */

class RecentStudentSearchList {

    private static $instance;
    const KEY_NAME = 'StudentSearchList';
    const CACHE_TTL = 28800; // seconds to keep cache for (28,800 => 8 hrs)

    private $searchList;
    private $globalSearchList;

    private function __construct()
    {
        // Check if APC is available
        if(!extension_loaded('apc')){
            $this->searchList = array();
            $this->globalSearchList = array();
            return;
        }
         
        // Load the global list from cache, if it exists
        if(apc_fetch(self::KEY_NAME) !== FALSE){
            // Make sure we've loaded the Student class first
            PHPWS_Core::initModClass('hms', 'Student.php');
            PHPWS_Core::initModClass('hms', 'CachedStudent.php');
            $this->globalSearchList = apc_fetch(self::KEY_NAME);
        }else{
            $this->globalSearchList = array();
        }

        // Load the user unique search list
        if(apc_fetch(self::KEY_NAME . UserStatus::getUsername()) !== FALSE){
            // Make sure we've loaded the Student class first
            PHPWS_Core::initModClass('hms', 'Student.php');
            PHPWS_Core::initModClass('hms', 'CachedStudent.php');
            $this->searchList = apc_fetch(self::KEY_NAME . UserStatus::getUsername());
        }else{
            $this->searchList = array();
        }
    }

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new RecentStudentSearchList();
        }

        return self::$instance;
    }

    /**
     * Adds a search to the list.
     *
     * @param Student $student
     * @return void
     */
    public function add(Student $student)
    {
        if(!extension_loaded('apc')){
            return;
        }
         
        // Sanity checking on params
        if(!isset($student)){
            throw new InvalidArgumentException('Missing student object');
        }

        // Check if APC is available
        if(!extension_loaded('apc')){
            return;
        }

        /*****
         * User's List
        */

        // Search the array to see if this item already exists. If it does,
        // remove it and then re-index the array.
        $key = array_search($student, $this->searchList);
        if($key !== FALSE){
            // remove it from the array
            unset($this->searchList[$key]);
            // re-index the array
            $this->searchList = array_values($this->searchList);
        }

        // Add this item to the top of the list
        array_unshift($this->searchList, $student);

        // Save this list to the cache
        apc_store(self::KEY_NAME . UserStatus::getUsername(), $this->searchList);

        /*****
         * Global List
        */
        $key = array_search($student, $this->globalSearchList);
        if($key !== FALSE){
            // remove it from the array
            unset($this->globalSearchList[$key]);
            // re-index the array
            $this->globalSearchList = array_values($this->globalSearchList);
        }

        // Add this item to the top of the list
        array_unshift($this->globalSearchList, $student);

        // Save this list to the cache
        apc_store(self::KEY_NAME, $this->globalSearchList);
    }

    /**
     * Returns the search list
     */
    public function getList()
    {
        if(!extension_loaded('apc')){
            return array();
        }
         
        return $this->searchList;
    }

    public function getGlobalList()
    {
        if(!extension_loaded('apc')){
            return array();
        }
        return $this->globalSearchList;
    }
}

?>
