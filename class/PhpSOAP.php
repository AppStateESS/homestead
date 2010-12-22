<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class PhpSOAP extends SOAP
{
    private $client; // SOAP client object

    protected function __construct()
    {
        parent::__construct();
        ini_set('soap.wsdl_cache_enabled', 0);
        $this->client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));
    }

    public function getStudentInfo($username, $term)
    {
        // Sanity checking on the username
        if(empty($username) || is_null($username) || !isset($username)){
            throw new InvalidArgumentException('Bad username');
        }

        // Sanity checking on the term
        if(empty($term) || is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Bad term');
        }

        $params = array('StudentID'=>$username, 'TermCode'=>$term);

        try{
            $response = $this->client->GetStudentProfile($params);
        }catch(SoapFault $e){
            PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getStudentInfo', $params);
            return false;
        }

		SOAP::logSoap('getStudentInfo', 'success', $username, $term);

        return $response->profile;
    }

    public function getUsername($bannerId)
    {
        $params = array('BannerID'=>$bannerId);

        try{
            $response = $this->client->getUserName($params);
        }catch(SoapFault $e){
            PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getUsername', $params);
            return false;
        }

		SOAP::logSoap('getUsername', 'success', $username, $term);

        return $response->GetUserNameResult;
    }

    public function isValidStudent($username, $term)
    {

    }

    public function reportApplicationReceived($username, $term)
    {
        // meal plan code and meal code don't matter here
        $params = array(
                        'StudentID' => $username,
                        'TermCode'  => $term,
                        'PlanCode'  => 'HOME',
                        'MealCode'  => 1);

        try{
            $response = $this->client->CreateHousingApp($params);
        }catch(SoapFault $e){
		    PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
            throw new SOAPException($e->getMessage(), $e->getCode(), 'reportApplicationReceived', $params);
            return false;
        }

        if($response->CreateHousingAppResult != "0"){
		    SOAP::logSoap('reportApplicationReceived', 'failed', $username, $term);
            PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
            throw new BannerException('Error while reporting application to Banner.', $response->CreateHousingAppResult, 'reportApplicationReceived', $params);
            return false;
        }

		SOAP::logSoap('reportApplicationReceived', 'success', $username, $term);
        return true;
    }

    public function reportRoomAssignment($username, $term, $building, $room, $plan = 'HOME', $meal)
    {
        $params = array(
                        'StudentID'=>$username,
                        'TermCode'=>$term,
                        'BldgCode'=>$building,
                        'RoomCode'=>$room,
                        'PlanCode'=>$plan,
                        'MealCode'=>$meal);
        try{
            $response = $this->client->CreateRoomAssignment($params);
        }catch(SoapFault $e){
            PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
            throw new SOAPException($e->getMessage(), $e->getCode(), 'reportRoomAssignment', $params);
            return false;
        }

        if($response->CreateRoomAssignmentResult != "0"){
		    SOAP::logSoap('reportRoomAssignment', 'failed', $username, $term, $building, $room, $meal);
            PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
            throw new BannerException('Error while reporting assignment to Banner.', $response->CreateRoomAssignmentResult, 'reportRoomAssignment', $params);
            return FALSE;
        }

        SOAP::logSoap('reportRoomAssignment', 'success', $username, $term, $building, $room, $meal);
        return true;
    }

    public function removeRoomAssignment($username, $term, $building, $room)
    {
        $params = array(
                        'StudentID'=>$username,
                        'TermCode'=>$term,
                        'BldgCode'=>$building,
                        'RoomCode'=>$room);

        try{
            $response = $this->client->RemoveRoomAssignment($params);
        }catch(SoapFault $e){
            PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
            throw new SOAPException($e->getMessage(), $e->getCode(), 'removeRoomAssignment', $params);
            return false;
        }

        if($response->RemoveRoomAssignmentResult != "0"){
		    SOAP::logSoap('removeRoomAssignment', 'failed (' . $response->RemoveRoomAssignmentResult . ')', $username, $term, $building, $room);
            PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
            throw new BannerException('Error while reporting removal to Banner.', $response->RemoveRoomAssignmentResult, 'removeRoomAssignment', $params);
            return false;
        }

	    SOAP::logSoap('removeRoomAssignment', 'success', $username, $term, $building, $room);
        return TRUE;
    }

    public function getHousMealRegister($username, $term, $opt)
    {
        $params = array(
                        'StudentID'=>$username,
                        'TermCode'=>$term,
                        'Option'=>$opt);

        try{
            $response = $this->client->GetHousMealRegister($params);
        }catch(SoapFault $e){
            PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getHousMealRegister', $params);
            return false;
        }

	    SOAP::logSoap('getHousMealRegister', 'success', $username, $term, $opt);
        return $response->GetHousMealRegister;
    }
}

?>
