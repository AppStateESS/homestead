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

    private function makeCacheKey($username, $term)
    {
        return $username . $term;
    }

    public function getStudentByUsername($username, $term)
    {
        $key = $this->makeCacheKey($username, $term);

        $val = apc_fetch($key);
        if($val !== FALSE){
            return $val;
        }else{
            $provider = $this->getFallbackProvider();
            $result   = $provider->getStudentByUsername($username, $term);

            $this->refreshCache($result,$term);

            return $result;
        }
    }

    public function getStudentById($id, $term)
    {
        // Look for a key using the banner id, which should be holding the username
        $key = $id . $term;

        $username = apc_featch($key);

        if($username !== FALSE){
            // If that key existed, then look for a key using the username, which should be holding the student object
            $key = $this->makeCacheKey($username, $term);
            $student = apc_featch($key);
            if($student !== FALSE){
                return $student;
            }
        }

        // If we didn't return already, it means one key or the other wasn't in the cache
        $provider = $this->getFallbackProvider();
        $result   = $provider->getStudentById($id, $term);

        $this->refreshCache($result,$term);

        return $result;
    }

    public function refreshCache(Student $student, $term)
    {
        // Double check the TTL... Caching something in APC with a ttl of zero means it will be stored forever
        // HMS takes a TTL of zero to mean "don't cache it at all"
        if($this->ttl > 0){
            // Store the actual user object
            $key = $this->makeCacheKey($student->getUsername(), $term);
            apc_store($key, $student, $this->ttl);

            // Store a secondary key for bannerID->username lookup
            $key = $student->getBannerId() . $term;
            apc_store($key, $student->getUsername(), $this->ttl);
        }
    }

    public function clearCache()
    {
        apc_clear_cache("user");
    }
}