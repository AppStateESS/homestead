<?php

require_once('SOAPException.php');
require_once('BannerException.php');
require_once('StudentNotFoundException.php');

class SOAP
{
    private $client; // SOAP client object

    private $currentUser = 'jb67803';
    private $userType = 'A';

    //private $currentUser = 'bickersss';
    //private $userType = 'S';

    public function __construct()
    {
        ini_set('soap.wsdl_cache_enabled', 0);
        $this->client = new SoapClient('http://bansrvtest.its.appstate.edu:8081/shs0001.asmx?WSDL', array('trace'=>true));
    }

    public function getStudentProfile($bannerId, $term)
    {

        // Sanity checking on Banner Id
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new InvalidArgumentException('Missing Banner Id.');
        }

        // Sanity checking on the term
        if(empty($term) || is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Missing term.');
        }

        $params = array('User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'UserType'  => $this->userType);

        //test($params,1);

        try{
            $response = $this->client->GetStudentProfile($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getStudentProfile', $params);
            return false;
        }

        return $response->profile;
    }

    public function getUsername($bannerId)
    {
        // Sanity checking on Banner Id
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new InvalidArgumentException('Missing Banner Id.');
        }

        $params = array('User'=>$this->currentUser,
                        'BannerID'=>$bannerId,
                        'UserType'  => $this->userType);

        try{
            $response = $this->client->getUserName($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getUsername', $params);
            return false;
        }

        if(!isset($response->GetUserNameResult)){
            //throw new StudentNotFoundException("No matching student found with Banner ID: $bannerId.");
            return false;
        }

        return $response->GetUserNameResult;
    }

    public function getBannerId($username)
    {
        if(empty($username) || is_null($username) || !isset($username)){
            throw new InvalidArgumentException('Missing username');
        }

        $params = array('User'      => $this->currentUser,
                        'UserName'  => $username,
                        'UserType'  => $this->userType);

        try{
            $response = $this->client->GetBannerID($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getUsername', $params);
            return false;
        }


        if(!is_numeric($response->GetBannerIDResult)){
            throw new BannerException($response->GetBannerIDResult, null, 'getBannerId', $params);
        }

        return $response->GetBannerIDResult;
    }

    // TODO Update this or get rid of it
    public function isValidStudent($username, $term)
    {
        // Sanity checking on the Banner Id
        /*
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new InvalidArgumentException('Missing Banner Id.');
        }*/

        // Sanity checking on the term
        if(empty($term) || is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Missing term.');
        }

        $bannerId = $this->getBannerId($username);

        $params = array('User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'UserType'  => $this->userType);

        try{
            $response = $this->client->GetStudentProfile($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'isValidStudent', $params);
            return false;
        }

        if(isset($response->profile->banner_id)){
            return true;
        }else{
            return false;
        }
    }

    public function hasParentPin($bannerId)
    {
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new InvalidArgumentException('Missing Banner Id.');
        }

        $params = array('User'       => $this->currentUser,
                        'BannerID'   => $bannerId);

        try {
            $response = $this->client->HasParentPin($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'isValidStudent', $params);
            return false;
        }

        // If the response is empty, or has a numeric result, then there must have been some error
        if(!isset($response->HasParentPinResult) || is_numeric($response->HasParentPinResult)){
            throw new BannerException("Error while checking for parent PIN: {$response->HasParentPinResult}");
        }

        // Expecting a 'Y' or an 'N' as valid response values. Anything else is an exception.
        if($response->HasParentPinResult == 'Y'){
            return true;
        }else if($response->HasParentPinResult == 'N'){
            return false;
        }else {
            throw new BannerException("Unexpected result while checking for parent PIN: {$response->HasParentPinResult}");
            return false;
        }
    }

    public function getParentAccess($bannerId, $parentPin)
    {
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new InvalidArgumentException('Missing Banner Id.');
        }

        if(empty($parentPin) || is_null($parentPin) || !isset($parentPin)){
            throw new InvalidArgumentException('Missing parent PIN.');
        }

        $params = array('User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'ParentPin' => $parentPin);

        try {
            $response = $this->client->getParentAccess($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getParentAccess', $params);
            return false;
        }

        return $response->GetParentAccessResult;
    }

    public function createHousingApp($bannerId, $term)
    {
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new InvalidArgumentException('Missing BannerID');
        }

        if(empty($term) || is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Missing term.');
        }

        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'PlanCode'  => 'HOME', // Hard-coded, magic numbers... but we really should need to pass these
                        'MealCode'  => 1, // same here
                        'UserType'  => $this->userType);

        try{
            $response = $this->client->CreateHousingApp($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'createHousingApp', $params);
            return false;
        }

        // Check for a Banner error code
        if($response->CreateHousingAppResult != "0"){
            throw new BannerException('Error while reporting application to Banner.', $response->CreateHousingAppResult, 'reportApplicationReceived', $params);
            return false;
        }

        return true;
    }

    public function createRoomAssignment($bannerId, $term, $building, $bannerBedId, $plan = 'HOME', $meal)
    {
        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'BldgCode'  => $building,
                        'RoomCode'  => $bannerBedId,
                        'PlanCode'  => $plan,
                        'MealCode'  => $meal,
                        'UserType'  => $this->userType);
        try{
            $response = $this->client->CreateRoomAssignment($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'createRoomAssignment', $params);
            return false;
        }

        if($response->CreateRoomAssignmentResult != "0"){
            throw new BannerException('Error while reporting assignment to Banner.', $response->CreateRoomAssignmentResult, 'createRoomAssignment', $params);
            return FALSE;
        }

        return true;
    }

    /**
     * Create a room assignment in Banner. Really just a wrapper for createRoomAssignment() now.
     * @deprecated
     * @see createRoomAssignment()
     */
    public function reportRoomAssignment($username, $term, $building, $room, $plan = 'HOME', $meal)
    {
        $bannerId = $this->getBannerId($username);
        return $this->createRoomAssignment($bannerId, $term, $building, $room, $plan, $meal);
    }

    public function removeRoomAssignment($bannerId, $term, $building, $bannerBedId)
    {
        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'BldgCode'  => $building,
                        'RoomCode'  => $bannerBedId);

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

        return TRUE;
    }

    public function getHousMealRegister($bannerId, $term, $opt)
    {
        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'Option'    => $opt);

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
                        'User'      => $this->currentUser,
                        'BldgCode'  => $building,
                        'RoomCode'  => $room,
                        'TermCode'  => $term);

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

    public function setHousingWaiver($bannerId, $term)
    {
        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term);

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

    public function clearHousingWaiver($bannerId, $term)
    {
        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term);

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
