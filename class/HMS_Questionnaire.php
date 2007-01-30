<?php

/**
 * The HMS_Questionnaire class
 * Implements the Questionnaire object and methods to load/save
 * questionnaires from the database.
 * 
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Questionnaire {

    var $id;

    var $hms_student_id;
    var $student_status;
    var $term_classification;
    var $gender;
    var $meal_option;
    var $lifestyle_option;
    var $preferred_bedtime;
    var $room_condition;
    var $in_relationship;
    var $currently_employed;
    var $rlc_interest;

    var $created_on;
    var $created_by;

    var $deleted = 0;
    var $deleted_by;
    var $deleted_on;
    

    /**
    * Constructor
    * Set $hms_student_id equal to the ASU email of the student you want
    * to create/load a questionnaire for. Otherwise, the student currently
    * logged in (session) is used.
    */
    function HMS_Questionnaire($hms_student_id = NULL)
    {

        if(isset($hms_student_id)){
            $this->setStudentID($hms_student_id);
        }else{
            $this->setStudentID($_SESSION['asu_username']);
        }
        
        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_Questionnaire()','Caught error from init');
            return $result;
        }
    }

    function init()
    {
        # Check if a questionnaire for this user and semester already exists.
        $result = HMS_Questionnaire::check_for_questionnaire();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','init',"Caught error from check_for_questionnaire.");
            #return "<i>ERROR!</i><br />Could not check for existing questionnaire!<br />";
            return $result;
        }
        
        # If a questionnaire exists, then load it's data into this object. 
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
        $this->setRelationship($result['in_relationship']);
        $this->setEmployed($result['currently_employed']);
        $this->setRlcInterest($result['rlc_interest']);
        $this->setCreatedOn($result['created_on']);
        $this->setDeleted($result['deleted']);
        $this->setDeletedBy($result['deleted_by']);
        $this->setDeletedOn($result['deleted_on']);

        return $result;
    }
    
    /**
     * Crates a new questionnaire object from $_REQUEST data and save it to the database.
     */
    function save_questionnaire()
    {
        $question = &new HMS_Questionnaire();
        
        $question->setStudentStatus($_REQUEST['student_status']);
        $question->setTermClassification($_REQUEST['classification_for_term']);
        $question->setGender($_REQUEST['gender_type']);
        $question->setMealOption($_REQUEST['meal_option']);
        $question->setLifestyle($_REQUEST['lifestyle_option']);
        $question->setPreferredBedtime($_REQUEST['preferred_bedtime']);
        $question->setRoomCondition($_REQUEST['room_condition']);
        $question->setRelationship($_REQUEST['relationship']);
        $question->setEmployed($_REQUEST['employed']);
        $question->setRlcInterest($_REQUEST['rlc_interest']);

        $result = $question->save();
        
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','Caught error from Questionnaire::save()');
            $error = "<i>Error!</i><br />Could not create/update your questionnaire!<br />";
            return $error;
        }else{
            $success  = "Your questionnaire was successfully saved.<br /><br />";
            $success .= "You may logout or view your questionnaire responses.<br /><br />";
            $success .= PHPWS_Text::secureLink(_('View My Questionnaire'), 'hms', array('type'=>'student', 'op'=>'review_questionnaire'));
            $success .= "<br /><br />";
            $success .= PHPWS_Text::moduleLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));
            return $success;
        }
    }

    /**
     * Saves the current Questionnaire object to the database.
     */
    function save()
    {

        $db = &new PHPWS_DB('hms_questionnaire');
        $db->addValue('student_status',$this->getStudentStatus());
        $db->addValue('term_classification',$this->getTermClassification());
        $db->addValue('gender',$this->getGender());
        $db->addValue('meal_option',$this->getMealOption());
        $db->addValue('lifestyle_option',$this->getLifestyle());
        $db->addValue('preferred_bedtime',$this->getPreferredBedtime());
        $db->addValue('room_condition',$this->getRoomCondition());
        $db->addValue('in_relationship',$this->getRelationship());
        $db->addValue('currently_employed',$this->getEmployed());
        $db->addValue('rlc_interest',$this->getRlcInterest());
        $db->addValue('deleted',$this->getDeleted());
        $db->addValue('deleted_by',$this->getDeletedBy());
        $db->addValue('deleted_on',$this->getDeletedOn());
        
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
            PHPWS_Error::log($result,'hms','save_questionnaire',"Could not insert/update questionnaire for user: {$_SESSION['asu_username']}");
            return $result;
        }else{
            return TRUE;
        }
    }

    /**
     * Checks to see if a questionnaire already exists for the objects current $hms_user_id.
     * If so, it returns the ID of that questionnaire record, otherwise it returns false.
     */
    function check_for_questionnaire($asu_username = NULL)
    {
        $db = &new PHPWS_DB('hms_questionnaire');
        if(isset($asu_username)) {
            $db->addWhere('hms_student_id',$asu_username,'ILIKE');
        } else {
            $db->addWhere('hms_student_id',$this->getStudentID(),'ILIKE');
        }
        $db->addWhere('created_on',HMS::get_current_year(),'>=');
        $db->addWhere('deleted',0,'=');

        $result = $db->select('row');
        
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','check_for_questionnare',"asu_username:{$_SESSION['asu_username']}");
            return $result;
        }
        
        if(sizeof($result) > 1){
            return $result;
        }else{
            return FALSE;
        }
    }

    
    /*
     * Displays the given user's questionnaire.
     * If no user specified, defaults to current user.
     */
    function show_questionnaire($asu_username = null){

        if(!isset($asu_username)){
            $asu_username = $_SESSION['asu_username'];
        }

        PHPWS_Core::initModClass('hms', 'HMS_Questionnaire.php');
        $questionnaire = new HMS_Questionnaire($asu_username);
        

        $tpl['TITLE']   = 'Residence Hall Application';
        if(isset($message)){
            $tpl['MESSAGE'] = $message;
        }
        $tpl['REDO']    = PHPWS_Text::secureLink("Return to Menu", 'hms', array('type'=>'hms', 'op'=>'main'));
        $tpl['NEWLINES']= "<br /><br />";
          
        if($questionnaire->getStudentStatus() == 1) $tpl['STUDENT_STATUS'] = "New Freshman";
        else if ($questionnaire->getStudentStatus() == 2) $tpl['STUDENT_STATUS'] = "Transfer";

        if($questionnaire->getTermClassification() == 1) $tpl['CLASSIFICATION_FOR_TERM'] = "Freshman";
        else if($questionnaire->getTermClassification() == 2) $tpl['CLASSIFICATION_FOR_TERM'] = "Sophomore";
        else if($questionnaire->getTermClassification() == 3) $tpl['CLASSIFICATION_FOR_TERM'] = "Junior";
        else if($questionnaire->getTermClassification() == 4) $tpl['CLASSIFICATION_FOR_TERM'] = "Senior";
          
        if($questionnaire->getGender() == 0) $tpl['GENDER_TYPE'] = "Female";
        else if($questionnaire->getGender() == 1) $tpl['GENDER_TYPE'] = "Male";
            
        if($questionnaire->getMealOption() == 1) $tpl['MEAL_OPTION'] = "Low";
        else if($questionnaire->getMealOption() == 2) $tpl['MEAL_OPTION'] = "Medium";
        else if($questionnaire->getMealOption() == 3) $tpl['MEAL_OPTION'] = "High";
        else if($questionnaire->getMealOption() == 4) $tpl['MEAL_OPTION'] = "Super";
           
        if($questionnaire->getLifestyle() == 1) $tpl['LIFESTYLE_OPTION'] = "Single Gender";
        else if($questionnaire->getLifestyle() == 2) $tpl['LIFESTYLE_OPTION'] = "Co-Ed";
            
        if($questionnaire->getPreferredBedtime() == 1) $tpl['PREFERRED_BEDTIME'] = "Early";
        else if($questionnaire->getPreferredBedtime() == 2) $tpl['PREFERRED_BEDTIME'] = "Late";

        if($questionnaire->getRoomCondition() == 1) $tpl['ROOM_CONDITION'] = "Clean";
        else if($questionnaire->getRoomCondition() == 2) $tpl['ROOM_CONDITION'] = "Dirty";
            
        if($questionnaire->getRelationship() == 0) $tpl['RELATIONSHIP'] = "No"; 
        else if($questionnaire->getRelationship() == 1) $tpl['RELATIONSHIP'] = "Yes"; 
        else if($questionnaire->getRelationship() == 2) $tpl['RELATIONSHIP'] = "Not Disclosed"; 
            
        if($questionnaire->getEmployed() == 0) $tpl['EMPLOYED'] = "No";
        else if($questionnaire->getEmployed() == 1) $tpl['EMPLOYED'] = "Yes";
        else if($questionnaire->getEmployed() == 2) $tpl['EMPLOYED'] = "Not Disclosed";
             
        if($questionnaire->getRlcInterest() == 0) $tpl['RLC_INTEREST_1'] = "No";
        else if($questionnaire->getRlcInterest() == 1) $tpl['RLC_INTEREST_1'] = "Yes";
       
        $master['QUESTIONNAIRE']  = PHPWS_Template::process($tpl, 'hms', 'student/student_questionnaire.tpl');
        return PHPWS_Template::process($master,'hms','student/student_questionnaire_combined.tpl');
        
    }
   
    /**
     * Uses the forms class to display the questionnaire form or
     * a confirmation page.
     */
    function display_questionnaire_form($view = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        if($view != NULL) {
            return HMS_Form::display_questionnaire_results();
        } else {
            return HMS_Form::begin_questionnaire();
        }
    }

    /**
     * Uses the forms class to display the questionnaire search page.
     */
    function display_questionnaire_search()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::questionnaire_search_form();
    }

    /**
     * Does the actual searching of questionnaires.
     */
    function questionnaire_search()
    {
        $tags = array();

        $tags['RESULTS'] = HMS_Questionnaire::questionnaire_search_pager();

        return PHPWS_Template::process($tags, 'hms', 'student/questionnaire_search_results.tpl');
    }

    /**
     * Sets up the pager object for searching questionnairs.
     */
    function questionnaire_search_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['USERNAME']   = _('Username');
        $pageTags['FIRST_NAME'] = _('First Name');
        $pageTags['LAST_NAME']  = _('Last Name');
        $PageTags['ACTIONS']    = _('Action');

        $pager = &new DBPager('hms_questionnaire','HMS_Questionnaire');

        $pager->addWhere('hms_questionnaire.hms_student_id',$_REQUEST['asu_username'],'ILIKE');
        $pager->db->addOrder('hms_student_id','ASC');

        $pager->setModule('hms');
        $pager->setTemplate('student/questionnaire_search_pager.tpl');
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
        $tags['ACTIONS'] = PHPWS_Text::secureLink('[View]', 'hms',array('type'=>'student','op'=>'show_questionnaire','user'=>$this->getStudentID())) . " [Select as Roomate]";

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

    function setRelationship($relation){
        $this->in_relationship = $relation;
    }

    function getRelationship(){
        return $this->in_relationship;
    }

    function setEmployed($employed){
        $this->currently_employed = $employed;
    }

    function getEmployed(){
        return $this->currently_employed;
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
}
