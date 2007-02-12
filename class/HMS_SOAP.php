<?php

class HMS_SOAP{
    function is_valid_student($username)
    {
        include('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $student = $proxy->GetStudentProfile($username, '200740');
        if($student->last_name == '') {
            return false;
        } else {
            return true;
        }
    }
}

?>
