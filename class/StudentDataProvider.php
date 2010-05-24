<?php

abstract class StudentDataProvider {

    private $fallbackProvider;

    function __construct($fallbackProvider = NULL){
        $this->fallbackProvider = $fallbackProvider;
    }

    abstract function getStudentByUsername($username, $term, $ttl);

    abstract function getStudentById($username, $term, $ttl);

    abstract function getFallbackProvider();
}

?>