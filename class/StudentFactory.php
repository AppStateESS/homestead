<?php

namespace Homestead;

class StudentFactory {

    /**
     * @param string $username
     * @param integer $term
     * @param StudentDataProvider $provider Could be either ApcDataProvider or LocalCacheDataProvider
     * @return CachedStudent
     */
    public static function getStudentByUsername($username, $term, $provider = NULL)
    {
        if(is_null($provider)){
            $provider = StudentDataProvider::getInstance();
        }

        return $provider->getStudentByUsername($username, $term);
    }

    /**
     *
     * @param string $bannerID
     * @param integer $term
     * @param ApcDataProvider|LocalCacheDataProvider $provider
     * @return Student
     */
    public static function getStudentByBannerID($bannerID, $term, $provider = NULL)
    {
        if(is_null($provider)){
            $provider = StudentDataProvider::getInstance();
        }

        return $provider->getStudentById($bannerID, $term);
    }
}
