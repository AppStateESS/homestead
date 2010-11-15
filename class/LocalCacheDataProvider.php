<?php

PHPWS_Core::initModClass('hms', 'CachedStudent.php');

class LocalCacheDataProvider extends StudentDataProvider {

    public function getStudentByUsername($username, $term)
    {
        PHPWS_Core::initModClass('hms', 'Student.php');
        $student = new CachedStudent();

        $db = new PHPWS_DB('hms_student_cache');
        $db->addWhere('username', $username);
        $db->addWhere('term', $term);
        $db->addWhere('timestamp', time() - $this->ttl, '>');

        //$db->setTestMode();
        $result = $db->select('row');

        // If there's an error, fail gracefully to the fall-back provider
        if(PHPWS_Error::logIfError($result)){
            $provider = $this->getFallbackProvider();
            return $provider->getStudentByUsername($username, $term);
        }

        // If the result was empty, use the fallback
        if(is_null($result)){
            $provider = $this->getFallbackProvider();
            $result   = $provider->getStudentByUsername($username, $term);

            // Refresh the cache using the result
            $this->refreshCache($result, $term);
        }else{
            // Do some hackery to make a CachedStudent object out of the db array
            $result = CachedStudent::plugData($result);

            self::getAddresses($result);
            self::getPhoneNumbers($result);
        }

        return $result;
    }

    public function getStudentById($id, $term)
    {
        PHPWS_Core::initModClass('hms', 'Student.php');
        $student = new CachedStudent();

        $db = new PHPWS_DB('hms_student_cache');
        $db->addWhere('banner_id', $id);
        $db->addWhere('term', $term);
        $db->addWhere('timestamp', time() - $this->ttl, '>');

        //$db->setTestMode();
        $result = $db->select('row');

        // If there's an error, fail gracefully to the fall-back provider
        if(PHPWS_Error::logIfError($result)){
            $provider = $this->getFallbackProvider();
            return $provider->getStudentById($id, $term);
        }

        // If the result was empty, use the fallback
        if(is_null($result)){
            $provider = $this->getFallbackProvider();
            $result = $provider->getStudentById($id, $term);

            // Refresh the cache using the result
            $this->refreshCache($result, $term);
        }else{
            // Do some hackery to make a CachedStudent object out of the db array
            $result = CachedStudent::plugData($result);

            self::getAddresses($result);
            self::getPhoneNumbers($result);
        }

        return $result;
    }

    private function refreshCache(Student $student, $term){
        // Store the core data
        $db = new PHPWS_DB('hms_student_cache');
        $db->addWhere('username', $student->getUsername());
        $db->addWhere('banner_id', $student->getBannerId());
        $db->addWhere('term', $term);
        $result = $db->delete();

        // Silently log any errors
        PHPWS_Error::logIfError($result);

        $db->reset();

        $student = CachedStudent::toCachedStudent($student);

        $student->save($term, $this->ttl);

        // Silently log any errors
        PHPWS_Error::logIfError($result);

        // Store the addresses
        self::setAddresses($student);

        // Store the phone numbers
        self::setPhoneNumbers($student);
    }

    private static function getAddresses(Student &$student)
    {
        $db = new PHPWS_DB('hms_student_address_cache');
        $db->addWhere('banner_id', $student->getBannerId());
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $addresses = array();

        foreach($result as $addr){

            $obj = null;

            $obj->atyp_code = $addr['atyp_code'];
            $obj->line1     = $addr['line1'];
            $obj->line2     = $addr['line2'];
            $obj->line3     = $addr['line3'];
            $obj->city      = $addr['city'];
            $obj->state     = $addr['state'];
            $obj->zip       = $addr['zip'];

            $addresses[] = $obj;
        }

        $student->setAddressList($addresses);
    }

    private static function getPhoneNumbers(Student &$student)
    {
        $db = new PHPWS_DB('hms_student_phone_cache');
        $db->addWhere('banner_id', $student->getBannerId());
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $phoneNumbers = array();

        foreach($result as $number){
            $phoneNumbers[] = $number['number'];
        }

        $student->setPhoneNumberList($phoneNumbers);
    }

    private static function setAddresses(Student $student)
    {
        $db = new PHPWS_DB('hms_student_address_cache');
        $db->addWhere('banner_id', $student->getBannerId());
        $result = $db->delete();

        // Silently log any errors
        PHPWS_Error::logIfError($result);

        $db = new PHPWS_DB('hms_student_address_cache');
        $addressList = $student->getAddressList();

        foreach($addressList as $address){
            $db->reset();
            $address->banner_id = $student->getBannerId();
            $result = $db->saveObject($address);

            PHPWS_Error::logIfError($result);
        }
    }

    private static function setPhoneNumbers(Student $student)
    {
        $db = new PHPWS_DB('hms_student_phone_cache');
        $db->addWhere('banner_id', $student->getBannerId());
        $result = $db->delete();

        // Silently log any errors
        PHPWS_Error::logIfError($result);

        $db = new PHPWS_DB('hms_student_phone_cache');
        $phoneList = $student->getPhoneNumberList();

        foreach($phoneList as $number){
            $db->reset();
            $obj->number = $number;
            $obj->banner_id = $student->getBannerId();
            $result = $db->saveObject($obj);

            PHPWS_Error::logIfError($result);
        }
    }

    public function clearCache()
    {
        $db = new PHPWS_DB('hms_student_cache');
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $db = new PHPWS_DB('hms_student_address_cache');
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $db = new PHPWS_DB('hms_student_phone_cache');
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }
}

?>