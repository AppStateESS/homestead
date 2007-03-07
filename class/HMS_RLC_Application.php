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

    var $required_course = 0;
    var $approved = 0;
    var $assigned_by_user = NULL;
    var $assigned_by_initials = NULL;

    /**
     * Constructor
     * Set $user_id equal to the ASU email of the student you want
     * to create/load a application for. Otherwise, the student currently
     * logged in (session) is used.
     */
    function HMS_RLC_Application($user_id = NULL)
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
        $this->setFirstChoice($result['rlc_first_choice_id']);
        $this->setSecondChoice($result['rlc_second_choice_id']);
        $this->setThirdChoice($result['rlc_second_choice_id']);
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

        
        $application->setFirstChoice($_REQUEST['rlc_first_choice']);
        $application->setSecondChoice($_REQUEST['rlc_second_choice']);
        $application->setThirdChoice($_REQUEST['rlc_third_choice']);
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
            $db->addValue('date_submitted', $this->getDateSubmitted());

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
            return TRUE;
        }
    }

    function check_for_application($asu_username = NULL)
    {
        $db = &new PHPWS_DB('hms_learning_community_applications');

        if(isset($asu_user)){
            $db->addWhere('user_id',$asu_username,'ILIKE');
        }else{
            $db->addWhere('user_id',$_SESSION['asu_username'],'ILIKE');
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
    
    function rlc_application_admin_pager(){
        PHPWS_Core::initCoreClass('DBPager.php');

        $form = new PHPWS_Form;
        $form->addHidden('type','admin');
        $form->addHidden('op','rlc_assignments_submit');
        $form->addSubmit('Submit Changes');

        $tags = $form->getTemplate();

        $pager = &new DBPager('hms_learning_community_applications','HMS_RLC_Application');
        $pager->db->addOrder('date_submitted','ASC');

        $pager->setModule('hms');
        $pager->setTemplate('admin/rlc_assignments_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No pending RLC applications.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle1"');
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');

        return $pager->get();
    }

    function getAdminPagerTags(){

        $tags = array();

        $tags['NAME'] = HMS_SOAP::get_full_name_inverted($this->getUserID());
        $tags['1ST_CHOICE'] = ;
        $tags['2ND_CHOICE'] = ;
        $tags['3RD_CHOICE'] = ;
        $tags['FINAL_RLC'] = ;
        $tags['SPECIAL_POP'] = ;
        $tags['MAJOR'] = ;
        $tags['HS_GPA'] = ;
        $tags['GENDER'] = ;
        $tags['APPLY_DATE'] = ;
        $tags['COURSE_OK'] = ;
        $tags['FINAL_ASSIGN_BY'] = ;

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

    function setUserID($user_id){
        $this->user_id = $user_id;
    }

    function getUserID(){
        return $this->user_id;
    }

    function setDateSubmitted($date = NULL){
        if(!isset($date)){
            $this->date_submitted = mktime();
        }else{
            $this->date_submitted = $date;
        }
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

    function setThirdChoice($choice){
        $this->rlc_third_choice_id = $choice;
    }

    function getThirdChoice(){
        return $this->rlc_third_choice_id;
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
        return $this->strengths_weaknesses;
    }

    function setRLCQuestion0($question){
        $this->rlc_question_0 = $question;
    }

    function getRLCQuestion0(){
        return $this->rlc_question_0;
    }

    function setRLCQuestion1($question){
        $this->rlc_question_1 = $question;
    }

    function getRLCQuestion1(){
        return $this->rlc_question_1;
    }

    function setRLCQuestion2($question){
        $this->rlc_question_2 = $question;
    }

    function getRLCQuestion2(){
        return $this->rlc_question_2;
    }

    function setRequiredCourse($required){
        $this->required_course = $required;
    }

    function getRequiredCourse(){
        return $this->required_course;
    }

    function setApproved($approved){
        $this->approved = $approved;
    }

    function getApproved(){
        return $this->approved;
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
