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
        if(PHPWS_Error::logIfError($db->loadObject($this))){
            $this->id = 0;
            return false;
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
            return false;
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
        if(Current_User::getUsername() == HMS_STUDENT_USER){
            $this->setModifiedBy($_SESSION['asu_username']);
        }else{
            $this->setModifiedBy(Current_User::getUsername());
        }

        # If the object is new, set the 'created' fields
        if($this->getId() == 0){
            $this->setCreatedOn(time());

            if(Current_User::getUsername() == HMS_STUDENT_USER){
                $this->setCreatedBy($_SESSION['asu_username']);
            }else{
                $this->setCreatedBy(Current_User::getUsername());
            }
        }
    }

    /**
     * Uses the ActivityLog class to log the submission of this application
     */
    public function log()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        # Determine which user name to use as the current user
        if(Current_User::getUsername() == HMS_STUDENT_USER){
            $username = $this->getUsername();
        }else{
            $username = Current_User::getUsername();
        }

        HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_SUBMITTED_APPLICATION, $username, 'Term: ' . $this->getTerm());
    }

     public function delete()
    {
        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)){
            return $result;
        }

        return TRUE;
    }

    /**
     * Reports 'this' application to Banner
     */
    public function reportToBanner()
    {
        $plancode = HMS_SOAP::get_plan_meal_codes($this->getUsername(), 'lawl', $this->getMealPlan());
        $result = HMS_SOAP::report_application_received($this->getUsername(), $this->getTerm(), $plancode['plan'], $plancode['meal']);

        # If there was an error it will have already been logged
        # but send out a notification anyway
        # TODO: Improve the notification system
        if($result > 0){
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
        }else{
            # Log the fact that the application was sent to banner
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            if(Current_User::getUsername() == HMS_STUDENT_USER){
                HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_APPLICATION_REPORTED, $this->getUsername());
            }else{
                HMS_Activity_Log::log_activity($this->getUsername(), ACTIVITY_APPLICATION_REPORTED, Current_User::getUsername());
            }
        }
    }

    /*
     * Returns the table row tags for the 'unassigned applications report' in
     * HMS_Reports.php
     */
    public function unassigned_applicants_rows()
    {
        $tpl = array();
        $tpl['BANNER_ID']       = $this->getBannerId();
        $tpl['USERNAME']        = $this->getUsername();
        $tpl['GENDER']          = HMS_Util::formatGender($this->getGender());
        $tpl['STUDENT_TYPE']    = HMS_Util::formatType($this->getStudentType());
        $tpl['APP_TERM']        = HMS_Term::term_to_text($this->getApplicationTerm(), TRUE);
        $tpl['MEAL']            = HMS_Util::formatMealOption($this->getMealPlan());
        $tpl['ROOMMATE']        = HMS_Roommate::get_confirmed_roommate($this->getUsername(), $this->getTerm());
        $tpl['ACTIONS']         = '[' . PHPWS_Text::secureLink('Assign', 'hms', array('type'=>'assignment', 'op'=>'show_assign_student', 'username'=>$this->getUsername()), '_blank') . ' ]';

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
    public static function checkForApplication($username = NULL, $term = NULL, $withdrawn = FALSE)
    {
        $db = &new PHPWS_DB('hms_new_application');
        if(isset($username)) {
            $db->addWhere('username',$username,'ILIKE');
        }
        
        if(isset($term)){
            $db->addWhere('term', $term);
        } else {
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $db->addWhere('term', HMS_Term::get_current_term());
        }

        if(!$withdrawn){
            $db->addWhere('withdrawn', 0);
        }
        
        $result = $db->select('row');

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','check_for_application',"username:{$_SESSION['asu_username']}");
            return FALSE;
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
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');

        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('username', $username);
        $db->addWhere('term', $term);

        $result = $db->select('one');

        if($result == NULL){
            return FALSE;
        }

        $sem = HMS_Term::get_term_sem($term);

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

    public static function getAllFreshmenApplications($term = NULL){
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');

        if(is_null($term)){
            $term = HMS_Term::get_selected_term();
        }

        $sem = HMS_Term::get_term_sem($term);
        
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
