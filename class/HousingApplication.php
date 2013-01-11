<?php

/**
 * Main model class to represent a Housing Application.
 *
 * @author jbooker
 * @package hms
 */
class HousingApplication {

    public $id = 0;

    public $term; // The term which this application is for

    public $application_type; // The type of this application, defined by each subclass' constructor

    public $banner_id;
    public $username;

    public $gender;
    public $student_type;  // 'F', 'C', 'T', etc...
    public $application_term; // The term the student started school
    public $cell_phone;
    public $meal_plan;

    /* Emergency Contact Info */
    public $emergency_contact_name;
    public $emergency_contact_relationship;
    public $emergency_contact_phone;
    public $emergency_contact_email;
    public $emergency_medical_condition;

    /* Missing Persons Information */
    public $missing_person_name;
    public $missing_person_relationship;
    public $missing_person_phone;
    public $missing_person_email;

    /* Special needs flags */
    public $physical_disability;
    public $psych_disability;
    public $medical_need;
    public $gender_need;

    public $international; // Whether or not this student is an international student. 0 => false, 1=> true (0 by default)

    public $created_on; // unix timestamp when the application as first saved
    public $created_by; // user name of the person who created this application

    public $modified_on; // unix timestamp when the application was last modified
    public $modified_by; // user name of the person who last modified this application

    /**
     * Set to 'true' by the withdrawn search process. Should always be false by default.
     * @deprecated Use 'cancelled' member variable instead.
     */
    public $withdrawn = 0;

    /* Contract Cancellation */
    public $cancelled;
    public $cancelled_reason;
    public $cancelled_by;
    public $cancelled_on;

    /**
     * Constructor for the abstract HousingApplication class. It can never be called directly. Instead,
     * it's intended for use by sub-classes.
     *
     * If the object already exists (i.e. has a non-zero 'id'), then it is up to the sub-class
     * to call the 'load()' method in this class to load the core data.
     *
     * This constrcutor should only be called
     * remaining parameters are required
     * and this method will handle initializing the values of the core application member variables defined in
     * this class
     *
     * @param string $term
     * @param string $banner_id
     * @param string $username
     * @param string $gender
     * @param string $student_type
     * @param string $application_term
     * @param string $cell_phone
     * @param string $meal_plan
     * @param string $physical_disability
     * @param string $psych_disability
     * @param string $gender_need
     * @param string $medical_need
     * @param string $international
     */
    public function __construct($term = null, $banner_id = null, $username = null, $gender = null, $student_type = null, $application_term = null, $cell_phone = null, $meal_plan = null, $physical_disability = null, $psych_disability = null, $gender_need = null, $medical_need = null, $international = null)
    {

        $this->setTerm($term);
        $this->setBannerId($banner_id);
        $this->setUsername($username);
        $this->setGender($gender);
        $this->setStudentType($student_type);
        $this->setApplicationTerm($application_term);
        $this->setCellPhone($cell_phone);
        $this->setMealPlan($meal_plan);

        $this->setPhysicalDisability($physical_disability);
        $this->setPsychDisability($psych_disability);
        $this->setMedicalNeed($medical_need);
        $this->setGenderNeed($gender_need);

        $this->setInternational($international);

        $this->setCancelled(false);
        $this->setCancelledReason(null);
        $this->setCancelledBy(null);
        $this->setCancelledOn(null);
    }

