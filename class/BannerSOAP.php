<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class BannerSOAP extends SOAP{

	/**
	 * Main public function for getting student info.
	 * Used by the rest of the "get" public functions
	 * @return SOAP object
	 * @throws InvalidArgumentException, SOAPException
	 */
	public function getStudentInfo($username, $term)
	{
		// Sanity checking on the username
		if(empty($username) || is_null($username) || !isset($username)){
			throw new InvalidArgumentException('Bad username');
		}

		// Sanity checking on the term
		if(empty($term) || is_null($term) || !isset($username)){
			throw new InvalidArgumentException('Bad term');
		}

		include_once('SOAP/Client.php');
		$wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
		$proxy = $wsdl->getProxy();
		$proxy->setOpt('timeout', 5000);
		$student = $proxy->GetStudentProfile($username, $term);

		# Check for an PEAR error and log it
		if(SOAP::isSoapFault($student)){
			SOAP::logSoap('get_student_info', 'PEAR error', $username,
			$term);
			SOAP::logSoapFault($student,'get_student_info',$username);

			PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
			throw new SOAPExcepetion('SOAP Fault');
		}

		# Check for a banner error
		if(is_numeric($student) && $student > 0){
			SOAP::logSoap('get_student_info', "Banner Error: $student",
			$username, $term);
			SOAP::logSoapError('error code: ' . $student, 'get_student_info', $username);

			PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
			throw new BannerException('Banner error', $student);
		}

		SOAP::logSoap('get_student_info', 'success', $username, $term);

		return $student;
	}

	/**
	 * Returns the ASU Username for the given banner id
	 */
	public function getUsername($banner_id)
	{
		include_once('SOAP/Client.php');
		$wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
		$proxy = $wsdl->getProxy();
		$proxy->setOpt('timeout', 5000);
		$username = $proxy->GetUserName($banner_id);

		if(SOAP::isSoapFault($username)) {
			SOAP::logSoapError($username, 'get_username', $bannerid);

			PHPWS_Core::initModClass('hms', 'exception/SOAPException.php');
			throw new SOAPException('SOAP Fault');
		}

		return $username;
	}
	
	public function isValidStudent($username, $term)
	{
	    $student = self::getStudentInfo($username, $term);
	    
	    // Check for a banner ID. If that's null/empty, then we're pretty sure this student doesn't exist
	    if(!isset($student->banner_id) || is_null($student->banner_id) || empty($student->banner_id)){
	        return false;
	    }
	    
	    return true;
	}

	/**
	 * Report that a housing application has been received.
	 * Makes First Connections stop bugging the students.
	 */
	public function reportApplicationReceived($username, $term)
	{
		include_once('SOAP/Client.php');
		$wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
		$proxy = $wsdl->getProxy();
		$proxy->setOpt('timeout', 5000);
		$result = $proxy->CreateHousingApp($username, $term, 'HOME', null); // always report a null meal plan

		# Check for an error and log it
		if(SOAP::isSoapFault($result)){
			SOAP::logSoap('report_application_received', 'PEAR error',
			$username, $term, $plan_code, $meal_code);
			SOAP::logSoapFault($result, 'report_application_received', $username . ' ' . $term);

			PHPWS_Core::initModClass('hms', 'SOAPException.php');
			throw new SOAPException('SOAP exception');
		}

		# It's not a SOAP Fault, so hopefully it's an int.
		$result = (int)$result;

		# Check for a banner error
		if($result > 0){
			SOAP::logSoap('report_application_received',
                "Banner error: $result", $username, $term, $plan_code,
			$meal_code);
			SOAP::logSoapError($result, 'report_application_received', $username);

			PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
			throw new BannerException('Banner error', $result);
		}

		SOAP::logSoap('report_application_received', 'success', $username,
		$term, $plan_code, $meal_code);

		return $result;
	}

	/**
	 * Sends a room assignment to banner. Will cause students to be billed, etc.
	 */
	public function reportRoomAssignment($username, $term, $building_code, $room_code, $plan_code = 'HOME', $meal_code)
	{
		include_once('SOAP/Client.php');
		$wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
		$proxy = $wsdl->getProxy();
		$proxy->setOpt('timeout', 5000);
		$assignment = $proxy->CreateRoomAssignment($username, $term, $building_code, $room_code, $plan_code, $meal_code);

		# Check for an error and log it
		if($this->isSoapFault($assignment)){
			SOAP::logSoap('report_room_assignment', 'PEAR error',
			$username, $term, $building_code, $room_code, $plan_code,
			$meal_code);
			SOAP::logSoapFault($assignment, 'report_room_assignment', $username . ' ' . $term);

			PHPWS_Core::initModClass('hms', 'SOAPException.php');
			throw new SOAPException('SOAP exception');
		}

		# Check for a banner error
		if(is_numeric($assignment) && $assignment > 0){
			$this->logSoap('report_room_assignment',
                "Banner error: $assignment", $username, $term, $building_code,
			$room_code, $plan_code, $meal_code);
			SOAP::logSoapError('Banner error: ' . $assignment, 'report_room_assignment', $username);

			PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
			throw new BannerException('Banner error', $assignment);
		}

		SOAP::logSoap('report_room_assignment', 'success', $username,
		$term, $building_code, $room_code, $plan_code, $meal_code);

		return $assignment;
	}

	/**
	 * Remove the deletion of a room assignment to Banner.
	 * Will cause students to be credited, etc.
	 */
	public function removeRoomAssignment($username, $term, $building, $room)
	{
		include_once('SOAP/Client.php');
		$wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
		$proxy = $wsdl->getProxy();
		$proxy->setOpt('timeout', 5000);
		$removal = $proxy->RemoveRoomAssignment($username, $term, $building, $room);

		# Check for an error and log it
		if($this->isSoapFault($removal)){
			SOAP::logSoap('remove_room_assignment', 'PEAR error',
			$username, $term, $building, $room);
			SOAP::logSoapFault($removal, 'remove_room_assignment', $username . ' ' . $term);

			PHPWS_Core::initModClass('hms', 'SOAPException.php');
			throw new SOAPException('SOAP exception');
		}

		# Check for a banner error
		if(is_numeric($removal) && $removal > 0){
			SOAP::logSoap('remove_room_assignemnt',
                "Banner error: $removal", $username, $term, $building, $room);
			SOAP::logSoapError('Banner error: ' . $removal, 'remove_room_assignment', $username);

			PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
			throw new BannerException('Banner error', $result);
		}

		SOAP::logSoap('remove_room_assignment', 'success', $username,
		$term, $building, $room);

		return $removal;
	}

	/**
	 * Returns a student's current assignment information
	 * $opt is one of:
	 *  'All'
	 *  'HousingApp'
	 *  'RoomAssign'
	 *  'MealAssign'
	 */
	public function getHousMealRegister($username, $termcode, $opt)
	{
		include_once('SOAP/Client.php');
		$wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
		$proxy = $wsdl->getProxy();
		$proxy->setOpt('timeout', 5000);
		$student = $proxy->GetHousMealRegister($username, $termcode, $opt);

		# Check for an error and log it
		if($this->isSoapFault($student)) {
			SOAP::logSoap('get_hous_meal_register', 'PEAR Error',
			$username, $termcode, $opt);
			SOAP::logSoapFault($student, 'get_hous_meal_register', $username);

			PHPWS_Core::initModClass('hms', 'SOAPException.php');
			throw new SOAPException('SOAP exception');
		}

		# Check for a banner error
		if(is_numeric($student) && $student > 0){
			SOAP::logSoap('get_hous_meal_register',
                "Banner error: $student", $username, $termcode, $opt);
			SOAP::logSoapError('Banner error: ' . $student, 'get_hous_meal_register', $username);

			PHPWS_Core::initModClass('hms', 'exception/BannerException.php');
			throw new BannerException('Banner error', $result);
		}

		SOAP::logSoap('get_hous_meal_register', 'success',
		$username, $termcode, $opt);

		return $student;
	}
}

?>
