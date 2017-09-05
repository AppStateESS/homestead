<?php

namespace Homestead;

use \Homestead\Exception\StudentNotFoundException;
use \Homestead\Exception\SOAPException;

class SOAPDataProvider extends StudentDataProvider {

    public function getStudentByUsername($username, $term)
    {
        // Force username to lowercase (SOAP is case sensitive)
        $username = strtolower($username);

        $soap = SOAP::getInstance(UserStatus::getUsername(), UserStatus::isAdmin()?(SOAP::ADMIN_USER):(SOAP::STUDENT_USER));
        $id = $soap->getBannerId($username);

        if (!isset($id) || is_null($id) || empty($id)) {
            throw new StudentNotFoundException('No matching student found.', 0, $id);
        }

        return SOAPDataProvider::getStudentById($id, $term);
    }

    public function getStudentById($id, $term)
    {
        // Sanity checking on the Banner ID
        $id = trim($id);

        if (!isset($id) || empty($id) || $id == '') {
            throw new \InvalidArgumentException('Missing Banner id. Please enter a valid Banner ID (nine digits).');
        }

        if (strlen($id) > 9 || strlen($id) < 9 || !preg_match("/^[0-9]{9}$/", $id)) {
            throw new \InvalidArgumentException('That was not a valid Banner ID. Please enter a valid Banner ID (nine digits).');
        }

        $student = new Student();

        $soap = SOAP::getInstance(UserStatus::getUsername(), UserStatus::isAdmin()?(SOAP::ADMIN_USER):(SOAP::STUDENT_USER));
        $soapData = $soap->getStudentProfile($id, $term);

        if ($soapData->error_num == 1101 && $soapData->error_desc == 'LookupStudentID') {
            throw new StudentNotFoundException('No matching student found.');
        }elseif (isset($soapData->error_num) && $soapData->error_num > 0) {
            //test($soapData,1);
            throw new SOAPException("Error while accessing SOAP interface: {$soapData->error_desc} ({$soapData->error_num})", $soapData->error_num, 'getStudentProfile', array($id, $term));
        }

        SOAPDataProvider::plugSOAPData($student, $soapData);

        //SOAPDataProvider::applyExceptions($student);
        require_once(PHPWS_SOURCE_DIR . SOAP_DATA_OVERRIDE_PATH);
        $dataOverride = new \SOAPDataOverride();
        $dataOverride->applyExceptions($student);

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

        if(isset($soapData->application_term)){
            $student->setApplicationTerm($soapData->application_term);
        }else{
            $student->setApplicationTerm(null);
        }

        $student->setType($soapData->student_type);
        $student->setClass($soapData->projected_class);
        $student->setCreditHours($soapData->credhrs_completed);

        if (isset($soapData->student_level)) {
            $student->setStudentLevel($soapData->student_level);
        } else {
            $student->setStudentLevel('');
        }

        $student->setInternational($soapData->international);

        $student->setHonors($soapData->honors);
        $student->setTeachingFellow($soapData->teaching_fellow);
        $student->setWataugaMember($soapData->watauga_member);
        $student->setGreek($soapData->greek);

        $student->setPinDisabled($soapData->disabled_pin);
        $student->setHousingWaiver($soapData->housing_waiver);

        if (isset($soapData->app_decision_code)) {
            $student->setAdmissionDecisionCode($soapData->app_decision_code);
        } else {
            $student->setAdmissionDecisionCode('');
        }

        if (isset($soapData->app_decision_desc)) {
            $student->setAdmissionDecisionDesc($soapData->app_decision_desc);
        } else {
            $student->setAdmissionDecisionDesc('');
        }

        /*****************
         * Phone Numbers *
         *****************/
         //TODO improve this so we're getting the other phone number fields
        $phoneNumbers = array();

        if (isset($soapData->phone) && is_array($soapData->phone)) {
            foreach($soapData->phone as $phone_number) {
                $phoneNumbers[] = '('.$phone_number->area_code.') '.$phone_number->number . (!empty($phone_number->ext) ? ' ext. '.$phone_number->ext : '');
            }
        } elseif (isset($soapData->phone)) {
            $phone_number = $soapData->phone;
            $phoneNumbers[] = '('.$phone_number->area_code.') '.$phone_number->number . (!empty($phone_number->ext) ? ' ext. '.$phone_number->ext : '');
        }

        $phoneNumbers = array_unique($phoneNumbers);
        $student->setPhoneNumberList($phoneNumbers);

        /*************
         * Addresses *
         *************/
        if (isset($soapData->address) && is_array($soapData->address) && count($soapData->address) > 0) {
            // Array of address objects given, just pass the array on to the new Student object
            $student->setAddressList($soapData->address);
        } else if (isset($soapData->address)) {
            // Only one address object given, make it into an array
            $student->setAddressList(array($soapData->address));
        } else {
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
}
