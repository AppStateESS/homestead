<?php

/**
 * The HMS_Application class
 * Implements the Application object and methods to load/save
 * applications from the database.
 * 
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Application {

    var $id;

    var $hms_student_id;
    var $student_status;
    var $term_classification;
    var $gender;
    var $meal_option;
    var $lifestyle_option;
    var $preferred_bedtime;
    var $room_condition;
    var $rlc_interest;
    var $agreed_to_terms;

    var $created_on;
    var $created_by;

    var $deleted = 0;
    var $deleted_by;
    var $deleted_on;
    

    /**
    * Constructor
    * Set $hms_student_id equal to the ASU email of the student you want
    * to create/load a application for. Otherwise, the student currently
    * logged in (session) is used.
    */
    function HMS_Application($hms_student_id = NULL)
    {

        if(isset($hms_student_id)){
            $this->setStudentID($hms_student_id);
        }else{
            $this->setStudentID($_SESSION['asu_username']);
        }
        
        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_Application()','Caught error from init');
            return $result;
        }
    }

    function init()
    {
        # Check if an application for this user and semester already exists.
        $result = HMS_Application::check_for_application();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','init',"Caught error from check_for_application.");
            #return "<i>ERROR!</i><br />Could not check for existing application!<br />";
            return $result;
        }
        
        # If an application exists, then load its data into this object. 
        if($result == FALSE || $result == NULL) return;
        
        $this->setID($result['id']);
        $this->setStudentID($result['hms_student_id']);
        $this->setStudentStatus($result['student_status']);
        $this->setTermClassification($result['term_classification']);
        $this->setGender($result['gender']);
        $this->setMealOption($result['meal_option']);
        $this->setLifestyle($result['lifestyle_option']);
        $this->setPreferredBedtime($result['preferred_bedtime']);
        $this->setRoomCondition($result['room_condition']);
        $this->setRlcInterest($result['rlc_interest']);
        $this->setCreatedOn($result['created_on']);
        $this->setDeleted($result['deleted']);
        $this->setDeletedBy($result['deleted_by']);
        $this->setDeletedOn($result['deleted_on']);
        $this->setAgreedToTerms($result['agreed_to_terms']);

        return $result;
    }
    
    /**
     * Creates a new application object from $_REQUEST data and saves it to the database.
     */
    function save_application()
    {
        $question = &new HMS_Application();
        
        $question->setStudentStatus($_REQUEST['student_status']);
        $question->setTermClassification($_REQUEST['classification_for_term']);
        $question->setGender($_REQUEST['gender_type']);
        $question->setMealOption($_REQUEST['meal_option']);
        $question->setLifestyle($_REQUEST['lifestyle_option']);
        $question->setPreferredBedtime($_REQUEST['preferred_bedtime']);
        $question->setRoomCondition($_REQUEST['room_condition']);
        $question->setRlcInterest($_REQUEST['rlc_interest']);
        $question->setAgreedToTerms($_REQUEST['agreed_to_terms']);

        $result = $question->save();
        
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','Caught error from Application::save()');
            $error = "<i>Error!</i><br />Could not create/update your application!<br />";
            return $error;
        }else{
            if($question->getRlcInterest() == 1) {
                PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
                return HMS_Learning_Community::show_rlc_application_form();
            } else {
                $success  = "Your application was successfully saved.<br /><br />";
                $success .= "You may logout or view your application responses.<br /><br />";
                $success .= PHPWS_Text::secureLink(_('View My Application'), 'hms', array('type'=>'student', 'op'=>'review_application'));
                $success .= "<br /><br />";
                $success .= PHPWS_Text::secureLink(_('Apply for a RLC'), 'hms', array('type'=>'student', 'op'=>'show_rlc_application_form'));
                $success .= "<br /><br />";
                $success .= PHPWS_Text::moduleLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));
                return $success;
            }
        }
    }

    /**
     * Saves the current Application object to the database.
     */
    function save()
    {

        $db = &new PHPWS_DB('hms_application');
        $db->addValue('student_status',$this->getStudentStatus());
        $db->addValue('term_classification',$this->getTermClassification());
        $db->addValue('gender',$this->getGender());
        $db->addValue('meal_option',$this->getMealOption());
        $db->addValue('lifestyle_option',$this->getLifestyle());
        $db->addValue('preferred_bedtime',$this->getPreferredBedtime());
        $db->addValue('room_condition',$this->getRoomCondition());
        $db->addValue('rlc_interest',$this->getRlcInterest());
        $db->addValue('deleted',$this->getDeleted());
        $db->addValue('deleted_by',$this->getDeletedBy());
        $db->addValue('deleted_on',$this->getDeletedOn());
        $db->addValue('agreed_to_terms',$this->getAgreedToTerms());
        
        # If this object has an ID, then do an update. Otherwise, do an insert.
        if(!$this->getID() || $this->getID() == NULL){
            # do an insert
            $this->setCreatedOn();
            $this->setCreatedBy($_SESSION['asu_username']);
            
            $db->addValue('hms_student_id',$this->getStudentID());
            $db->addValue('created_on',$this->getCreatedOn());
            $db->addValue('created_by', $this->getCreatedBy());
            
            $result = $db->insert();
        }else{
            # do an update
            $db->addWhere('id',$this->getID(),'=');
            $result = $db->update();
        }

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','save_application',"Could not insert/update application for user: {$_SESSION['asu_username']}");
            return $result;
        }else{
            return TRUE;
        }
    }

    /**
     * Checks to see if a application already exists for the objects current $hms_user_id.
     * If so, it returns the ID of that application record, otherwise it returns false.
     */
    function check_for_application($asu_username = NULL)
    {
        $db = &new PHPWS_DB('hms_application');
        if(isset($asu_username)) {
            $db->addWhere('hms_student_id',$asu_username,'ILIKE');
        } else {
            $db->addWhere('hms_student_id',$this->getStudentID(),'ILIKE');
        }
        $db->addWhere('created_on',HMS::get_current_year(),'>=');
        $db->addWhere('deleted',0,'=');

        $result = $db->select('row');
        
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','check_for_application',"asu_username:{$_SESSION['asu_username']}");
            return $result;
        }
        
        if(sizeof($result) > 1){
            return $result;
        }else{
            return FALSE;
        }
    }

    
    /*
     * Displays the given user's application.
     * If no user specified, defaults to current user.
     */
    function show_application($asu_username = null){

        if(!isset($asu_username)){
            $asu_username = $_SESSION['asu_username'];
        }

        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        $application = new HMS_Application($asu_username);
        

        $tpl['TITLE']   = 'Residence Hall Application';
        if(isset($message)){
            $tpl['MESSAGE'] = $message;
        }
        $tpl['REDO']    = PHPWS_Text::secureLink("Return to Menu", 'hms', array('type'=>'hms', 'op'=>'main'));
        $tpl['NEWLINES']= "<br /><br />";
          
        if($application->getStudentStatus() == 1) $tpl['STUDENT_STATUS'] = "New Freshman";
        else if ($application->getStudentStatus() == 2) $tpl['STUDENT_STATUS'] = "Transfer";

        if($application->getTermClassification() == 1) $tpl['CLASSIFICATION_FOR_TERM'] = "Freshman";
        else if($application->getTermClassification() == 2) $tpl['CLASSIFICATION_FOR_TERM'] = "Sophomore";
        else if($application->getTermClassification() == 3) $tpl['CLASSIFICATION_FOR_TERM'] = "Junior";
        else if($application->getTermClassification() == 4) $tpl['CLASSIFICATION_FOR_TERM'] = "Senior";
          
        if($application->getGender() == 0) $tpl['GENDER_TYPE'] = "Female";
        else if($application->getGender() == 1) $tpl['GENDER_TYPE'] = "Male";
            
        if($application->getMealOption() == 1) $tpl['MEAL_OPTION'] = "Low";
        else if($application->getMealOption() == 2) $tpl['MEAL_OPTION'] = "Medium";
        else if($application->getMealOption() == 3) $tpl['MEAL_OPTION'] = "High";
        else if($application->getMealOption() == 4) $tpl['MEAL_OPTION'] = "Super";
           
        if($application->getLifestyle() == 1) $tpl['LIFESTYLE_OPTION'] = "Single Gender";
        else if($application->getLifestyle() == 2) $tpl['LIFESTYLE_OPTION'] = "Co-Ed";
            
        if($application->getPreferredBedtime() == 1) $tpl['PREFERRED_BEDTIME'] = "Early";
        else if($application->getPreferredBedtime() == 2) $tpl['PREFERRED_BEDTIME'] = "Late";

        if($application->getRoomCondition() == 1) $tpl['ROOM_CONDITION'] = "Clean";
        else if($application->getRoomCondition() == 2) $tpl['ROOM_CONDITION'] = "Dirty";
            
        if($application->getRelationship() == 0) $tpl['RELATIONSHIP'] = "No"; 
        else if($application->getRelationship() == 1) $tpl['RELATIONSHIP'] = "Yes"; 
        else if($application->getRelationship() == 2) $tpl['RELATIONSHIP'] = "Not Disclosed"; 
            
        if($application->getEmployed() == 0) $tpl['EMPLOYED'] = "No";
        else if($application->getEmployed() == 1) $tpl['EMPLOYED'] = "Yes";
        else if($application->getEmployed() == 2) $tpl['EMPLOYED'] = "Not Disclosed";
             
        if($application->getRlcInterest() == 0) $tpl['RLC_INTEREST_1'] = "No";
        else if($application->getRlcInterest() == 1) $tpl['RLC_INTEREST_1'] = "Yes";
       
        $master['APPLICATION']  = PHPWS_Template::process($tpl, 'hms', 'student/student_application.tpl');
        return PHPWS_Template::process($master,'hms','student/student_application_combined.tpl');
        
    }
   
    /**
     * Uses the forms class to display the application form or
     * a confirmation page.
     */
    function display_application_form($view = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        if($view != NULL) {
            return HMS_Form::display_application_results();
        } else {
            return HMS_Form::begin_application();
        }
    }

    /**
     * Uses the forms class to display the application search page.
     */
    function display_application_search()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::application_search_form();
    }

    /**
     * Does the actual searching of applications.
     */
    function application_search()
    {
        $tags = array();

        $tags['RESULTS'] = HMS_Application::application_search_pager();

        return PHPWS_Template::process($tags, 'hms', 'student/application_search_results.tpl');
    }

    /**
     * Sets up the pager object for searching questionnairs.
     */
    function application_search_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['USERNAME']   = _('Username');
        $pageTags['FIRST_NAME'] = _('First Name');
        $pageTags['LAST_NAME']  = _('Last Name');
        $PageTags['ACTIONS']    = _('Action');

        $pager = &new DBPager('hms_application','HMS_Application');

        $pager->addWhere('hms_application.hms_student_id',$_REQUEST['asu_username'],'ILIKE');
        $pager->db->addOrder('hms_student_id','ASC');

        $pager->setModule('hms');
        $pager->setTemplate('student/application_search_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No matches found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);

        return $pager->get();
    }

    /* 
     *Sets up the row tags for the pager
     */
    function getPagerTags()
    {
        $tags['STUDENT_ID'] = $this->getStudentID();
        $tags['FIRST_NAME'] = "The first name goes here";
        $tags['LAST_NAME'] = "The last name goes here";
        $tags['ACTIONS'] = PHPWS_Text::secureLink('[View]', 'hms',array('type'=>'student','op'=>'show_application','user'=>$this->getStudentID())) . " [Select as Roomate]";

        return $tags;
    }


    /****************************
     * Accessor & Mutator Methods
     ****************************/

    function setID($id){
        $this->id = $id;
    }

    function getID(){
        return $this->id;
    }

    function setStudentID($id){
        $this->hms_student_id = $id;
    }

    function getStudentID(){
        return $this->hms_student_id;
    }

    function setStudentStatus($status){
        $this->student_status = $status;
    }

    function getStudentStatus(){
        return $this->student_status;
    }

    function setTermClassification($class){
        $this->term_classification = $class;
    }

    function getTermClassification(){
        return $this->term_classification;
    }

    function setGender($gender){
        $this->gender = $gender;
    }

    function getGender(){
        return $this->gender;
    }

    function setMealOption($meal){
        $this->meal_option = $meal;
    }

    function getMealOption(){
        return $this->meal_option;
    }

    function setLifestyle($style){
        $this->lifestyle_option = $style;
    }

    function getLifestyle(){
        return $this->lifestyle_option;
    }

    function setPreferredBedtime($time){
        $this->preferred_bedtime = $time;
    }

    function getPreferredBedtime(){
        return $this->preferred_bedtime;
    }

    function setRoomCondition($condition){
        $this->room_condition = $condition;
    }

    function getRoomCondition(){
        return $this->room_condition;
    }

    function setRlcInterest($interest){
        $this->rlc_interest = $interest;
    }

    function getRlcInterest(){
        return $this->rlc_interest;
    }

    function setCreatedOn($time = null){
        if($time == null){
            $this->created_on = mktime();
        }else{
            $this->created_on = $time;
        }
    }

    function getCreatedOn(){
        return $this->created_on;
    }

    function setCreatedBy($asu_username)
    {
        $this->created_by = $asu_username;
    }

    function getCreatedBy()
    {
        return $this->created_by;
    }

    function markDeleted($user){
        $this->setDeleted(1);
        $this->setDeletedBy($user);
        $this->setDeletedOn(mktime());
    }

    function setDeleted($status){
        $this->deleted = $status;
    }

    function getDeleted(){
        return $this->deleted;
    }

    function setDeletedBy($user){
        $this->deleted_by = $user;
    }

    function getDeletedBy(){
        return $this->deleted_by;
    }

    function setDeletedOn($time){
        $this->deleted_on = $time;
    }

    function getDeletedOn(){
        return $this->deleted_on;
    }

    function setAgreedToTerms($agreed){
        if($agreed == 0){
            $this->agreed_to_terms = FALSE;
        }else{
            $this->agreed_to_terms = TRUE;
        }
    }

    function getAgreedToTerms(){
        if($this->agreed_to_terms){
            return 1;
        }else{
            return 0;
        }
    }
}
