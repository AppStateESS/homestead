<?php


class HousingApplication {

    public $id = 0;

    public $term; // The term which this application is for

    public $banner_id;
    public $username;

    public $gender;
    public $student_type;  // 'F', 'C', 'T', etc...
    public $application_term; // The term the student started school
    public $cell_phone;
    public $meal_plan;

    /**
     * Special needs flags
     */
    public $physical_disability;
    public $psych_disability;
    public $medical_need;
    public $gender_need;

    public $created_on; // unix timestamp when the application as first saved
    public $created_by; // user name of the person who created this application

    public $modified_on; // unix timestamp when the application was last modified
    public $modified_by; // user name of the person who last modified this application

    /**
     * Set to 'true' by the withdrawn search process. Should always be false by default.
     */
    public $withdrawn;

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
     */
    public function __construct($term = NULL, $banner_id = NULL, $username = NULL, $gender = NULL, $student_type = NULL, $application_term = NULL, $cell_phone = NULL, $meal_plan = NULL, $physical_disability = NULL, $psych_disability = NULL, $gender_need = NULL, $medical_need = NULL){

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

        $this->setWithdrawn(false);
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
        if($this->id == 0){
            return;
        }

        $db = new PHPWS_DB('hms_new_application');
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
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
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $this->log();

        return true;
    }

    /**
     * Sets the last modified by/on fields and, if necessary, the created by/on fields
     */
    public function stamp(){
        # Set the last modified time
        $this->setModifiedOn(time());

        # Sets the 'last modified by' field according to who's logged in
        $this->setModifiedBy(UserStatus::getUsername());

        # If the object is new, set the 'created' fields
        if($this->getId() == 0){
            $this->setCreatedOn(time());
            $this->setCreatedBy(UserStatus::getUsername());
        }
    }

    /**
     * Uses the ActivityLog class to log the submission of this application
     */
    public function log()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        # Determine which user name to use as the current user
            $username = UserStatus::getUsername();

        HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_SUBMITTED_APPLICATION, $username, 'Term: ' . $this->getTerm());
    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
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
        
        try{
            $soap = SOAP::getInstance();
            $result = $soap->reportApplicationReceived($this->getUsername(), $this->getTerm());
        }catch(Exception $e){
            // Send an email notification
            PHPWS_Core::initCoreClass('Mail.php');
            $send_to = array();
            $send_to[] = 'jbooker@tux.appstate.edu';
            $send_to[] = 'jtickle@tux.appstate.edu';

            $mail = &new PHPWS_Mail;

            $mail->addSendTo($send_to);
            $mail->setFrom('hms@tux.appstate.edu');
            $mail->setSubject('HMS Application Error!');

            $body = "Username: {$this->getUsername()}\n";
            $mail->setMessageBody($body);
            $result = $mail->send();
             
            throw $e; // rethrow the exception it
        }

        # Log the fact that the application was sent to banner
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_APPLICATION_REPORTED, UserStatus::getUsername());
    }

    /*
     * Returns the table row tags for the 'unassigned applications report' in
     * HMS_Reports.php
     */
    public function unassignedApplicantsRows()
    {
        $tpl = array();
        $tpl['BANNER_ID']       = $this->getBannerId();
        $tpl['USERNAME']        = $this->getUsername();
        $tpl['GENDER']          = HMS_Util::formatGender($this->getGender());
        $tpl['STUDENT_TYPE']    = HMS_Util::formatType($this->getStudentType());
        $tpl['APP_TERM']        = Term::toString($this->getApplicationTerm(), TRUE);
        $tpl['MEAL']            = HMS_Util::formatMealOption($this->getMealPlan());
        $tpl['ROOMMATE']        = HMS_Roommate::get_confirmed_roommate($this->getUsername(), $this->getTerm());
        $tpl['ACTIONS']         = '[' . PHPWS_Text::secureLink('Assign', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$this->getUsername()), '_blank') . ' ]';

        return $tpl;
    }

    public function unassignedApplicantsCSV()
    {
        $tpl = array();
        $tpl['BANNER_ID']       = $this->getBannerId();
        $tpl['USERNAME']        = $this->getUsername();
        $tpl['GENDER']          = HMS_Util::formatGender($this->getGender());
        $tpl['STUDENT_TYPE']    = HMS_Util::formatType($this->getStudentType());
        $tpl['APP_TERM']        = Term::toString($this->getApplicationTerm(), TRUE);
        $tpl['MEAL']            = HMS_Util::formatMealOption($this->getMealPlan());
        $tpl['ROOMMATE']        = HMS_Roommate::get_confirmed_roommate($this->getUsername(), $this->getTerm());

        return $tpl;
    }


    /******************
     * Static Methods *
     ******************/

    /**
     * Checks to see if a application already exists for the given username.
     * If so, it returns the true, otherwise it returns false.
     * If no term is given, then the "current term" is used.
     *
     * The 'withdrawn' parameter is optional. If set to true, then this method will
     * return true for withdrawn applications. If false (default), then this method will
     * ignore withdrawn applications.
     */
    public static function checkForApplication($username, $term, $withdrawn = FALSE)
    {
        $db = &new PHPWS_DB('hms_new_application');
        $db->addWhere('username',$username,'ILIKE');

        $db->addWhere('term', $term);

        if(!$withdrawn){
            $db->addWhere('withdrawn', 0);
        }

        $result = $db->select('row');

        if(PEAR::isError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if(sizeof($result) > 0){
            return $result;
        }else{
            return FALSE;
        }
    }

    /**
     *
     */
    function getApplicationByUser($username, $term)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

        $student = StudentFactory::getStudentByUsername($username, $term);

        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('username', $username);
        $db->addWhere('term', $term);

        $result = $db->select('one');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if($result == NULL){
            return NULL;
        }

        # If the student is type 'C', return a LotteryApplication object
        if($student->getType() == TYPE_CONTINUING){
            $app = new LotteryApplication($result);
            return $app;
        }

        $sem = Term::getTermSem($term);
        $app = NULL;

        switch($sem){
            case TERM_FALL:
                $app = new FallApplication($result);
                break;
            case TERM_SPRING:
                $app = new SpringApplication($result);
                break;
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $app = new SummerApplication($result);
                break;
        }

        return $app;
    }

    /**
     * Returns an array of HousingApplication objects, one object for each application the
     * given student has completed. All parameters are optional.
     * Returns false if the request cannot be compelted for any reason.
     */
    public static function getAllApplications($username = NULL, $banner_id = NULL, $term = NULL){

        $db = new PHPWS_DB('hms_new_application');

        if(!is_null($banner_id)){
            $db->addWhere('banner_id', $banner_id);
        }

        if(!is_null($username)){
            $db->addWhere('username', $username);
        }

        if(!is_null($term)){
            $db->addWhere('term', $term);
        }

        $result = $db->getObjects('HousingApplication');

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return false;
        }

        return $result;
    }

    public static function getAllFreshmenApplications($term){
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');

        $sem = Term::getTermSem($term);

        $db = new PHPWS_DB('hms_new_application');

        # Add 'where' clause for term and student type
        $db->addWhere('term', $term);
        $db->addWhere('student_type', TYPE_FRESHMEN);

        for($i = 1; $i < func_num_args(); $i++){
            $db->addOrder(func_get_arg($i));
        }

        # Add the appropriate join, based on the term
        if($sem == TERM_FALL){
            $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_fall_application', 'id', 'id');
            $result = $db->getObjects('FallApplication');
        }else if($term == TERM_SPRING){
            $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_spring_application', 'id', 'id');
            $result = $db->getObjects('SpringApplication');
        }else if($term == TERM_SUMMER1 || $term == TERM_SUMMER2){
            $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_summer_application', 'id', 'id');
            $result = $db->getObjects('SummerApplication');
        }

        if(PEAR::isError($result)){
            return FALSE;
        }

        return $result;

    }

    /**
     * Returns a list of application terms that can be applied for at the same
     * time for the given entry term, and whether or not an application for that term is required
     * (i.e. a student with an application_term of 200920 must apply for 200920, 200940, and may
     * optionally apply for 200930.)
     *
     * In the returned array, 'app_term' is the application term which was given. 'term' is a term
     * which may be applied (or required to be applied) for by a student with the given 'app_term'.
     *
     * Term list is returned in ascending order (earliest term first).
     *
     * @param integer Term to check
     * @return array $arr[$index] = array('app_term' => <term>, 'term' => <term>, 'required'=> 0|1);
     */
    public static function getValidApplicationTerms($term){
        //TODO make this use ApplicationFeatures class
        $db = new PHPWS_DB('hms_term_applications');
        $db->addWhere('app_term', $term);
        $db->addOrder('term asc');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $result;
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
        $requiredTerms = HousingApplication::getValidApplicationTerms($student->getApplicationTerm());

        $needToApplyFor = array();
         
        foreach($requiredTerms as $t){
            // Skip this term if it's not required
            if($t['required'] == 0){
                continue;
            }
             
            // Check if a housing application exists for this student in this term
            if(!HousingApplication::checkForApplication($student->getUsername(), $t['term'])){
                $needToApplyFor[] = $t['term'];
            }
        }

        return $needToApplyFor;
    }

    /************************
     * Accessors & Mutators *
     ************************/

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getTerm(){
        return $this->term;
    }

    public function setTerm($term){
        $this->term = $term;
    }

    public function getBannerId(){
        return $this->banner_id;
    }

    public function setBannerId($id){
        $this->banner_id = $id;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getGender(){
        return $this->gender;
    }

    public function setGender($gender){
        $this->gender = $gender;
    }

    public function getStudentType(){
        return $this->student_type;
    }

    public function setStudentType($type){
        $this->student_type = $type;
    }

    public function getApplicationTerm(){
        return $this->application_term;
    }

    public function setApplicationTerm($term){
        $this->application_term = $term;
    }

    public function getCellPhone(){
        return $this->cell_phone;
    }

    public function setCellPhone($phone){
        $this->cell_phone = $phone;
    }

    public function getMealPlan(){
        return $this->meal_plan;
    }

    public function setMealPlan($plan){
        $this->meal_plan = $plan;
    }

    public function getPhysicalDisability(){
        return $this->physical_disability;
    }

    public function setPhysicalDisability($physical){
        $this->physical_disability = $physical;
    }

    public function getPsychDisability(){
        return $this->psych_disability;
    }

    public function setPsychDisability($psych){
        $this->psych_disability = $psych;
    }

    public function getMedicalNeed(){
        return $this->medical_need;
    }

    public function setMedicalNeed($medical){
        $this->medical_need = $medical;
    }

    public function getGenderNeed($gender){
        return $this->gender_need;
    }

    public function setGenderNeed($gender){
        $this->gender_need = $gender;
    }

    public function setCreatedOn($timestamp){
        $this->created_on = $timestamp;
    }

    public function getCreatedOn(){
        return $this->created_on;
    }

    public function getCreatedBy(){
        return $this->created_by;
    }

    public function setCreatedBy($username){
        $this->created_by = $username;
    }

    public function getModifiedBy(){
        return $this->modified_by;
    }

    public function setModifiedBy($username){
        $this->modified_by = $username;
    }

    public function getModifiedOn(){
        return $this->modified_on;
    }

    public function setModifiedOn($timestamp){
        $this->modified_on = $timestamp;
    }

    public function getWithdrawn(){
        return $this->withdrawn;
    }

    public function setWithdrawn($status){
        $this->withdrawn = $status;
    }
}
?>
