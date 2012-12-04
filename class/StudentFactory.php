<?php

PHPWS_Core::initModClass('hms', 'StudentDataProvider.php');
PHPWS_Core::initModClass('hms', 'Student.php');

class StudentFactory {

    public static function getStudentByUsername($username, $term, $provider = NULL)
    {
        if(is_null($provider)){
            $provider = StudentDataProvider::getInstance();
        }

        return $provider->getStudentByUsername($username, $term);
    }

    public static function getStudentByBannerId($bannerId, $term, $provider = NULL)
    {
        if(is_null($provider)){
            $provider = StudentDataProvider::getInstance();
        }

        $provider = StudentDataProvider::getInstance();
        return $provider->getStudentById($bannerId, $term);
    }
}

?>