    /**
     * Loads the core housing application data (i.e. the member variables defined in this class).
     * Requires that $this->id be non-zero.
     *
     * Sub-classes can override this method to load their own data specific to that application type.
     * In that case, the sub-class should call parent::load() to execute this method and load the core
     * application data.
     */
    protected function load()
    {
        if($this->id == 0) {
            return;
        }

        $db = new PHPWS_DB('hms_new_application');
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    /**
     * Saves the core housing application data (i.e. the member variables defined in this class).
     *
     * Sub-classes can override this method to save their own data specific to that application type.
     * In that case, the sub-class should call parent::load() to execute this method and save the core
     * application data.
     */
    public function save()
    {
        $this->stamp();

        $db = new PHPWS_DB('hms_new_application');

        $result = $db->saveObject($this);
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        //see ticket #581, we don't want to log every time we save
        //$this->log();

        return true;
    }

    /**
     * Sets the last modified by/on fields and, if necessary, the created by/on fields
     */
    public function stamp()
    {
        // Set the last modified time
        $this->setModifiedOn(time());

        // Sets the 'last modified by' field according to who's logged in
        $user = UserStatus::getUsername();
        if(isset($user) && !is_null($user)) {
            $this->setModifiedBy(UserStatus::getUsername());
        }else{
            $this->setModifiedBy('hms');
        }

        // If the object is new, set the 'created' fields
        if($this->getId() == 0) {
            $this->setCreatedOn(time());
            if(isset($user) && !is_null($user)) {
                $this->setCreatedBy(UserStatus::getUsername());
            }else{
                $this->setCreatedBy('hms');
            }
        }
    }

    /**
     * Uses the ActivityLog class to log the submission of this application
     */
    public function log()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        // Determine which user name to use as the current user
        $username = UserStatus::getUsername();

        if(isset($username) && !is_null($username)) {
            HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_SUBMITTED_APPLICATION, $username, 'Term: ' . $this->getTerm());
        } else {
            HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_SUBMITTED_APPLICATION, 'hms', 'Term: ' . $this->getTerm());
        }
    }

    /**
     * Delets this application from the database.
     *
     * @throws DatabaseException
     * @return boolean
     */
    public function delete()
    {
        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return TRUE;
    }

    /**
     * Reports 'this' application to Banner
     */
    public function reportToBanner()
    {
        PHPWS_Core::initModClass('hms', 'SOAP.php');

        try {
            $soap = SOAP::getInstance();
            $result = $soap->reportApplicationReceived($this->getUsername(), $this->getTerm());
        }catch(Exception $e) {
            // Send an email notification
            PHPWS_Core::initCoreClass('Mail.php');
            $send_to = array();
            $send_to[] = 'jbooker@tux.appstate.edu';
            $send_to[] = 'jtickle@tux.appstate.edu';

            $mail = new PHPWS_Mail;

            $mail->addSendTo($send_to);
            $mail->setFrom('hms@tux.appstate.edu');
            $mail->setSubject('HMS Application Error!');

            $body = "Username: {$this->getUsername()}\n";
            $mail->setMessageBody($body);
            $result = $mail->send();

            throw $e; // rethrow the exception it
        }

        // Log the fact that the application was sent to banner
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_APPLICATION_REPORTED, UserStatus::getUsername());
    }

    /**
     * Returns the Student object who this appllication is for
     * @return Student
     */
    public function getStudent()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        return StudentFactory::getStudentByBannerId($this->getBannerId(), $this->getTerm());
    }

    /**
     * Returns a nicely formatted string with the "type" of this application
     *
     * @return String
     */
    public function getPrintableAppType()
    {
        switch($this->application_type) {
            case 'fall':
                return "Fall";
                break;
            case 'spring':
                return "Spring";
                break;
            case 'summer':
                return "Summer";
                break;
            case 'lottery':
                return "Re-application";
                break;
            case 'offcampus_waiting_list':
                return "Open Waiting-list";
                break;
            default:
                return "Unknown";
                break;
        }
    }

    /**
     * Returns the fields for this HousingApplication parent class. Usually called by overriding methods in subclasses (e.g. SummerApplication).
     *
     * @return Array Array of fields for this HousingApplication.
     */
    protected function unassignedStudentsFields()
    {
        $fields = array();
        $fields['banner_id']         = $this->getBannerId();
        $fields['username']          = $this->getUsername();
        $fields['gender']            = HMS_Util::formatGender($this->getGender());
        $fields['application_term']  = Term::toString($this->getApplicationTerm(), TRUE);
        $fields['student_type']      = HMS_Util::formatType($this->getStudentType());
        $fields['meal_plan']         = HMS_Util::formatMealOption($this->getMealPlan());

        $fields['created_on']        = HMS_Util::get_long_date($this->getCreatedOn());

        $roommate = HMS_Roommate::get_confirmed_roommate($this->getUsername(), $this->getTerm());
        if(!is_null($roommate)) {
            $fields['roommate'] = $roommate->getFullName();
            $fields['roommate_id'] = $roommate->getBannerId();
        } else {
            $fields['roommate'] = '';
            $fields['roommate_id'] = '';
        }

        return $fields;
    }

    /**
     * Marks an application as cancelled.
     *
     * Valid values for $reasonKey are defined in defines.php,
     * and listed in HousingApplication::getCancellationReasons().
     *
     * @param integer $reasonKey
     */
    public function cancel($reasonKey)
    {
        $this->cancelled = 1;
        $this->cancelled_by = Current_User::getUsername();
        $this->cancelled_on = time();

        $reasons = self::getCancellationReasons();

        if($reasonKey == "0" || !array_key_exists($reasonKey, $reasons)) {
            throw new InvalidArgumentException('Invalid cancellation reason key.');
        }

        $this->cancelled_reason = $reasonKey;

        // Log that this happened
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_CANCEL_HOUSING_APPLICATION, Current_User::getUsername(), Term::toString($this->getTerm()) . ': ' . $reasons[$reasonKey]);
    }


    /******************
     * Static Methods *
    ******************/

    /**
     * Checks to see if a application already exists for the given username.
     * If so, it returns the true, otherwise it returns false.
     * If no term is given, then the "current term" is used.
     *
     * The 'cancelled' parameter is optional. If set to true, then this method will
     * return true for cancelled applications. If false (default), then this method will
     * ignore cancelled applications.
     *
     */
    public static function checkForApplication($username, $term, $cancelled = FALSE)
    {
        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('username', $username, 'ILIKE');

        $db->addWhere('term', $term);

        if(!$cancelled) {
            $db->addWhere('cancelled', 0);
        }

        $result = $db->select('row');

        if(PEAR::isError($result)) {
            throw new DatabaseException($result->toString());
        }

        if(sizeof($result) > 0) {
            return $result;
        }else{
            return FALSE;
        }
    }

    // TODO move this to the HousingApplicationFactory class, perhaps?
    // TODO make this static too
    /**
     * Returns a HousingApplication for the given user name.
     *
     * @deprecated
     * @see HousingApplicationFactory::getAppByStudent()
     * @param unknown_type $username
     * @param unknown_type $term
     * @param unknown_type $applicationType
     * @throws DatabaseException
     * @throws InvalidArgumentException
     */
    public function getApplicationByUser($username, $term, $applicationType = null)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'WaitingListApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $student = StudentFactory::getStudentByUsername($username, $term);

        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('username', $username);
        $db->addWhere('term', $term);

        if(!is_null($applicationType)) {
            $db->addWhere('application_type', $applicationType);
        }

        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if($result == null) {
            return null;
        }

        switch($result['application_type']) {
            case 'fall':
                $app = new FallApplication($result['id']);
                break;
            case 'spring':
                $app = new SpringApplication($result['id']);
                break;
            case 'summer':
                $app = new SummerApplication($result['id']);
                break;
            case 'lottery':
                $app = new LotteryApplication($result['id']);
                break;
            case 'offcampus_waiting_list':
                $app = new WaitingListApplication($result['id']);
                break;
            default:
                throw new InvalidArgumentException('Unknown application type: ' . $result['application_type']);
        }

        return $app;
    }

    /**
     * Returns an array of HousingApplication objects, one object for each application the
     * given student has completed. All parameters are optional.
     * Returns false if the request cannot be compelted for any reason.
     *
     * TODO depricate this and do it better
     *
     * @param string $username
     * @param string $banner_id
     * @param string $term
     * @return multitype:Ambigous <NULL, FallApplication>
     */
    public static function getAllApplications($username = null, $banner_id = null, $term = null)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');

        $db = new PHPWS_DB('hms_new_application');

        if(!is_null($banner_id)) {
            $db->addWhere('banner_id', $banner_id);
        }

        if(!is_null($username)) {
            $db->addWhere('username', $username, 'ILIKE');
        }

        if(!is_null($term)) {
            $db->addWhere('term', $term);
        }

        $result = $db->select();

        $apps = array();

        foreach($result as $app) {
            $apps[] = HousingApplicationFactory::getApplicationById($app['id']);
        }

        return $apps;
    }

    /**
     * Returns an array (indexed by term) of HousingApplication objects for the given student.
     * It does *not* convert these into the child classes/sub-types. You just get
     * the general HousingApplication type objects.
     *
     * @param Student $student
     * @throws InvalidArgumentException
     * @throws DatabaseException
     * @return Array Array of HousingApplication objects for the given user.
     */
    public static function getAllApplicationsForStudent(Student $student)
    {
        $db = new PHPWS_DB('hms_new_application');

        if(!isset($student) || empty($student) || is_null($student)) {
            throw new InvalidArgumentException('Missing/invalid student.');
        }

        $db->addWhere('banner_id', $student->getBannerId());

        $result = $db->getObjects('HousingApplication');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }


        // Re-index the applications using the term as the key
        $appsByTerm = array();
        if(isset($result) && !is_null($result)) {
            foreach($result as $app) {
                $appsByTerm[$app->getTerm()] = $app;
            }
        }

        return $appsByTerm;
    }

    /**
     * Returns all freshmen application for the given term.
     *
     * @param unknown $term
     * @return boolean|unknown
     */
    public static function getAllFreshmenApplications($term)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');

        $sem = Term::getTermSem($term);

        $db = new PHPWS_DB('hms_new_application');

        // Add 'where' clause for term and student type
        $db->addWhere('term', $term);
        $db->addWhere('student_type', TYPE_FRESHMEN);

        for($i = 1; $i < func_num_args(); $i++) {
            $db->addOrder(func_get_arg($i));
        }

        // Add the appropriate join, based on the term
        if($sem == TERM_FALL) {
            $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_fall_application', 'id', 'id');
            $result = $db->getObjects('FallApplication');
        } else if($term == TERM_SPRING) {
            $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_spring_application', 'id', 'id');
            $result = $db->getObjects('SpringApplication');
        } else if($term == TERM_SUMMER1 || $term == TERM_SUMMER2) {
            $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_summer_application', 'id', 'id');
            $result = $db->getObjects('SummerApplication');
        }

        if(PEAR::isError($result)) {
            return FALSE;
        }

        return $result;

    }

    /**
     * Returns applications for all unassigned freshmen.
     *
     * @param unknown $term
     * @param unknown $gender
     * @throws InvalidTermException
     * @throws DatabaseException
     * @return multitype:unknown
     */
    public static function getUnassignedFreshmenApplications($term, $gender)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('student_type', 'F');
        $db->addWhere('term', $term);
        $db->addWhere('withdrawn', 0);
        $db->addWhere('cancelled', 0);
        //        $db->addWhere('gender', $gender);

        // Add join for extra application fields (sub-class fields)
        switch(Term::getTermSem($term)) {
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                PHPWS_Core::initModClass('hms', 'SummerApplication.php');
                $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_summer_application', 'id', 'id');
                $db->addColumn('hms_new_application.*');
                //TODO addColumns for joined table
                $result = $db->getObjects('SummerApplication');
                break;
            case TERM_FALL:
                PHPWS_Core::initModClass('hms', 'FallApplication.php');
                $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_fall_application', 'id', 'id');
                // Add columns for joined table
                $db->addColumn('hms_new_application.*');
                $db->addColumn('hms_fall_application.lifestyle_option');
                $db->addColumn('hms_fall_application.preferred_bedtime');
                $db->addColumn('hms_fall_application.room_condition');
                $result = $db->getObjects('FallApplication');
                break;
            case TERM_SPRING:
                PHPWS_Core::initModClass('hms', 'SpringApplication.php');
                $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_spring_application', 'id', 'id');
                $db->addColumn('hms_new_application.*');
                //TODO addColumns for joined table
                $result = $db->getObjects('SpringApplication');
                break;
            default:
                PHPWS_Core::initModClass('hms', 'exception/InvalidTermException.php');
                throw new InvalidTermException($term);
        }

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        // The following is a hack to overcome shortcomings in the Database class.  What should happen
        // is a left outer join on (SELECT id, asu_username FROM hms_assignment WHERE term=201040)
        // where id is null.

        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('term', $term);
        $db->addColumn('asu_username');
        $assignments = $db->select('col');

        $newresult = array();

        for($count = 0; $count < count($result); $count++) {
            $app = $result[$count];
            if(!in_array($app->username, $assignments)) {
                //unset($result[$count]);
                $newresult[$app->username] = $app;
            }
        }

        return $newresult;
    }

    /**
     * Returns an array of the terms which this student can potentially apply for.
     *
     * @param Student $student
     * @return multitype:multitype:number unknown  multitype:number string
     */
    public static function getAvailableApplicationTermsForStudent(Student $student)
    {
        $availableTerms = array();

        $applicationTerm = $student->getApplicationTerm();
        $sem = Term::getTermSem($applicationTerm);

        switch($sem) {
            case TERM_SPRING:
            case TERM_FALL:
                $availableTerms[] = array('term'=>$applicationTerm, 'required'=>1);
                break;
            case TERM_SUMMER1:
                $availableTerms[] = array('term'=>$applicationTerm, 'required'=>1);
                $summer2Term = Term::getNextTerm($applicationTerm);
                $availableTerms[] = array('term'=>$summer2Term, 'required'=>0);
                $fallTerm = Term::getNextTerm($summer2Term);
                $availableTerms[] = array('term'=>$fallTerm, 'required'=>1);
                break;
            case TERM_SUMMER2:
                $availableTerms[] = array('term'=>$applicationTerm, 'required'=>1);
                $fallTerm = Term::getNextTerm($applicationTerm);
                $availableTerms[] = array('term'=>$fallTerm, 'required'=>1);
                break;
        }

        return $availableTerms;
    }

    /**
     * Returns an array of term which the student *must* apply for.
     *
     * @param Student $student
     * @return multitype:|multitype:Ambigous <multitype:multitype:number, multitype:multitype:number unknown  multitype:number string  >
     */
    public static function getRequiredApplicationTermsForStudent(Student $student)
    {
        // Special case for Transfer students: They're not required to apply for any term
        if($student->getType() == TYPE_TRANSFER) {
            return array();
        }

        $availableTerms = self::getAvailableApplicationTermsForStudent($student);

        $requiredTerms = array();

        foreach($availableTerms as $term) {
            if($term['required'] == 1) {
                $requiredTerms[] = $term;
            }
        }

        return $requiredTerms;
    }

    /**
     * Determines whether or not a student has applied for all terms for which he/she is required
     * to apply for according to his/her application term.
     *
     * If the student has not applied for all required terms, the terms which he/she still needs to
     * apply for are returned in an array. If the student has applied for all required terms, then
     * the returned array will be empty.
     *
     * @param Student $student
     * @return Array list of terms which the student still needs to apply for
     */
    public static function checkAppliedForAllRequiredTerms(Student $student)
    {
        $requiredTerms = self::getRequiredApplicationTermsForStudent($student);

        $existingApplications = self::getAllApplicationsForStudent($student);

        $needToApplyFor = array();

        foreach($requiredTerms as $term) {
            // Check if a housing application exists for this student in this term
            if(!isset($existingApplications[$term['term']])) {
                $needToApplyFor[] = $term['term'];
            }
        }

        return $needToApplyFor;
    }

    /************************
     * Accessors & Mutators *
    ************************/

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getBannerId()
    {
        return $this->banner_id;
    }

    public function setBannerId($id)
    {
        $this->banner_id = $id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getStudentType()
    {
        return $this->student_type;
    }

    public function setStudentType($type)
    {
        $this->student_type = $type;
    }

    public function getApplicationTerm()
    {
        return $this->application_term;
    }

    public function setApplicationTerm($term)
    {
        $this->application_term = $term;
    }

    public function getCellPhone()
    {
        return $this->cell_phone;
    }

    public function setCellPhone($phone)
    {
        $this->cell_phone = $phone;
    }

    public function getMealPlan()
    {
        return $this->meal_plan;
    }

    public function setMealPlan($plan)
    {
        $this->meal_plan = $plan;
    }

    public function getPhysicalDisability()
    {
        return $this->physical_disability;
    }

    public function setPhysicalDisability($physical)
    {
        $this->physical_disability = $physical;
    }

    public function getPsychDisability()
    {
        return $this->psych_disability;
    }

    public function setPsychDisability($psych)
    {
        $this->psych_disability = $psych;
    }

    public function getMedicalNeed()
    {
        return $this->medical_need;
    }

    public function setMedicalNeed($medical)
    {
        $this->medical_need = $medical;
    }

    public function getGenderNeed($gender)
    {
        return $this->gender_need;
    }

    public function setGenderNeed($gender)
    {
        $this->gender_need = $gender;
    }

    public function getInternational()
    {
        return $this->international;
    }

    public function setInternational($intl)
    {
        $this->international = $intl;
    }

    public function setCreatedOn($timestamp)
    {
        $this->created_on = $timestamp;
    }

    public function getCreatedOn()
    {
        return $this->created_on;
    }

    public function getCreatedBy()
    {
        return $this->created_by;
    }

    public function setCreatedBy($username)
    {
        $this->created_by = $username;
    }

    public function getModifiedBy()
    {
        return $this->modified_by;
    }

    public function setModifiedBy($username)
    {
        $this->modified_by = $username;
    }

    public function getModifiedOn()
    {
        return $this->modified_on;
    }

    public function setModifiedOn($timestamp)
    {
        $this->modified_on = $timestamp;
    }

    public function getCancelled()
    {
        return $this->cancelled;
    }

    public function setCancelled($status)
    {
        $this->cancelled = $status;
    }

    public function isCancelled()
    {
        if($this->getCancelled() == 1) {
            return true;
        }else{
            return false;
        }
    }

    public function getCancelledReason()
    {
        return $this->cancelled_reason;
    }

    public function setCancelledReason($reason)
    {
        $this->cancelled_reason = $reason;
    }

    public function getCancelledBy()
    {
        return $this->cancelled_by;
    }

    public function setCancelledBy($user)
    {
        $this->cancelled_by = $user;
    }

    public function getCancelledOn()
    {
        return $this->cancelled_on;
    }

    public function setCancelledOn($time)
    {
        $this->cancelled_on = $time;
    }

    public function getApplicationType()
    {
        return $this->application_type;
    }

    public function setApplicationType($type)
    {
        $this->application_type = $type;
    }


    /*******
     * Emergency Contact Info
    */

    public function getEmergencyContactName()
    {
        return $this->emergency_contact_name;
    }

    public function setEmergencyContactName($name)
    {
        $this->emergency_contact_name = $name;
    }

    public function getEmergencyContactRelationship()
    {
        return $this->emergency_contact_relationship;
    }

    public function setEmergencyContactRelationship($relation)
    {
        $this->emergency_contact_relationship = $relation;
    }

    public function getEmergencyContactPhone()
    {
        return $this->emergency_contact_phone;
    }

    public function setEmergencyContactPhone($phone)
    {
        $this->emergency_contact_phone = $phone;
    }

    public function getEmergencyContactEmail()
    {
        return $this->emergency_contact_email;
    }

    public function setEmergencyContactEmail($email)
    {
        $this->emergency_contact_email = $email;
    }

    public function getEmergencyMedicalCondition()
    {
        return $this->emergency_medical_condition;
    }

    public function setEmergencyMedicalCondition($cond)
    {
        $this->emergency_medical_condition = $cond;
    }

    public function getMissingPersonName()
    {
        return $this->missing_person_name;
    }

    public function setMissingPersonName($name)
    {
        $this->missing_person_name = $name;
    }

    public function getMissingPersonRelationship()
    {
        return $this->missing_person_relationship;
    }

    public function setMissingPersonRelationship($relation)
    {
        $this->missing_person_relationship = $relation;
    }

    public function getMissingPersonPhone()
    {
        return $this->missing_person_phone;
    }

    public function setMissingPersonPhone($phone)
    {
        $this->missing_person_phone = $phone;
    }

    public function getMissingPersonEmail()
    {
        return $this->missing_person_email;
    }

    public function setMissingPersonEmail($email)
    {
        $this->missing_person_email = $email;
    }


    /**
     * Returns the withdrawn flag.
     *
     * @deprecated
     */
    public function getWithdrawn()
    {
        return $this->withdrawn;
    }

    /**
     * Returns boolean based on the withdrawn flag.
     *
     * @deprecated
     */
    public function isWithdrawn()
    {
        if($this->withdrawn == 1) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * Sets the withdrawn flag.
     *
     * @deprecated
     */
    public function setWithdrawn($status)
    {
        $this->withdrawn = $status;
    }

    /**
     * Returns an associative array of cancellation reasons to string descriptions.
     *
     * @return multitype:string
     */
    public static function getCancellationReasons()
    {
        return array(
                CANCEL_BEFORE_JULY   => 'Cancel Before July',
                CANCEL_AFTER_JULY    => 'Cancel After July',
                CANCEL_WITHDRAWN     => 'Withdrawn',
                CANCEL_INTENT        => 'Intent Not to Return',
                CANCEL_BEFORE_ASSIGN => 'Cancel Before Assignment'
        );
    }
}
?>
