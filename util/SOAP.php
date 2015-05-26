<?php

require_once('SOAPException.php');
require_once('BannerException.php');
require_once('StudentNotFoundException.php');

class PhpSOAP
{
    private $client; // SOAP client object

    public function __construct()
    {
        ini_set('soap.wsdl_cache_enabled', 0);
        $this->client = new SoapClient('file://' . getcwd() . '/shs0001.wsdl', array('trace'=>true));
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
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getStudentInfo', $params);
            return false;
        }

        return $response->profile;
    }

    public function getUsername($bannerId)
    {
        $params = array('BannerID'=>$bannerId);

        try{
            $response = $this->client->getUserName($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getUsername', $params);
            return false;
        }

        if(!isset($response->GetUserNameResult)){
            throw new StudentNotFoundException("Invalid Banner ID: $bannerId");
            return false;
        }

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
            throw new SOAPException($e->getMessage(), $e->getCode(), 'reportApplicationReceived', $params);
            return false;
        }

        if($response->CreateHousingAppResult != "0"){
            throw new BannerException('Error while reporting application to Banner.', $response->CreateHousingAppResult, 'reportApplicationReceived', $params);
            return false;
        }

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
            throw new SOAPException($e->getMessage(), $e->getCode(), 'reportRoomAssignment', $params);
            return false;
        }

        if($response->CreateRoomAssignmentResult != "0"){
            throw new BannerException('Error while reporting assignment to Banner.', $response->CreateRoomAssignmentResult, 'reportRoomAssignment', $params);
            return FALSE;
        }

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
            throw new SOAPException($e->getMessage(), $e->getCode(), 'removeRoomAssignment', $params);
            return false;
        }

        if($response->RemoveRoomAssignmentResult != "0"){
            throw new BannerException('Error while reporting removal to Banner.', $response->RemoveRoomAssignmentResult, 'removeRoomAssignment', $params);
            return false;
        }

        return true;
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
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getHousMealRegister', $params);
            return false;
        }

        return $response->housmeal_register;
    }

    public function getBannerIdByBuildingRoom($building, $room, $term)
    {
        $params = array(
                        'BldgCode'=>$building,
                        'RoomCode'=>$room,
                        'TermCode'=>$term);

        try{
            $response = $this->client->GetBannerIDbyBuildingRoom($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getBannerIdByBuildingRoom', $params);
            return false;
        }

        if(isset($response->GetBannerIDbyBuildingRoomResult)){
            return $response->GetBannerIDbyBuildingRoomResult;
        }else{
            return null;
        }
    }

    public function setHousingWaiver($username, $term)
    {
        $params = array(
                        'StudentID'=>$username,
                        'TermCode'=>$term);

        try{
            $response = $this->client->SetHousingWaiver($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'setHousingWaiver', $params);
            return false;
        }

        if($response->SetHousingWaiverResult != "0"){
            throw new BannerException('Error while setting waiver flag in Banner.', $response->SetHousingWaiverResult, 'setHousingWaiver', $params);
            return false;
        }

        return true;
    }

    public function clearHousingWaiver($username, $term)
    {
        $params = array(
                        'StudentID'=>$username,
                        'TermCode'=>$term);

        try{
            $response = $this->client->ClearHousingWaiver($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'clearHousingWaiver', $params);
            return false;
        }

        if($response->ClearHousingWaiverResult != "0"){
            throw new BannerException('Error while clearing waiver flag in Banner.', $response->ClearHousingWaiverResult, 'clearHousingWaiver', $params);
            return false;
        }

        return true;
    }
}
