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
   
    /**
     * Uses the forms class to display the questionnaire.
     */
    function display_questionnaire($view = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        if($view != NULL) {
            return HMS_Form::display_questionnaire_results();
        } else {
            return HMS_Form::begin_questionnaire();
        }
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
