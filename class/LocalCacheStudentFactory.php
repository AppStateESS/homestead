<?php

PHPWS_Core::initModClass('hms', 'StudentDataFactory.php');
PHPWS_Core::initModClass('hms', 'Student.php');

class LocalCacheStudentFactory extends StudentDataFactory {
    
    public static function getStudentByUsername($username, $term)
    {
        $student = new Student();
        $student->setUsername($username);

        $db = new PHPWS_DB('hms_student_cache');
        
        if(!isset($soapData->banner_id) || is_null($soapData->banner_id) || empty($soapData->banner_id)){
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException('No matching student found.');
        }
        
        StudentFactory::plugSOAPData($student, $soapData);
        
        return $student;
    }
    
    public static function getStudentByBannerId($bannerId, $term)
    {
        $soap     = SOAP::getInstance();
        $username = $soap->getUsername($bannerId);
        
        if(!isset($username) || is_null($username) || empty($username)){
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException('No matching student found.');
        }
        
        return StudentFactory::getStudentByUsername($username, $term);
    }
    
}

?>
