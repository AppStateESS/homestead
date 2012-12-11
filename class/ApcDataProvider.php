<?php

/**
 * APC Data Provider
 *
 * Provides functionality for getting/fetching student data from the APC.
 *
 * @author jbooker
 * @package hms
 */

class ApcDataProvider extends StudentDataProvider {

    private function makeCacheKey($bannerId, $term)
    {
        return $bannerId . $term;
    }

    /**
     * Returns a key to use for looking up a banner ID by username.
     */
    private function makeUsernameKey($username, $term)
    {
        return $username . $term;
    }

    public function getStudentByUsername($username, $term)
    {

        // Since we use BannerID to store the actual student object, check for a key holding username => banner id
        $bannerId = apc_fetch($this->makeCacheKey($username, $term));
        if($bannerId !== FALSE){
           // If that key existed, then it should hold the Banner ID, so do a second lookup with the banner id
           $key = $this->makeCacheKey($bannerId, $term);
           $student = apc_fetch($key);
           if($student !== FALSE){
               $student->setDataSource(get_class($this));
               return $student;
           }
        }

        // If we didn't find a cahce hit already, it means one key or the other wasn't in the cache, so fallback to next cache level
        $provider = $this->getFallbackProvider();
        $result   = $provider->getStudentByUsername($username, $term);

        $this->refreshCache($result,$term);

        return $result;
    }

    public function getStudentById($bannerId, $term)
    {
        $key = $this->makeCacheKey($bannerId, $term);
        $student = apc_fetch($key);
        if($student !== FALSE){
            $student->setDataSource(get_class($this));
            return $student;
        }

        $provider = $this->getFallbackProvider();
        $result   = $provider->getStudentById($bannerId, $term);

        $this->refreshCache($result,$term);

        return $result;
    }

    public function refreshCache(Student $student, $term)
    {
        // Double check the TTL... Caching something in APC with a ttl of zero means it will be stored forever
        // HMS takes a TTL of zero to mean "don't cache it at all"
        if($this->ttl > 0){
            // Store the actual user object
            $key = $this->makeCacheKey($student->getBannerId(), $term);
            apc_store($key, $student, $this->ttl);

            // Store a secondary key for username->BannerId lookup
            $key = $student->getUsername() . $term;
            apc_store($key, $student->getBannerId(), $this->ttl);
        }
    }

    public function clearCache()
    {
        apc_clear_cache("user");
    }
}
