<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class PhpSOAP extends SOAP
{

    public function getStudentInfo($username, $term)
    {
        $client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));

        $response = $client->GetStudentProfile(array('StudentID'=>'jb67803', 'TermCode'=>'201040'));

		SOAP::logSoap('get_student_info', 'success', $username, $term);

        return $response->profile;
    }

    public function getUsername($bannerId)
    {
        $client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));
        
        $params = array('BannerID'=>$bannerId);

		SOAP::logSoap('get_student_info', 'success', $username, $term);

        $response = $client->getUserName($params);
    }

    public function isValidStudent($username, $term)
    {
        $client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));

    }

    public function reportApplicationReceived($username, $term)
    {
        $client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));

    }

    public function reportRoomAssignment($username, $term, $building_code, $room_code, $plan_code = 'HOME', $meal_code)
    {
        $client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));

    }

    public function removeRoomAssignment($username, $term, $building, $room)
    {
        $client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));

    }

    public function getHousMealRegister($username, $termcode, $opt)
    {
        $client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));

    }

}

?>
