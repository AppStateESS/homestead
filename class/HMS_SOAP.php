<?php

class HMS_SOAP{
    function is_valid_student($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            PHPWS_Error::log($student,'hms','is_valid_student',$username);
            return $student;
        }
        
        if($student->last_name == NULL) {
            return false;
        } else {
            return true;
        }
    }

    function get_first_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        
        if(PEAR::isError($student)){
            PHPWS_Error::log($student,'hms','get_first_name',$username);
            return $student;
        }else if($student->first_name == NULL){
            return NULL;
        }else{
            return $student->first_name;
        }
    }

    function get_middle_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            PHPWS_Error::log($student,'hms','get_middle_name',$username);
            return $student;
        }else if($student->middle_name == NULL){
            return NULL;
        }else{
            return $student->middle_name;
        }
    }

    function get_last_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            PHPWS_Error::log($student,'hms','get_last_lame',$username);
            return $student;
        }else if($student->last_name == NULL){
            return NULL;
        }else{
            return $student->last_name;
        }
    }

    function get_gender($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            PHPWS_Error::log($student,'hms','get_gender',$username);
            return $student;
        }else if($student->gender == NULL){
            return NULL;
        }else{
            return $student->gender;
        }
    }

    function get_address($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            PHPWS_Error::log($student,'hms','get_address',$username);
            return $student;
        }else if($student->address == NULL){
            return NULL;
        }else{
            return $student->address;
        }
    }

    function get_student_type($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        if(PEAR::isError($student)) {
            PHPWS_Error::log($student, 'hms', 'get_student_type', $username);
            return $student;
        }else if($student->student_type == NULL){
            return NULL;
        }else{
            return $student->student_type;
        }
    }

    function get_dob($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        if(PEAR::isError($student)) {
            PHPWS_Error::log($student, 'hms', 'get_student_type', $username);
            return $student;
        }else if($student->dob == NULL){
            return NULL;
        }else{
            return $student->dob;
        }
    }

    function get_student_info($username)
    {
        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $student = $proxy->GetStudentProfile($username, '200740');
        
        # Check for an error and log it
        if(PEAR::isError($student)){
            PHPWS_Error::log($student,'hms','get_student_info',$username); 
        }
        return $student;
    }
}

?>
