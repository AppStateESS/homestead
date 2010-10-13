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
				//PHPWS_Core::initModClass('hms', 'BannerSOAP.php');
				//self::$instance = new BannerSOAP();
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

    protected static function checkResponse($resonse)
    {
        # Check for a SOAP fault
        if($response instanceof SoapFault){
            SOAP::logSoapFault($response, 'getStudentInfo', $username);

			PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
			throw new SOAPExcpetion($response->__toString());
        }

        # Check for a banner error
		if(is_numeric($response) && $response > 0){
			SOAP::logSoap('get_student_info', "Banner Error: $response", $username, $term);
			SOAP::logSoapError('error code: ' . $response, 'get_student_info', $username);

			PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
			throw new BannerException('Banner error', $response);
		}
    }

	/**
	 * Returns TRUE if an error object is of class 'soap_fault'
     * @depricated - use CheckResponse() instead
	 */
	protected static function isSoapFault($object)
	{
		if(is_object($object) && is_a($object, 'soap_fault')){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	/**
	 * Uses the PHPWS_Core log public function to 'manually' log soap errors to soap_error.log.
	 */
	protected static function logSoapFault($soap_fault, $function, $extra_info)
	{
		$error_msg = $soap_fault->message . 'in public function: ' . $function . " Extra: " . $extra_info;
		PHPWS_Core::log($error_msg, 'soap_error.log', _('Error'));
	}

	/**
	 * Uses the PHPWS_Core log public function to 'manually' log soap erros to soap_error.log.
	 */
	protected static function logSoapError($message, $function, $extra)
	{
		PHPWS_Core::log('Banner error: ' . $message . ' in public function: ' . $function . ' Extra: ' . $extra, 'soap_error.log', 'Error');
	}

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
