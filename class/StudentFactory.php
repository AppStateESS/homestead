<?php

PHPWS_Core::initModClass('hms', 'Student.php');
PHPWS_Core::initModClass('hms', 'SOAP.php');

class StudentFactory {
	
	public static function getStudentByUsername($username, $term)
	{
	    
		$student = new Student();
		$student->setUsername($username);
		
		$soap = SOAP::getInstance();
		$soapData = $soap->getStudentInfo($username, $term);
		//TODO throw exception if null result from SOAP
		
		StudentFactory::plugSOAPData($student, $soapData);
		
		return $student;
	}
	
	public static function getStudentByBannerId($bannerId, $term)
	{
		$soap = SOAP::getInstance();
		$username = $soap->getUsername($bannerId);
		// TODO: throw exception if nothing is returned
		
		return StudentFactory::getStudentByUsername($username, $term);
	}
	
	private static function plugSOAPData(&$student, $soapData)
	{
		$student->setBannerId($soapData->banner_id);
		
		$student->setFirstName($soapData->first_name);
		$student->setMiddleName($soapData->middle_name);
		$student->setLastName($soapData->last_name);
		
		$student->setGender($soapData->gender);
		$student->setDOB($soapData->dob);
		
		$student->setApplicationTerm($soapData->application_term);
		$student->setType($soapData->student_type);
		$student->setClass($soapData->projected_class);
		$student->setCreditHours($soapData->credhrs_completed);
		
		$phoneNumbers = array();
		
		if(is_array($soapData->phone)){
            foreach($soapData->phone as $phone_number){
                $phoneNumbers[] = '('.$phone_number->area_code.') '.$phone_number->number . (!empty($phone_number->ext) ? ' ext. '.$phone_number->ext : '');
            }
        }else{
            $phone_umber = $soapData->phone;
            $phoneNumbers[] = '('.$phone_number->area_code.') '.$phone_number->number . (!empty($phone_number->ext) ? ' ext. '.$phone_number->ext : '');
        }
        
        $phoneNumbers = array_unique($phoneNumbers);
		$student->setPhoneNumberList($phoneNumbers);
		
		if(is_array($soapData->address) && count($soapData->address) > 0){
			// Array of address objects given, just pass the array on to the new Student object
			$student->setAddressList($soapData->address);
		}else{
			// Only one address object give, make it into an array
			$student->setAddressList(array($soapData->address));
		}
	}
	
}

?>