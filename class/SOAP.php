<?php

/**
 * SOAP parent class. Defines the basic functions of the SOAP interface and handles some utility functions.
 *
 * @author jbooker
 *
 */
abstract class SOAP
{

    protected static $instance;
    protected static $cache;

    /**
     * Constructor
     */
    protected function __construct()
    {

    }

    /**
     * Get an instance of the singleton.
     *
     * @return SOAP - Instance of the SOAP class.
     */
    public static function getInstance()
    {
        if(empty(self::$instance)) {
            if(SOAP_INFO_TEST_FLAG) {
                PHPWS_Core::initModClass('hms', 'TestSOAP.php');
                self::$instance = new TestSOAP();
            } else {
                PHPWS_Core::initModClass('hms', 'PhpSOAP.php');
                self::$instance = new PhpSOAP();
            }
        }

        return self::$instance;
    }

    /**
     * Main public function for getting student info.
     * Used by the rest of the "get" public functions
     *
     * @param String    $username
     * @param Integer   $term
     * @return SOAP object
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function getStudentInfo($username, $term);


    /**
     * Returns the ASU Username for the given banner id
     *
     * @param  Integer $bannerId
     * @return String  Username corresponding to given Banner id.
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function getUsername($bannerId);

    /**
     * Returns true if the given user name corresponds to a valid student for the given semester. Returns false otherwise.
     *
     * @param String $username
     * @param Integer $term
     * @return bool
     */
    public abstract function isValidStudent($username, $term);

    /**
     * Report that a housing application has been received.
     * Makes First Connections stop bugging the students.
     *
     * @param String $username
     * @param Integer $term
     * @return boolean True if successful
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function reportApplicationReceived($username, $term);

    /**
     * Sends a room assignment to banner. Will cause students to be billed, etc.
     *
     * @param String $username
     * @param Integer $term
     * @param String $building_code Banner building code
     * @param Integer $room_code Banner bed Id.
     * @param String $plan_code Banner plan code ('HOUSE' or 'HOME').
     * @param Integer $meal_code Banner meal code (numeric code for meal plan level)
     * @return boolean True if successful.
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function reportRoomAssignment($username, $term, $building_code, $room_code, $plan_code, $meal_code);

    /**
     * Remove the deletion of a room assignment to Banner.
     * Will cause students to be credited, etc.
     *
     * @param String $username
     * @param Integer $term
     * @param String $building Banner building code
     * @param Integer $room Banner bed code.
     * @return boolean True if successful
     * @throws InvalidArgumentException, SOAPException, BannerException
     */
    public abstract function removeRoomAssignment($username, $term, $building, $room);

    /**
     * Returns a student's current assignment information
     * $opt is one of:
     *  'All'
     *  'HousingApp'
     *  'RoomAssign'
     *  'MealAssign'
     *
     * @param String $username
     * @param Integer $termcode
     * @param String $opt
     * @return void
     * @throws InvalidArgumentException, SOAPException
     */
    public abstract function getHousMealRegister($username, $termcode, $opt);

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
    protected static function logSoap($function, $result)
    {
        $arglist = func_get_args();
        $args = implode(', ', array_slice($arglist, 2));
        $msg = "$function($args) result: $result";
        PHPWS_Core::log($msg, 'soap.log', 'SOAP');
    }
}

?>
