<?php

/**
 * SOAP parent class. Defines the basic functions of the SOAP interface and handles some utility functions.
 *
 * @author jbooker
 *
 */

if (!defined('SOAP_OVERRIDE_FILE')) {
    define('SOAP_OVERRIDE_FILE', 'TestSOAP.php');
}

abstract class SOAP
{

    // User types
    const ADMIN_USER    = 'A';
    const STUDENT_USER  = 'S';

    protected static $instance;
    protected static $cache;

    protected $currentUser; // User name for the currently logged in user
    protected $userType; // User type (one of 'S' (student), or 'A' (admin staff)

    /**
     * Constructor
     *
     * @param string $username Username of the currently logged in user
     * @param string $userType The user type of the currently logged in user (one of 'S' (student), or 'A' (admin staff)
     */
    protected function __construct($username, $userType)
    {
        $this->currentUser  = $username;
        $this->userType     = $userType;
    }

    /**
     * Get an instance of the singleton.
     *
     * @param string $username User name of the currently logged in user
     * @param enum   $userType Type of user, as set in defines
     * @return SOAP - Instance of the SOAP class.
     */
    public static function getInstance($username, $userType)
    {
        if(!isset($username) || is_null($username)){
            throw new InvalidArgumentException('Missing Username.');
        }

        if(!isset($userType) || is_null($userType)){
            throw new InvalidArgumentException('Missing user type.');
        }

        if(empty(self::$instance)) {
            if(SOAP_INFO_TEST_FLAG) {
                PHPWS_Core::initModClass('hms', SOAP_OVERRIDE_FILE);
                self::$instance = new TestSOAP($username, $userType);
            } else {
                PHPWS_Core::initModClass('hms', 'PhpSOAP.php');
                self::$instance = new PhpSOAP($username, $userType);
            }
        }

        return self::$instance;
    }

    /**
     * Main public function for getting student info. Returns a Profile object from
     * SOAP representing the requested student's pfofile data, or an empty object
     * if no student exists with the requested banner id.
     *
     * @param   string    $bannerId
     * @param   Integer   $term
     * @return  SOAP object
     * @throws  InvalidArgumentException, SOAPException
     */
    public abstract function getStudentProfile($bannerId, $term);

    /**
     * Returns a Profile object from SOAP representing the requested student, or an empty object
     * if the requested user name doesn't exist.
     *
     * Deprecated in favor of getStudentProfile()
     *
     * @deprecated
     * @see getStudentProfile()
     * @param String    $username
     * @param Integer   $term
     * @return SOAP object
     * @throws InvalidArgumentException
     */
    public function getStudentInfo($username, $term){
        $bannerId = $this->getBannerId($username);

        if(!isset($bannerId) || $bannerId == ''){
            throw new InvalidArgumentException('No user found with username: ' . $username);
        }

        return $this->getStudentProfile($bannerId, $term);
    }

    /**
     * Returns the ASU Username for the given banner id
     *
     * @param  Integer $bannerId
     * @return String  Username corresponding to given Banner id.
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function getUsername($bannerId);

    /**
     * Returns the Banner ID for the given username
     *
     * @param string $username
     * @return string banner id corresponding ot given user name
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function getBannerId($username);

    /**
     * Returns true if the given username corresponds to a valid student for the given semester. Returns false otherwise.
     *
     * @param String $username
     * @param Integer $term
     * @return bool
     */
    public abstract function isValidStudent($username, $term);

    /**
     * Returns true if the student with the given Banner ID has established a parent PIN in Banner, false otherwise.
     *
     * @param string $bannerId\
     * @return boolean
     * @throws InvalidArgumentException, BannerException
     */
    public abstract function hasParentPin($bannerId);

    /**
     * Returns a Array/Object (??) of parent access information for the student with
     * the given Banner Id if the given parent PIN is correct (as set in Banner by the student).
     *
     * @param string $bannerId
     * @param string $parentPin
     * @return Mixed $parentAccess
     * @throws InvalidArgumentException, BannerException
     */
    public abstract function getParentAccess($bannerId, $parentPin);

