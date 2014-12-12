<?php
PHPWS_Core::initModClass('hms', 'HMS_Item.php');

/**
 * Learning Community objects for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Learning_Community extends HMS_Item
{
    public $id=NULL;
    public $community_name=NULL;
    public $abbreviation;
    public $capacity;
    public $hide;

    public $allowed_student_types; //A string containing a character for each allowed student type, maxLen() == 16;
    public $allowed_reapplication_student_types;
    public $members_reapply; // Indicates whether current members of the community are always allowed to reapply, regardless of student type
    public $extra_info; // A text field, show to the student when the RLC is selected

    // Move-in time IDs specific to this community (used in assignment notifications)
    public $f_movein_time_id;
    public $t_movein_time_id;
    public $c_movein_time_id;

    public $freshmen_question;
    public $returning_question;

    public $terms_conditions;

    public function __construct($id = 0)
    {
        $this->construct($id);
    }

    public function getDb()
    {
        return new PHPWS_DB('hms_learning_communities');
    }

    public function allowStudentType($student_type){
        if(!is_string($student_type)
        || strlen($student_type) != 1
        || stripos($this->allowed_student_types, $student_type) === false
        ){
            return false;
        }

        return true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(){
        $this->id = $id;
    }

    /**
     * @deprecated
     */
    public function set_id($id)
    {
        $this->id = $id;
    }

    /**
     * @deprecated
     */
    public function get_id()
    {
        return $this->id;
    }

    public function set_community_name($name)
    {
        $this->community_name = $name;
    }

    public function getName()
    {
        return $this->community_name;
    }

    public function get_community_name()
    {
        return $this->getName();
    }

    public function set_abbreviation($abb)
    {
        $this->abbreviation = $abb;
    }

    public function get_abbreviation()
    {
        return $this->abbreviation;
    }

    public function set_capacity($cap)
    {
        $this->capacity = $cap;
    }

    public function get_capacity()
    {
        return $this->capacity;
    }

    public function getAllowedStudentTypes(){
        return $this->allowed_student_types;
    }

    public function setAllowedStudentTypes($types){
        $this->allowed_student_types = $types;
    }

    public function getAllowedReapplicationStudentTypes(){
        return $this->allowed_reapplication_student_types;
    }

    public function setAllowedReapplicationStudentTypes($types){
        $this->allowed_reapplication_student_types = $types;
    }

    public function getMembersReapply(){
        return $this->members_reapply;
    }

    public function setMembersReapply($apply){
        $this->members_reapply = $apply;
    }

    public function getFreshmenQuestion()
    {
        return $this->freshmen_question;
    }

    public function setFreshmenQuestion($question)
    {
        $this->freshmen_question = $question;
    }

    public function getReturningQuestion()
    {
        return $this->returning_question;
    }

    public function getTermsConditions()
    {
        return $this->terms_conditions;
    }

    public function setTermsConditions($text){
        $this->terms_conditions = $text;
    }

    public function setReturningQuestion($question){
        $this->returning_question = $question;
    }

    public function getFreshmenMoveinTime(){
        return $this->f_movein_time_id;
    }

    public function setFreshmenMoveinTime($movein_time){
        $this->f_movein_time_id = $movein_time;
    }

    public function getContinuingMoveinTime(){
        return $this->c_movein_time_id;
    }

    public function setContinuingMoveinTime($movein_time){
        $this->c_movein_time_id = $movein_time;
    }

    public function getTransferMoveinTime(){
        return $this->t_movein_time_id;
    }

    public function setTransferMoveinTime($movein_time){
        $this->t_movein_time_id = $movein_time;
    }

    //TODO depricate this crap
    public function set_variables()
    {
        if(isset($_REQUEST['id']) && $_REQUEST['id'] != NULL) $this->set_id($_REQUEST['id']);
        $this->set_community_name($_REQUEST['community_name']);
        $this->set_abbreviation($_REQUEST['abbreviation']);
        $this->set_capacity($_REQUEST['capacity']);
    }

    public function rowTags(){
        return array('ACTIONS' => "<a href=\"index.php?module=hms&action=ShowAddRlc&id={$this->id}\">Edit</a>");
    }

    /**
     * Get a JSON encoded view of the learning community.
     *
     * @param int $id The id of the learning community to return
     * @return json JSON encoded object
     */
    public function JSONLearningCommunity($id)
    {
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            die();
        }
        if(is_numeric($id)){
            $db = new PHPWS_DB('hms_learning_communities');
            $db->addWhere('id', $id);
            $result = $db->select();

            if(PHPWS_Error::logIfError($result)){
                throw new DatabaseException($result->toString());
            }

            return json_encode($result);
        }
    }

    /**
     * Returns an associative array containing the list of RLC abbreviations keyed by their id.
     */
    public static function getRLCListAbbr($student_type = NULL)
    {
        $db = new PHPWS_DB('hms_learning_communities');

        $db->addColumn('id');
        $db->addColumn('abbreviation');
        if(!is_null($student_type) && strlen($student_type) == 1)
        $db->addColumn('allowed_student_types', "%{$student_type}%", 'ilike');

        $result = $db->select('assoc');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /**
     * Returns an associative array containing the list of RLCs using their full names, keyed by their id.
     * @deprecated
     * @see RlcFactory
     */
    public static function getRlcList($hidden = NULL, $student_type = NULL)
    {
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        if(!is_null($student_type) && strlen($student_type) == 1)
        $db->addWhere('allowed_student_types', "%{$student_type}%", 'ilike');

        if($hidden === FALSE){
            $db->addWhere('hide', 0);
        }

        $db->addOrder('community_name ASC');

        $rlc_choices = $db->select('assoc');

        if(PHPWS_Error::logIfError($rlc_choices)){
            throw new DatabaseException($rlc_choices->toString());
        }

        return $rlc_choices;
    }

    /**
     * Returns an associative array containing the list of RLCs using their full names,
     * keyed by their id, that a student is allowed to re-apply for.
     */
    public function getRLCListReapplication($hidden = NULL, $student_type = NULL)
    {
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        if(!is_null($student_type) && strlen($student_type) == 1)
        $db->addWhere('allowed_reapplication_student_types', "%{$student_type}%", 'ilike');

        if($hidden === FALSE){
            $db->addWhere('hide', 0);
        }

        $rlc_choices = $db->select('assoc');

        if(PHPWS_Error::logIfError($rlc_choices)){
            throw new DatabaseException($rlc_choices->toString());
        }

        return $rlc_choices;
    }

    /**
     * Exports the pending RLC applications into a CSV file.
     * Looks in $_REQUEST for which RLC to export.
     */
    public function rlc_application_export()
    {
    }

    /**
     * Exports the completed RLC assignments.
     */
    public function rlc_assignment_export()
    {
        if( !Current_User::allow('hms', 'view_rlc_applications') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $db = new PHPWS_DB('hms_learning_communities');
        $db->addColumn('community_name');
        $db->addWhere('id',$_REQUEST['rlc_list']);
        $title = $db->select('one');

        $filename = $title . '-assignments-' . date('Ymd') . ".csv";

        // setup the title and headings
        $buffer = $title . "\n";
        $buffer .= '"last_name","first_name","middle_name","gender","email"' . "\n";

        // get the list of assignments
        $db = new PHPWS_DB('hms_learning_community_assignment');
        $db->addColumn('user_id');
        $db->addWhere('hms_learning_community_assignment.rlc_id',$_REQUEST['rlc_list']); # select assignments only for the given RLC
        $users = $db->select();

        foreach($users as $user){
            $sinfo = HMS_SOAP::get_student_info($user['user_id']);
            $buffer .= '"' . $sinfo->last_name . '",';
            $buffer .= '"' . $sinfo->first_name . '",';
            $buffer .= '"' . $sinfo->middle_name . '",';
            $buffer .= '"' . $sinfo->gender . '",';
            $buffer .= '"' . $user['user_id'] . '@appstate.edu' . '"' . "\n";
        }

        //Download file
        if(ob_get_contents())
        print('Some data has already been output, can\'t send file');
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
        header('Content-Type: application/force-download');
        else
        header('Content-Type: application/octet-stream');
        if(headers_sent())
        print('Some data has already been output to browser, can\'t send file');
        header('Content-Length: '.strlen($buffer));
        header('Content-disposition: attachment; filename="'.$filename.'"');
        echo $buffer;
        die();
    }
}

/**
 * Empty constructor child class for restoring RLC objects from the database.
 *
 * @author Jeremy Booker
 * @package hms
 */
class RestoredRlc extends HMS_Learning_Community {
    public function __construct(){}
}
?>
