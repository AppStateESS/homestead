<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class SOAPDataProvider extends StudentDataProvider {

    public function getStudentByUsername($username, $term)
    {
        $student = new Student();
        $student->setUsername($username);

        $soap = SOAP::getInstance();
        $soapData = $soap->getStudentInfo($username, $term);

        if(!isset($soapData->banner_id) || is_null($soapData->banner_id) || empty($soapData->banner_id)){
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException('No matching student found.');
        }

        SOAPDataProvider::plugSOAPData($student, $soapData);

        SOAPDataProvider::applyExceptions($student);

        return $student;
    }

    public function getStudentById($id, $term)
    {
        $soap = SOAP::getInstance();
        $username = $soap->getUsername($id);

        if(!isset($username) || is_null($username) || empty($username)){
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException('No matching student found.');
        }

        return SOAPDataProvider::getStudentByUsername($username, $term);
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

        $student->setDepositDate($soapData->deposit_date);

        $student->setStudentLevel($soapData->student_level);
        $student->setInternational($soapData->international);

        $student->setHonors($soapData->honors);
        $student->setTeachingFellow($soapData->teaching_fellow);
        $student->setWataugaMember($soapData->watauga_member);
        
        $student->setHousingWaiver($soapData->housing_waiver);
        $student->setPinDisabled($soapData->disabled_pin);

        $phoneNumbers = array();

        if(isset($soapData->phone) && is_array($soapData->phone)){
            foreach($soapData->phone as $phone_number){
                $phoneNumbers[] = '('.$phone_number->area_code.') '.$phone_number->number . (!empty($phone_number->ext) ? ' ext. '.$phone_number->ext : '');
            }
        }elseif (isset($soapData->phone)){
            $phone_number = $soapData->phone;
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

    /**
     * No cache used in this provider, so this method doesn't do anything,
     * but we're still required to define it, just in case.
     */
    public function clearCache()
    {
    }

    private static function applyExceptions(&$student)
    {
        /*
         * This is a hack to fix some freshmen students who have application terms in the future but are considered type 'C' by the registrar's office.
         * See Trac #719
         */
        PHPWS_Core::initModClass('hms', 'Term.php');
        if($student->getApplicationTerm() > Term::getCurrentTerm() && $student->getType() == TYPE_CONTINUING){
            $student->setType(TYPE_FRESHMEN);
        }

        if($student->getUsername() == 'marshallkd'){
            $student->setApplicationTerm(201040);
        }

        if($student->getUsername() == 'weldoncr'){
            $student->setApplicationTerm(200840);
        }

        if($student->getUsername() == 'ghoniema'){
            $student->setType(TYPE_CONTINUING);
        }
        
        if($student->getUsername() == 'brannonpg'){
            $student->setApplicationTerm(201210);
        }
    }
}

?>