    /**
     * Report that a housing application has been received.
     * Makes First Connections stop bugging the students.
     *
     * @deprecated
     * @see createHousingApp()
     * @param String $username
     * @param Integer $term
     * @return boolean True if successful
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public function reportApplicationReceived($username, $term)
    {
        $bannerId = $this->getBannerId($username);

        if(!isset($bannerId) || $bannerId == ''){
            throw new InvalidArgumentException('No user found with username: ' . $username);
        }

        return $this->createHousingApp($bannerId, $term);
    }

    /**
     * Create a housing application in Banner.
     * Makes admissions software stop bugging students.
     *
     * @param string $bannerId The student's banner ID
     * @param string $term The term for which the application should be created
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function createHousingApp($bannerId, $term);

    /**
     * Creates a room assignment in Banner. Will cause students to be billed, etc.
     *
     * @param String $bannerId
     * @param Integer $term
     * @param String $building Banner building code
     * @param Integer $bannerBedId Banner bed Id.
     * @param String $plan Banner plan code ('HOUSE' or 'HOME', defaults to 'HOME').
     * @return boolean True if successful.
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function createRoomAssignment($bannerId, $term, $building, $bannerBedId);

    /**
     * Sends a room assignment to banner. Will cause students to be billed, etc.
     *
     * @deprecated
     * @see createRoomAssignment()
     * @param String $username
     * @param Integer $term
     * @param String $building_code Banner building code
     * @param Integer $room_code Banner bed Id.
     * @param String $plan_code Banner plan code ('HOUSE' or 'HOME').
     * @param Integer $meal_code Banner meal code (numeric code for meal plan level)
     * @return boolean True if successful.
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public function reportRoomAssignment($username, $term, $building_code, $room_code, $plan_code)
    {
        $bannerId = $this->getBannerId($username);
        return $this->createRoomAssignment($bannerId, $term, $building_code, $room_code, $plan_code);
    }

    /**
     * Remove a room assignment in Banner.
     * Will cause students to be credited, etc.
     *
     * @param String $bannerId
     * @param Integer $term
     * @param String $building Banner building code
     * @param Integer $bannerBedId Banner bed id.
     * @param Integer $percentRefund Percent of original charges student should be refunded.
     * @return boolean True if successful
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function removeRoomAssignment($bannerId, $term, $building, $bannerBedId, $percentRefund);


    /**
     * Create a meal plan for the given student.
     * Will throw an exception if a meal plan already exists.
     *
     * @param String $bannerId
     * @param Integer $term
     * @param Integer $mealCode Banner meal code (numeric code for meal plan level)
     * @return boolean True if successful.
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function createMealPlan($bannerId, $term, $mealCode);

    /**
     * Sets the flag in Banner that says this student is exempt from
     * the freshmen on-campus living requirement.
     *
     * @param string $bannerId
     * @param string $term
     * @return boolean True if successful, false otherwise
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function setHousingWaiver($bannerId, $term);

    /**
     * Sets the flag in Banner that says this student is exempt from
     * the freshmen on-campus living requirement.
     *
     * @param string $bannerId
     * @param string $term
     * @return boolean True if successful, false otherwise
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function clearHousingWaiver($bannerId, $term);

    /**
     * Returns a student's current assignment information
     * $opt is one of:
     *  'All'
     *  'HousingApp'
     *  'RoomAssign'
     *  'MealAssign'
     *
     * @param String $bannerId
     * @param Integer $term
     * @param String $opt
     * @return stdClass
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function getHousMealRegister($bannerId, $term, $opt);

    /**
     * Queries Banner for the BannerID of the student assigned to a given bed.
     * Returns null if there is no student assigned to the bed.
     *
     * @param String $building - The Banner building code (eg. 'AHR', 'EHR', etc)
     * @param String $room - The Banner bed id number (eg. '01051')
     * @param Integer $term - The term to query for
     */
    public abstract function getBannerIdByBuildingRoom($building, $room, $term);

    /**
     * Adds a damage change to the given student's account.
     *
     * @param Integer $bannerId Student's Banner ID
     * @param Integer $term Term to report the damage in
     * @param Integer $amount Damage cost/price, whole dollars only
     * @param String  $damageDescription Short descirption of the damage
     * @param String  $detailCode Detail code string to record with the charge in student's Banner account
     * @return boolean True if successful, false otherwise
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function addRoomDamageToStudentAccount($bannerId, $term, $amount, $damageDescription, $detailCode);


    /**
     * Moves each student in the array from their current bed to a new bed in
     * an atomic operation. This allows the billing to be pro-rated depending
     * on the date of the move and the difference in room rates.
     *
     * @see BannerRoomChangeStudent
     * @param Array<BannerRoomChangeStudent> Array of BannerRoomChangeStudent objects representing each student to move
     * @param Integer $term The term for these assignments
     * @return boolean True if successful, false otherwise
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function moveRoomAssignment(Array $students, $term);



    /*********************
     * Utility Functions *
     *********************/

    /**
     * Uses the PHPWS_Core log public function to 'manually' log soap requests
     *
     * @param String $function The name of the function that's doing the logging.
     * @param String $result A string indicating the result of the function call. Could be anything (usually "success").
     * @return void
     */
     protected static function logSoap($function, $result, Array $params, $responseCode = null, $errorMessage = null)
     {
         $args = '';

         foreach($params as $p){
             if(is_array($p) || is_object($p)){
                 $args[] = implode(', ', $p);
             }else{
                 $args[] = $p;
             }
         }

         $args = implode(', ', $args);
         $msg = "$function($args) result: $result ";

         if($responseCode !== null){
             $msg .= "($responseCode: $errorMessage)";
         }

         PHPWS_Core::log($msg, 'soap.log', 'SOAP');
     }
}
