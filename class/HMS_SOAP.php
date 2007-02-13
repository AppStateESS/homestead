<?php

class HMS_SOAP{
    function is_valid_student($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        if($student->last_name == '') {
            return false;
        } else {
            return true;
        }
    }

    function get_first_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        return $student->first_name;
    }

    function get_middle_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        return $student->middle_name;
    }

    function get_last_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        return $student->last_name;
    }

    function get_gender($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        return $student->gender;
    }

    function get_student_info($username)
    {
        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $student = $proxy->GetStudentProfile($username, '200740');
        return $student;
    }
}

?>
