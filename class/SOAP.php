<?php

abstract class SOAP {

	protected static $instance;
	protected static $cache;

	protected function __construct(){}

	public static function getInstance()
	{
		if(empty(self::$instance)){
			if(SOAP_INFO_TEST_FLAG){
				PHPWS_Core::initModClass('hms', 'TestSOAP.php');
				self::$instance = new TestSOAP();
			}else{
				PHPWS_Core::initModClass('hms', 'PhpSOAP.php');
				self::$instance = new PhpSOAP();
			}
		}

		return self::$instance;
	}

	/**
	 * Main public function for getting student info.
	 * Used by the rest of the "get" public functions
	 * @return SOAP object
	 * @throws InvalidArgumentException, SOAPException
	 */
	public abstract function getStudentInfo($username, $term);


	/**
	 * Returns the ASU Username for the given banner id
	 * @return String Username corresponding to given Banner id.
	 * @throws InvalidArgumentException, SOAPException
	 */
	public abstract function getUsername($banner_id);

	/**
	 * Returns true if the given user name corresponds to a valid student for the given semester. Returns false otherwise.
	 * @param String $username
	 * @param int $term
	 * @return bool
	 */
	public abstract function isValidStudent($username, $term);

	/**
	 * Report that a housing application has been received.
	 * Makes First Connections stop bugging the students.
	 * @return boolean True if successful
	 * @throws InvalidArgumentException, SOAPException, BannerException
	 */
	public abstract function reportApplicationReceived($username, $term);

	/**
	 * Sends a room assignment to banner. Will cause students to be billed, etc.
	 * @return boolean True if successful.
	 * @throws InvalidArgumentException, SOAPException, BannerException
	 */
	public abstract function reportRoomAssignment($username, $term, $building_code, $room_code, $plan_code, $meal_code);

	/**
	 * Remove the deletion of a room assignment to Banner.
	 * Will cause students to be credited, etc.
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
	 *  @throws InvalidArgumentException, SOAPException
	 */
	public abstract function getHousMealRegister($username, $termcode, $opt);

	/*********************
	 * Utility Functions *
	 *********************/

	/**
	 * Uses the PHPWS_Core log public function to 'manually' log soap requests
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
