<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class PhpSOAP extends SOAP
{
    private $client; // SOAP client object

    protected function __construct()
    {
        parent::__construct();

        $this->client = new SoapClient('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', array('trace'=>true));
    }

    public function getStudentInfo($username, $term)
    {
        $response = $this->client->GetStudentProfile(array('StudentID'=>$username, 'TermCode'=>$term));

		SOAP::logSoap('get_student_info', 'success', $username, $term);

        return $response->profile;
    }

    public function getUsername($bannerId)
    {
        $params = array('BannerID'=>$bannerId);

        $response = $this->client->getUserName($params);

		SOAP::logSoap('get_student_info', 'success', $username, $term);

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

        $response = $this->client->CreateHousingApp($params);

        return $response->CreateHousingAppResult;
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

        $response = $this->client->CreateRoomAssignment($params);

        return $response->CreateRoomAssignmentResult;
    }

    public function removeRoomAssignment($username, $term, $building, $room)
    {
        $params = array(
                        'StudentID'=>$username,
                        'TermCode'=>$term,
                        'BldgCode'=>$building,
                        'RoomCode'=>$room);

        $response = $this->client->RemoveRoomAssignment($params);

        return $response->RemoveRoomAssignmentResult;
    }

    public function getHousMealRegister($username, $termcode, $opt)
    {
        $params = array(
                        'StudentID'=>$username,
                        'TermCode'=>$term,
                        'Option'=>$opt);

        $response = $this->client->GetHousMealRegister($params);
        
        return $response->GetHousMealRegister;
    }
}

?>
