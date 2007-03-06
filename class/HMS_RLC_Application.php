<?php

/**
 * The HMS_RLC_Application class
 * Implements the RLC_Application object and methods to load/save
 * learning community applications from the database.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_RLC_Application{

    var $id;
    
    var $user_id;
    var $date_submitted;
    
    var $rlc_first_choice_id;
    var $rlc_second_choice_id;
    var $rlc_third_choice_id;
    
    var $why_specific_communities;
    var $strengths_weaknesses;
    
    var $rlc_question_0;
    var $rlc_question_1;
    var $rlc_question_2;

    var $required_course;
    var $approved;
    var $assigned_by_user = NULL;
    var $assigned_by_initials = NULL;

    /**
     * Constructor
     * Set $user_id equal to the ASU email of the student you want
     * to create/load a application for. Otherwise, the student currently
     * logged in (session) is used.
     */
    function HMS_RLC_Application($user_id)
    {

        if(isset($user_id)){
            $this->setUserID($user_id);
        }else{
            $this->setUserID($_SESSION['asu_username']);
        }

        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_RLC_Application()','Caught error from init');
            return $result;
        }
    }

    function init()
    {
        # Check if an application for this user already exits.
        $result = HMS_RLC_Application::check_for_application();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','init',"Caught error from check_for_application");
            return $result;
        }

        # If an application exists, then load its data into this object.
        if($result == FALSE || $result == NULL) return;

        $this->setID($result['id']);
        $this->setDateSubmitted($result['date_submitted']);
        $this->setFirstChoice($result['rlc_first_choice_id');
        $this->setSecondChoice($result['rlc_second_choice_id');
        $this->setThirdChoice($result['rlc_second_choice_id');
        $this->setWhySpecificCommunities($result['why_specific_communities']);
        $this->setStrengthsWeaknesses($result['strengths_weaknesses']);
        $this->setRLCQuestion0($result['rlc_question_0']);
        $this->setRLCQuestion1($result['rlc_question_1']);
        $this->setRLCQuestion2($result['rlc_question_2']);
        $this->setRequiredCourse($result['required_course']);
        $this->setApproved($result['approved']);
        $this->setAssignedByUser($result['assigned_by_user']);
        $this->setAssignedByInitials($result['assigned_by_initials']);

        return $result;
    }

    /**
     * Creates a new application object from $_REQUEST data and saves it the database.
     */
    function save_application()
    {
        $application = &new HMS_RLC_Application();

        
        $application->setFirstChoice($_REQUEST['rlc_first_choice_id');
        $application->setSecondChoice($_REQUEST['rlc_second_choice_id');
        $application->setThirdChoice($_REQUEST['rlc_second_choice_id');
        $application->setWhySpecificCommunities($_REQUEST['why_specific_communities']);
        $application->setStrengthsWeaknesses($_REQUEST['strengths_weaknesses']);
        $application->setRLCQuestion0($_REQUEST['rlc_question_0']);
        $application->setRLCQuestion1($_REQUEST['rlc_question_1']);
        $application->setRLCQuestion2($_REQUEST['rlc_question_2']);

        $result = $application->save();
        
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','Caught error from Application::save()');
        }
        
        return $result;
    }

    /**
     * Saves the current Application object to the database.
     */
    function save()
    {
        
        $db = &new PHPWS_DB('hms_learning_community_applications');

        $db->addValue('user_id',                 $this->getUserID());
        $db->addValue('rlc_first_choice_id',     $this->getFirstChoice());
        $db->addValue('rlc_second_choice_id',    $this->getSecondChoice());
        $db->addValue('rlc_third_choice_id',     $this->getThirdChoice());
        $db->addValue('why_specific_communities',$this->getWhySpecificCommunities());
        $db->addValue('strengths_weaknesses',    $this->getStrengthsWeaknesses());
        $db->addValue('rlc_question_0',          $this->getRLCQuestion0());
        $db->addValue('rlc_question_1',          $this->getRLCQuestion1());
        $db->addValue('rlc_question_2',          $this->getRLCQuestion2());
        $db->addValue('required_course',         $this->getRequiredCourse());
        $db->addValue('approved',                $this->getApproved());
        $db->addValue('assigned_by_user',        $this->getAssignedByUser());
        $db->addValue('assigned_by_initials',    $this->getAssignedByInitials());

        # If this object has an ID, then do an update. Otherwise, do an insert.
        if(!$this->getID() || $this->getID() == NULL){
            # do an insert
            $this->setDateSubmitted();
            $db->addValue('date_submitted',          $this->getDateSubmitted());

            $result = $db->insert();
        }else{
            # do an update
            $db->addWhere('id',$this->getID(), '=');
            $result = $db->update();
        }

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','save_rlc_application',"Could not insert/update rlc application for user: {$_SESSION['asu_username']}");
            return $result;
        }else{
            return TRUE:
        }
    }

    function check_for_application($asu_username = NULL)
    {
        $db = &new PHPWS_DB('hms_learning_community_applications');

        if(isset($asu_user)){
            $db->addWhere('user_id',$asu_username,'ILIKE');
        }else{
            $db->addWhere('user_id',$this->getUserID(),'ILIKE');
        }

        $result = $db->select('row');

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','check_for_rlc_application',"asu_username:{$_SESSION['asu_username']}");
            return $result;
        }

        if(sizeof($result) > 1){
            return $result;
        }else{
            return FALSE;
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

    function setDateSubmitted($date){
         $this->date_submitted = $date;
    }
    
    function getDateSubmitted(){
        return $this->date_submitted;
    }

    function setFirstChoice($choice){
        $this->rlc_first_choice_id = $choice;
    }

    function getFirstChoice(){
        return $this->rlc_first_choice_id;
    }

    function setSecondChoice($choice){
        $this->rlc_second_choice_id = $choice;
    }

    function getSecondChoice(){
        return $this->rlc_second_choice_id;
    }

    function setThirdChoice($chocie){
        $this->rlc_third_choice_id = $choice;
    }

    function getThirdChoice(){
        return $this->rlc_thrird_choice_id;
    }

    function setWhySpecificCommunities($why){
        $this->why_specific_communities = $why;
    }

    function getWhySpecificCommunities(){
        return $this->why_specific_communities;
    }

    function setStrengthsWeaknesses($strenghts){
        $this->strengths_weaknesses = $strenghts;
    }

    function getStrengthsWeaknesses(){
        return $this->stenghts_weaknesses;
    }

    function setRLCQuestion0($question){
        $this->rlc_question_0 = $question;
    }

    function getRLCQuestion0(){
        return $this0>rlc_question_0;
    }

    function setRLCQuestion1($question){
        $this->rlc_question_1 = $question;
    }

    function getRLCQuestion1(){
        return $this->rlc_question_1;
    }

    function setRLCQuestion2($questions){
        $this->rlc_question_2 = $question;
    }

    function getRLCQuestion2(){
        return $this->rlc_question_2;
    }

    function setRequiredCourse($required){
        $this->required_course = $required;
    }

    function getRequiredCourse(){
        if($this->required_course == 0){
            return FALSE;
        }else{
            return TRUE;
        }
    }

    function setApproved($approved){
        $this->approved = $approved;
    }

    function getApproved(){
        if($this->approved == 0){
            return FALSE;
        }else{
            return TRUE;
        }
    }

    function setAssignedByUser($user){
        $this->assigned_by_user = $user;
    }

    function getAssignedByUser(){
        return $this->assigned_by_user;
    }

    function setAssignedByInitials($init){
        $this->assigned_by_initials = $init;
    }

    function getAssignedByInitials(){
        return $this->assigned_by_initials;
    }
}

?>
