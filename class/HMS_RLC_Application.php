<?php
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
/**
 * The HMS_RLC_Application class
 * Implements the RLC_Application object and methods to load/save
 * learning community applications from the database.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

define('RLC_RESPONSE_LIMIT', 4096); // max number of characters allowed in the text areas on the RLC application

class HMS_RLC_Application{

    public $id;

    public $user_id;
    public $date_submitted;

    public $rlc_first_choice_id;
    public $rlc_second_choice_id;
    public $rlc_third_choice_id;

    public $why_specific_communities;
    public $strengths_weaknesses;

    public $rlc_question_0;
    public $rlc_question_1;
    public $rlc_question_2;

    public $hms_assignment_id = NULL;
    public $term = NULL;

    public $denied = 0;

    /**
     * Constructor
     * Set $user_id equal to the ASU email of the student you want
     * to create/load a application for. Otherwise, the student currently
     * logged in (session) is used.
     */
    public function HMS_RLC_Application($user_id = NULL, $term = NULL)
    {

        if(isset($user_id)){
            $this->setUserID($user_id);
        }else{
            return;
        }

        $result = $this->init($user_id, $term);
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_RLC_Application()','Caught error from init');
            return $result;
        }
    }

    public function delete()
    {
        if(!isset($this->id)) {
            return FALSE;
        }

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id',$this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $this->id = 0;

        return TRUE;
    }

    //TODO loadObject
    public function init($user_id = NULL, $term)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $student = StudentFactory::getStudentByUsername($user_id, $term);
        # Check if an application for this user already exits.
        $result = HMS_RLC_Application::check_for_application($user_id, $term);

        # If an application exists, then load its data into this object.
        if($result == FALSE || $result == NULL) return;

        $this->setID($result['id']);
        $this->setDateSubmitted($result['date_submitted']);
        $this->setFirstChoice($result['rlc_first_choice_id']);
        $this->setSecondChoice($result['rlc_second_choice_id']);
        $this->setThirdChoice($result['rlc_third_choice_id']);
        $this->setWhySpecificCommunities($result['why_specific_communities']);
        $this->setStrengthsWeaknesses($result['strengths_weaknesses']);
        $this->setRLCQuestion0($result['rlc_question_0']);
        $this->setRLCQuestion1($result['rlc_question_1']);
        $this->setRLCQuestion2($result['rlc_question_2']);
        $this->setAssignmentID($result['hms_assignment_id']);
        $this->setEntryTerm($result['term']);

        return $result;
    }

    /**
     * Saves the current Application object to the database.
     * TODO: saveObject
     */
    public function save()
    {
        //Ensure that the user is allowed to apply for all of their choices
        //before doing anything else
        $student = StudentFactory::getStudentByUsername($this->getUserID(), $this->term);
        //$choice1 = new HMS_Learning_Community #TODO, load student
        $db = new PHPWS_DB('hms_learning_community_applications');

        $db->addValue('user_id',                    $this->getUserID());
        $db->addValue('rlc_first_choice_id',        $this->getFirstChoice());
        $db->addValue('rlc_second_choice_id',       $this->getSecondChoice());
        $db->addValue('rlc_third_choice_id',        $this->getThirdChoice());
        $db->addValue('why_specific_communities',   $this->getWhySpecificCommunities());
        $db->addValue('strengths_weaknesses',       $this->getStrengthsWeaknesses());
        $db->addValue('rlc_question_0',             $this->getRLCQuestion0());
        $db->addValue('rlc_question_1',             $this->getRLCQuestion1());
        $db->addValue('rlc_question_2',             $this->getRLCQuestion2());
        $db->addValue('hms_assignment_id',          $this->getAssignmentID());
        $db->addValue('term',                       $this->term);
        $db->addValue('denied',                     $this->denied);

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

    /*****************
     * Static Methods *
     *****************/

    /**
     * Check to see if an application already exists for the specified user. Returns FALSE if no application exists.
     * If an application does exist, an associative array containing that row is returned. In the case of a db error, a PEAR
     * error object is returned.
     * @param include_denied Controls whether or not denied applications are returned
     */
    public function check_for_application($asu_username, $term, $include_denied = TRUE)
    {
        $db = new PHPWS_DB('hms_learning_community_applications');

        $db->addWhere('user_id',$asu_username,'ILIKE');

        $db->addWhere('term', $term);

        if(!$include_denied){
            $db->addWhere('denied', 0);
        }

        $result = $db->select('row');
        
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if(sizeof($result) > 1){
            return $result;
        }else{
            return FALSE;
        }
    }
    
    public function getApplicationById($id, $term){
        
        $app = new HMS_RLC_Application();

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id', $id);
        $result = $db->loadObject($app);
        
        return $app;
    }

    public function getAdminPagerTags()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'Term.php');

        $student = StudentFactory::getStudentByUsername($this->user_id, Term::getCurrentTerm());

        $rlc_list = HMS_Learning_Community::getRLCList();

        $tags = array();

        $tags['NAME']           = $student->getFullNameProfileLink();
        
        $rlcCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
        $rlcCmd->setUsername($this->getUserID());
        
        $tags['1ST_CHOICE']     = $rlcCmd->getLink($rlc_list[$this->getFirstChoice()],'_blank');
        if(isset($rlc_list[$this->getSecondChoice()]))
        $tags['2ND_CHOICE'] = $rlc_list[$this->getSecondChoice()];
        if(isset($rlc_list[$this->getThirdChoice()]))
        $tags['3RD_CHOICE'] = $rlc_list[$this->getThirdChoice()];
        $tags['FINAL_RLC']      = HMS_RLC_Application::generateRLCDropDown($rlc_list,$this->getID());
        $tags['CLASS']          = $student->getClass();
        //        $tags['SPECIAL_POP']    = ;
        //        $tags['MAJOR']          = ;
        //        $tags['HS_GPA']         = ;
        $tags['GENDER']         = $student->getGender();
        $tags['DATE_SUBMITTED'] = date('d-M-y',$this->getDateSubmitted());
        
        $denyCmd = CommandFactory::getCommand('DenyRlcApplication');
        $denyCmd->setApplicationId($this->getID());
        
        $tags['DENY']           = $denyCmd->getLink('Deny');

        return $tags;
    }

    public function applicantsReport()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $term = Term::getSelectedTerm();

        $sinfo            = HMS_SOAP::get_student_info($this->user_id);
        $application_date = isset($this->date_submitted) ? HMS_Util::get_long_date($this->date_submitted) : 'Error with the submission date';

        $roomie = NULL;
        if(HMS_Roommate::has_confirmed_roommate($this->user_id, $term)){
            $roomie = HMS_Roommate::get_Confirmed_roommate($this->user_id, $term);
        }
        elseif(HMS_Roommate::has_roommate_request($this->user_id, $term)){
            $roomie = HMS_Roommate::get_unconfirmed_roommate($this->user_id, $term) . ' *pending* ';
        }

        $row['last_name']           = $sinfo->last_name;
        $row['first_name']          = $sinfo->first_name;
        $row['middle_name']         = $sinfo->middle_name;
        $row['gender']              = $sinfo->gender;
        $row['roommate']            = $roomie;
        $row['email']               = $this->user_id . '@appstate.edu';
        $row['second_choice']       = $this->getSecondChoice();
        $row['third_choice']        = $this->getThirdChoice();
        $row['major']               = 'N/A';                    //TODO: Plug this in from somewhere...
        $row['application_date']    = $application_date;
        $row['denied']              = (isset($this->denied) && $this->denied == 0) ? 'yes' : 'no';

        return $row;
    }

    public function denied_pager()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('hms_learning_community_applications', 'HMS_RLC_Application');

        $pager->db->addWhere('term', Term::getSelectedTerm());
        $pager->db->addWhere('denied', 1); // show only denied applications

        $pager->db->addColumn('hms_learning_community_applications.*');
        $pager->db->addColumn('hms_learning_communities.abbreviation');
        $pager->db->addWhere('hms_learning_community_applications.rlc_first_choice_id',
                             'hms_learning_communities.id','=');

        $pager->setModule('hms');
        $pager->setTemplate('admin/denied_rlc_app_pager.tpl');
        $pager->setEmptyMessage("No denied RLC applications exist.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle1"');
        $pager->addRowTags('getDeniedPagerTags');

        return $pager->get();
    }

    //TODO update this!!
    public function getDeniedPagerTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $student = StudentFactory::getStudentByUsername($this->user_id, $this->term);
        
        $tags = array();
        $rlc_list = HMS_Learning_Community::getRLCList();

        $tags['NAME']           = $student->getProfileLink();
        $tags['1ST_CHOICE']     = '<a href="./index.php?module=hms&type=rlc&op=view_rlc_application&username=' . $this->getUserID() . '" target="_blank">' . $rlc_list[$this->getFirstChoice()] . '</a>';
        if(isset($rlc_list[$this->getSecondChoice()]))
        $tags['2ND_CHOICE'] = $rlc_list[$this->getSecondChoice()];
        if(isset($rlc_list[$this->getThirdChoice()]))
        $tags['3RD_CHOICE'] = $rlc_list[$this->getThirdChoice()];
        $tags['CLASS']          = $student->getClass();
        $tags['GENDER']         = $student->getGender();
        $tags['DATE_SUBMITTED'] = date('d-M-y',$this->getDateSubmitted());
        
        $unDenyCmd = CommandFactory::getCommand('UnDenyRlcApplication');
        $unDenyCmd->setApplicationId($this->id);
        
        $tags['ACTION']         = $unDenyCmd->getLink('Un-Deny');

        return $tags;
    }

    /**
     * Generates a drop down menu using the RLC abbreviations
     */
    public function generateRLCDropDown($rlc_list,$application_id){

        $output = "<select name=\"final_rlc[$application_id]\">";

        $output .= '<option value="-1">None</option>';

        foreach ($rlc_list as $id => $rlc_name){
            $output .= "<option value=\"$id\">$rlc_name</option>";
        }

        $output .= '</select>';

        return $output;
    }

    /****************************
     * Accessor & Mutator Methods
     ****************************/

    public function setID($id){
        $this->id = $id;
    }

    public function getID(){
        return $this->id;
    }

    public function setUserID($user_id){
        $this->user_id = $user_id;
    }

    public function getUserID(){
        return $this->user_id;
    }

    public function setDateSubmitted($date = NULL){
        if(!isset($date)){
            $this->date_submitted = mktime();
        }else{
            $this->date_submitted = $date;
        }
    }

    public function getDateSubmitted(){
        return $this->date_submitted;
    }

    public function setFirstChoice($choice){
        $this->rlc_first_choice_id = $choice;
    }

    public function getFirstChoice(){
        return $this->rlc_first_choice_id;
    }

    public function setSecondChoice($choice){
        $this->rlc_second_choice_id = $choice;
    }

    public function getSecondChoice(){
        return $this->rlc_second_choice_id;
    }

    public function setThirdChoice($choice){
        $this->rlc_third_choice_id = $choice;
    }

    public function getThirdChoice(){
        return $this->rlc_third_choice_id;
    }

    public function setWhySpecificCommunities($why){
        $this->why_specific_communities = $why;
    }

    public function getWhySpecificCommunities(){
        return $this->why_specific_communities;
    }

    public function setStrengthsWeaknesses($strenghts){
        $this->strengths_weaknesses = $strenghts;
    }

    public function getStrengthsWeaknesses(){
        return $this->strengths_weaknesses;
    }

    public function setRLCQuestion0($question){
        $this->rlc_question_0 = $question;
    }

    public function getRLCQuestion0(){
        return $this->rlc_question_0;
    }

    public function setRLCQuestion1($question){
        $this->rlc_question_1 = $question;
    }

    public function getRLCQuestion1(){
        return $this->rlc_question_1;
    }

    public function setRLCQuestion2($question){
        $this->rlc_question_2 = $question;
    }

    public function getRLCQuestion2(){
        return $this->rlc_question_2;
    }

    public function setAssignmentID($id){
        $this->hms_assignment_id = $id;
    }

    public function getAssignmentID(){
        return $this->hms_assignment_id;
    }

    public function getEntryTerm(){
        return $this->term;
    }

    public function setEntryTerm($term){
        $this->term = $term;
    }
}

?>
