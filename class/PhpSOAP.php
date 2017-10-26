<?php

namespace Homestead;

use \Homestead\Exception\SOAPException;
use \Homestead\Exception\BannerException;
use \Homestead\Exception\StudentNotFoundException;
use \Homestead\Exception\MealPlanExistsException;

/**
 * PhpSOAP Class - Singleton implementation of SOAP class.
 * Implements methods for access to Banner Housing Web Service via SOAP.
 *
 * @author Jeremy Booker
 * @package hms
 */
class PhpSOAP extends SOAP
{
    private $client; // SOAP client object

    /**
     * Constcutor
     *
     * @param string $username Username of the currently logged in user
     * @param string $userType Type of user logged in. Valid values defined as class constants in SOAP.php
     */
    protected function __construct($username, $userType)
    {
        parent::__construct($username,$userType);
        ini_set('soap.wsdl_cache_enabled', 0);
        $this->client = new \SoapClient('file://' . PHPWS_SOURCE_DIR . WSDL_FILE_PATH, array('trace'=>true, 'connection_timeout'=>30));
    }

    public function getStudentProfile($bannerId, $term)
    {

        // Sanity checking on Banner Id
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new \InvalidArgumentException('Missing Banner Id.');
        }

        // Sanity checking on the term
        if(empty($term) || is_null($term) || !isset($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $params = array('User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'UserType'  => $this->userType);

        //test($params,1);

        try{
            $response = $this->client->GetStudentProfile($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getStudentProfile', $params);
        }

        SOAP::logSoap('getStudentProfile', 'success', $params);

        return $response->profile;
    }

    public function getUsername($bannerId)
    {
        // Sanity checking on Banner Id
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new \InvalidArgumentException('Missing Banner Id.');
        }

        $params = array('User'=>$this->currentUser,
                        'BannerID'=>$bannerId,
                        'UserType'  => $this->userType);

        try{
            $response = $this->client->getUserName($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getUsername', $params);
        }

        if(!isset($response->basic_response->value)){
            //throw new StudentNotFoundException("No matching student found with Banner ID: $bannerId.");
            return false;
        }

        SOAP::logSoap('getUsername', 'success', $params);

        return $response->basic_response->value;
    }

    public function getBannerId($username)
    {
        if(empty($username) || is_null($username) || !isset($username)){
            throw new \InvalidArgumentException('Missing username');
        }

        $params = array('User'      => $this->currentUser,
                        'UserName'  => $username,
                        'UserType'  => $this->userType);

        try{
            $response = $this->client->GetBannerID($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getUsername', $params);
        }

        if(!isset($response->basic_response->value)){
            return null;
        }

        if(!is_numeric($response->basic_response->value)){
            //throw new BannerException($response->GetBannerIDResult, null, 'getBannerId', $params);
            return null;
        }

        SOAP::logSoap('getBannerId', 'success', $params);

        return $response->basic_response->value;
    }

    // TODO Update this or get rid of it
    public function isValidStudent($username, $term)
    {
        // Sanity checking on the Banner Id
        /*
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new \InvalidArgumentException('Missing Banner Id.');
        }*/

        // Sanity checking on the term
        if(empty($term) || is_null($term) || !isset($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $bannerId = $this->getBannerId($username);

        $params = array('User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'UserType'  => $this->userType);

        try{
            $response = $this->client->GetStudentProfile($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'isValidStudent', $params);
        }

        SOAP::logSoap('isValidStudent', 'success', $params);

        if(isset($response->profile->banner_id)){
            return true;
        }else{
            return false;
        }
    }

    public function hasParentPin($bannerId)
    {
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new \InvalidArgumentException('Missing Banner Id.');
        }

        $params = array('User'       => $this->currentUser,
                        'BannerID'   => $bannerId);

        try {
            $response = $this->client->HasParentPin($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'isValidStudent', $params);
        }

        // If the response is empty, or has a numeric result, then there must have been some error
        if(!isset($response->HasParentPinResult) || is_numeric($response->HasParentPinResult)){
            throw new BannerException("Error while checking for parent PIN: {$response->HasParentPinResult}");
        }

        SOAP::logSoap('hasParentPin', 'success', $params);

        // Expecting a 'Y' or an 'N' as valid response values. Anything else is an exception.
        if($response->HasParentPinResult == 'Y'){
            return true;
        }else if($response->HasParentPinResult == 'N'){
            return false;
        }else {
            throw new BannerException("Unexpected result while checking for parent PIN: {$response->HasParentPinResult}");
        }
    }

    public function getParentAccess($bannerId, $parentPin)
    {
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new \InvalidArgumentException('Missing Banner Id.');
        }

        if(empty($parentPin) || is_null($parentPin) || !isset($parentPin)){
            throw new \InvalidArgumentException('Missing parent PIN.');
        }

        $params = array('User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'ParentPin' => $parentPin);

        try {
            $response = $this->client->getParentAccess($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getParentAccess', $params);
        }

        SOAP::logSoap('getParentAccess', 'success', $params);

        return $response->GetParentAccessResult;
    }

    public function createHousingApp($bannerId, $term)
    {
        if(empty($bannerId) || is_null($bannerId) || !isset($bannerId)){
            throw new \InvalidArgumentException('Missing BannerID');
        }

        if(empty($term) || is_null($term) || !isset($term)){
            throw new \InvalidArgumentException('Missing term.');
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
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'createHousingApp', $params);
        }

        // Check for a Banner error code
        if($response->basic_response->error_num != "0"){
            SOAP::logSoap('createHousingApp', 'failed', $params);
            throw new BannerException('Error while reporting application to Banner.', $response->basic_response->error_num, 'reportApplicationReceived', $params);
        }

        SOAP::logSoap('createHousingApp', 'success', $params);

        return true;
    }

    public function createRoomAssignment($bannerId, $term, $building, $bannerBedId)
    {
        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'BldgCode'  => $building,
                        'RoomCode'  => $bannerBedId,
                        'PlanCode'  => 'HOME',
                        'UserType'  => $this->userType);
        try{
            $response = $this->client->CreateRoomAssignment($params);
        }catch(SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'createRoomAssignment', $params);
        }

        if($response->basic_response->error_num != "0"){
            SOAP::logSoap('createRoomAssignment', 'failed', $params);
            throw new BannerException('Error while reporting assignment to Banner.', $response->basic_response->error_num, 'createRoomAssignment', $params);
        }

        SOAP::logSoap('createRoomAssignment', 'success', $params);

        return true;
    }

    public function removeRoomAssignment($bannerId, $term, $building, $bannerBedId, $percentRefund)
    {
        $params = array(
                        'User'          => $this->currentUser,
                        'BannerID'      => $bannerId,
                        'TermCode'      => $term,
                        'BldgCode'      => $building,
                        'RoomCode'      => $bannerBedId,
                        'PercentRefund' => $percentRefund);

        try{
            $response = $this->client->RemoveRoomAssignment($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'removeRoomAssignment', $params);
        }

        if($response->basic_response->error_num != "0"){
            SOAP::logSoap('removeRoomAssignment', 'failed', $params);
            throw new BannerException('Error while reporting removal to Banner.', $response->basic_response->error_num, 'removeRoomAssignment', $params);
        }

        SOAP::logSoap('removeRoomAssignment', 'success', $params);

        return true;
    }

    public function createMealPlan($bannerId, $term, $mealCode)
    {
        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'MealCode'  => $mealCode);

        try{
            $response = $this->client->CreateMealPlan($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'CreateMealPlan', $params);
        }

        if($response->basic_response->error_num === 1403){
            SOAP::logSoap('createMealPlan', 'Already exists', $params, $response->basic_response->error_num);
            throw new MealPlanExistsException('Meal plan already exists.', $response->basic_response->error_num);
        }

        if($response->basic_response->error_num !== 0){
            SOAP::logSoap('createMealPlan', 'failed', $params, $response->basic_response->error_num, $response->basic_response->error_message);
            throw new BannerException('Error creating meal plan: ' . $response->basic_response->error_message, $response->basic_response->error_num, 'CreateMealPlan', $params);
        }

        SOAP::logSoap('createMealPlan', 'success', $params);
        return true;
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
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getHousMealRegister', $params);
        }

        SOAP::logSoap('getHousMealRegister', 'success', $params);
        return $response->GetHousMealRegister;
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
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'getBannerIdByBuildingRoom', $params);
        }

        SOAP::logSoap('getBannerIdByBuildingRoom', 'success', $params);

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
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'setHousingWaiver', $params);
        }

        if($response->basic_response->error_num != "0"){
            throw new BannerException('Error while setting waiver flag in Banner.', $response->basic_response->error_num, 'setHousingWaiver', $params);
        }

        SOAP::logSoap('setHousingWaiver', 'success', $params);

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
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'clearHousingWaiver', $params);
        }

        if($response->basic_response->error_num != "0"){
            throw new BannerException('Error while clearing waiver flag in Banner.', $response->basic_response->error_num, 'clearHousingWaiver', $params);
        }

        SOAP::logSoap('clearHousingWaiver', 'success', $params);

        return true;
    }

    public function addRoomDamageToStudentAccount($bannerId, $term, $amount, $damageDescription, $detailCode)
    {
        // Description field has a max size of 20 in Banner, so truncate to 20 chars if needed
        $damageDescription = (strlen($damageDescription) > 20) ? substr($damageDescription, 0, 20) : $damageDescription;

        $params = array(
                        'User'      => $this->currentUser,
                        'BannerID'  => $bannerId,
                        'TermCode'  => $term,
                        'Amount'    => $amount,
                        'DamageDescription' => $damageDescription,
                        'DamageDetailCode' => $detailCode);

        try {
            $response = $this->client->AddRoomDamageToStudentAccount($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'AddRoomDamageToStudentAccount', $params);
        }

        if($response->basic_response->error_num != "0"){
            throw new BannerException('Error while reporting room damage to Banner.', $response->basic_response->error_num, 'addRoomDamageToStudentAccount', $params);
        }

        SOAP::logSoap('AddRoomDamageToStudentAccount', 'success', $params);

        return true;
    }

    /**
     * Calls web service function to move room assignments. This is important to keep billing correct because moveAssignment does prorating of charges.
     */
    public function moveRoomAssignment(Array $students, $term)
    {
        $params = array(
            'User'      => $this->currentUser,
            'TermCode'  => $term,
            'Students'  => $students
        );

        try {
            $response = $this->client->MoveRoomAssignment($params);
        }catch(\SoapFault $e){
            throw new SOAPException($e->getMessage(), $e->getCode(), 'MoveRoomAssignmentResult', $params);
        }

        if($response->basic_response->error_num != "0"){
            throw new BannerException('Error while moving room assignments in Banner.', $response->basic_response->error_num, 'MoveRoomAssignmentResult', $params);
        }

        $logParams = array();
        $logParams['params'] = print_r($params, true);
        
        SOAP::logSoap('moveRoomAssignment', 'success', $logParams, true);
    }
}
