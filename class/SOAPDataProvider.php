<?php

PHPWS_Core::initModClass('hms', 'SOAP.php');

class SOAPDataProvider extends StudentDataProvider {

    public function getStudentByUsername($username, $term)
    {
        $soap = SOAP::getInstance(UserStatus::getUsername(), UserStatus::isAdmin()?(SOAP::ADMIN_USER):(SOAP::STUDENT_USER));
        $id = $soap->getBannerId($username);

        if(!isset($id) || is_null($id) || empty($id)){
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException('No matching student found.');
        }

        return SOAPDataProvider::getStudentById($id, $term);
    }

    public function getStudentById($id, $term)
    {
        $student = new Student();

        $soap = SOAP::getInstance(UserStatus::getUsername(), UserStatus::isAdmin()?(SOAP::ADMIN_USER):(SOAP::STUDENT_USER));
        $soapData = $soap->getStudentProfile($id, $term);

        if($soapData->error_num == 1101 && $soapData->error_desc == 'LookupStudentID'){
            PHPWS_Core::initModClass('hms', 'exception/StudentNotFoundException.php');
            throw new StudentNotFoundException('No matching student found.');
        }elseif (isset($soapData->error_num) && $soapData->error_num > 0){
            throw new SOAPException("Error while accessing SOAP interface: {$soapData->errorDesc} ({$soapData->error_num})");
        }

        SOAPDataProvider::plugSOAPData($student, $soapData);

        SOAPDataProvider::applyExceptions($student);

        $student->setDataSource(get_class($this));

        return $student;
    }

    private static function plugSOAPData(&$student, $soapData)
    {
        $student->setBannerId($soapData->banner_id);
        $student->setUsername($soapData->user_name);

        $student->setFirstName($soapData->first_name);
        $student->setMiddleName($soapData->middle_name);
        $student->setLastName($soapData->last_name);
        $student->setPreferredName($soapData->pref_name);

        $student->setDOB($soapData->dob);
        $student->setGender($soapData->gender);

        $student->setConfidential($soapData->confid);

        $student->setApplicationTerm($soapData->application_term);
        $student->setType($soapData->student_type);
        $student->setClass($soapData->projected_class);
        $student->setCreditHours($soapData->credhrs_completed);

        if(isset($soapData->student_level)){
            $student->setStudentLevel($soapData->student_level);
        }else{
            $student->setStudentLevel('');
        }
        
        $student->setInternational($soapData->international);

        $student->setHonors($soapData->honors);
        $student->setTeachingFellow($soapData->teaching_fellow);
        $student->setWataugaMember($soapData->watauga_member);
        $student->setGreek($soapData->greek);
        
        $student->setPinDisabled($soapData->disabled_pin);
        $student->setHousingWaiver($soapData->housing_waiver);
        
        if(isset($soapData->app_decision_code)){
            $student->setAdmissionDecisionCode($soapData->app_decision_code);
        }else{
            $student->setAdmissionDecisionCode('');
        }

        if(isset($soapData->app_decision_desc)){
            $student->setAdmissionDecisionDesc($soapData->app_decision_desc);
        }else{
            $student->setAdmissionDecisionDesc('');
        }

        /*****************
         * Phone Numbers *
         *****************/
         //TODO improve this so we're getting the other phone number fields
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

        /*************
         * Addresses *
         *************/
        if(isset($soapData->address) && is_array($soapData->address) && count($soapData->address) > 0){
            // Array of address objects given, just pass the array on to the new Student object
            $student->setAddressList($soapData->address);
        }else if(isset($soapData->address)){
            // Only one address object given, make it into an array
            $student->setAddressList(array($soapData->address));
        }else{
            // $soapData->address property probably wasn't defined, so set addressList to empty array
            $student->setAddressList(array());
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
        
        // This is a hack to fix the student type for international grad students
        $type = $student->getType();
        if((!isset($type) || $type == '') && $student->getStudentLevel() == LEVEL_GRAD && $student->isInternational() == 1){
            $student->setType(TYPE_GRADUATE);
            $student->setClass(CLASS_SENIOR);
        }

        if($student->getBannerId() == '900325006'){
            $student->setClass(CLASS_SENIOR);
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
