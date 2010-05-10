<?php

abstract class StudentDataFacotry {
    
    public abstract function getStudentByUsername($username, $term);
    
    public abstract function getStudentByBannerId($bannerId, $term);
}

?>