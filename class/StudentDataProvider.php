<?php

abstract class StudentDataProvider {

    abstract function getStudentByUsername($username, $term, $ttl);


    abstract function getStudentById($username, $term, $ttl);

}

?>