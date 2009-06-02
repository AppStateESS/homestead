<?php

/**
 * The HMS_RLC_Application class
 * Implements the RLC_Application object and methods to load/save
 * learning community applications from the database.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

define('RLC_RESPONSE_LIMIT', 4096);

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

    var $hms_assignment_id = NULL;
    var $term = NULL;

    var $denied = 0;

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
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        if(!isset($this->id)) {
            return FALSE;
        }

        $db = &new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id',$this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }

        $this->id = 0;

        return TRUE;
    }

    public function init($user_id = NULL, $term = NULL)
    {
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $student = HMS_SOAP::get_student_info($user_id);
        # Check if an application for this user already exits.
        $result = HMS_RLC_Application::check_for_application($user_id, $term);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','init',"Caught error from check_for_application");
        }

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
     */
    public function save()
    {
        
        $db = &new PHPWS_DB('hms_learning_community_applications');

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
    * Creates a new application object from $_REQUEST data and saves it the database.
    */
    public function save_application()
    {
        $application = &new HMS_RLC_Application($_SESSION['asu_username']);

        $application->setUserID($_SESSION['asu_username']);
        $application->setFirstChoice($_REQUEST['rlc_first_choice']);
        if($_REQUEST['rlc_second_choice'] > -1){
            $application->setSecondChoice($_REQUEST['rlc_second_choice']);
        }
        if($_REQUEST['rlc_third_choice'] > -1){
            $application->setThirdChoice($_REQUEST['rlc_third_choice']);
        }
        $application->setWhySpecificCommunities($_REQUEST['why_specific_communities']);
        $application->setStrengthsWeaknesses($_REQUEST['strengths_weaknesses']);
        $application->setRLCQuestion0($_REQUEST['rlc_question_0']);
        $application->setEntryTerm($_SESSION['application_term']);
        
        if(isset($_REQUEST['rlc_question_1'])){
            $application->setRLCQuestion1($_REQUEST['rlc_question_1']);
        }else{
            $application->setRLCQuestion1(NULL);
        }

        if(isset($_REQUEST['rlc_question_2'])){
            $application->setRLCQuestion2($_REQUEST['rlc_question_2']);
        }else{
            $application->setRLCQuestion2(NULL);
        }

        $application->term = $_SESSION['application_term'];

        $result = $application->save();
        
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','Caught error from Application::save()');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_RLC_APP_SUBMITTED, Current_User::getUsername(), "Submitted an RLC application");
        
        return $result;
    }
    
    /**
    * Check to see if an application already exists for the specified user. Returns FALSE if no application exists.
    * If an application does exist, a db object containing that row is returned. In the case of a db error, a PEAR
    * error object is returned. 
    * @param include_denied Controls whether or not denied applications are returned
    */
    public function check_for_application($asu_username = NULL, $entry_term = NULL, $include_denied = TRUE)
    {
        $db = &new PHPWS_DB('hms_learning_community_applications');

        if(isset($asu_username)){
            $db->addWhere('user_id',$asu_username,'ILIKE');
        }else{
            $db->addWhere('user_id',$_SESSION['asu_username'],'ILIKE');
        }

        if(isset($entry_term)){
            $db->addWhere('term', $entry_term);
        } else {
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $db->addWhere('term', HMS_Term::get_current_term());
        }

        if(!$include_denied){
            $db->addWhere('denied', 0);
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
    
    /**
     * RLC Application pager for the RLC admin panel
     */
    public function rlc_application_admin_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('hms','HMS_Term.php');
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');

        $form = new PHPWS_Form;
        $form->addHidden('type','rlc');
        $form->addHidden('op','rlc_assignments_submit');
        $form->addSubmit('Submit Changes');
        $tags = $form->getTemplate();


        $pager = &new DBPager('hms_learning_community_applications','HMS_RLC_Application');
        $pager->db->addColumn('hms_learning_community_applications.*');
        $pager->db->addColumn('hms_learning_communities.abbreviation');
        // The 'addOrder' calls must not be used in order for the sort order buttons on the pager to work
        #$pager->db->addOrder('hms_learning_communities.abbreviation','ASC');
        #$pager->db->addOrder('hms_learning_community_applications.date_submitted', 'ASC');        
        //$pager->db->addOrder('user_id','ASC');
        $pager->db->addWhere('hms_learning_community_applications.rlc_first_choice_id',
                             'hms_learning_communities.id','=');
        $pager->db->addWhere('hms_assignment_id',NULL,'is');
        $pager->db->addWhere('term', HMS_Term::get_selected_term());
        $pager->db->addWhere('denied', 0); // Only show non-denied applications in this pager
        if( isset($_REQUEST['rlc']) ) {
            $pager->db->addWhere('hms_learning_communities.id', $_REQUEST['rlc'], '=');
        }

        $pager->setModule('hms');
        $pager->setLink('index.php?module=hms&type=rlc&op=rlc_assignments_submit');
        $pager->setTemplate('admin/rlc_assignments_pager.tpl');
        $pager->setEmptyMessage("No pending RLC applications.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle1"');
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');
        $pager->setReportRow('applicantsReport');

        return $pager->get();
    }
    
    public function getAdminPagerTags()
    {

        $rlc_list = HMS_Learning_Community::getRLCList();

        $tags = array();
        
        $tags['NAME']           = HMS_Student::get_link($this->user_id);
        $tags['1ST_CHOICE']     = '<a href="./index.php?module=hms&type=rlc&op=view_rlc_application&username=' . $this->getUserID() . '" target="_blank">' . $rlc_list[$this->getFirstChoice()] . '</a>';
        if(isset($rlc_list[$this->getSecondChoice()]))
            $tags['2ND_CHOICE'] = $rlc_list[$this->getSecondChoice()];
        if(isset($rlc_list[$this->getThirdChoice()]))
            $tags['3RD_CHOICE'] = $rlc_list[$this->getThirdChoice()];
        $tags['FINAL_RLC']      = HMS_RLC_Application::generateRLCDropDown($rlc_list,$this->getID());
        $tags['CLASS']          = HMS_SOAP::get_student_class($this->getUserID(), HMS_SOAP::get_application_term($this->getUserID()));
//        $tags['SPECIAL_POP']    = ;
//        $tags['MAJOR']          = ;
//        $tags['HS_GPA']         = ;
        $tags['GENDER']         = HMS_SOAP::get_gender($this->getUserID());
        $tags['DATE_SUBMITTED'] = date('d-M-y',$this->getDateSubmitted());
        $tags['DENY']           = PHPWS_Text::secureLink('Deny', 'hms', array('type'=>'rlc', 'op'=>'deny_rlc_application', 'id'=>$this->id));

        return $tags;
    }

    public function applicantsReport()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $term = HMS_Term::get_selected_term();
        
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
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');

        $pager = &new DBPager('hms_learning_community_applications', 'HMS_RLC_Application');

        $pager->db->addWhere('term', HMS_Term::get_selected_term());
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

    public function getDeniedPagerTags()
    {
        $tags = array();
        $rlc_list = HMS_Learning_Community::getRLCList();

        $tags['NAME']           = HMS_Student::get_link($this->user_id);
        $tags['1ST_CHOICE']     = '<a href="./index.php?module=hms&type=rlc&op=view_rlc_application&username=' . $this->getUserID() . '" target="_blank">' . $rlc_list[$this->getFirstChoice()] . '</a>';
        if(isset($rlc_list[$this->getSecondChoice()]))
            $tags['2ND_CHOICE'] = $rlc_list[$this->getSecondChoice()];
        if(isset($rlc_list[$this->getThirdChoice()]))
            $tags['3RD_CHOICE'] = $rlc_list[$this->getThirdChoice()];
        $tags['CLASS']          = HMS_SOAP::get_student_class($this->getUserID(), HMS_SOAP::get_application_term($this->getUserID()));
        $tags['GENDER']         = HMS_SOAP::get_gender($this->getUserID());
        $tags['DATE_SUBMITTED'] = date('d-M-y',$this->getDateSubmitted());
        $tags['ACTION']         = PHPWS_Text::secureLink('Un-Deny', 'hms', array('type'=>'rlc', 'op'=>'un_deny_rlc_application', 'id'=>$this->id));

        return $tags;
    }
    
    /****************************************************************/
    /* Generates a processed template for the rlc sort dropdown box */
    /****************************************************************/
    public function getDropDown()
    {
        $db = &new PHPWS_DB('hms_learning_communities');
        $result = $db->select();

        if( PHPWS_Error::logIfError($result) ) {
            return $result;
        }

        $communities = array();
        foreach( $result as $community ) {
            $communities[$community['id']] = $community['community_name'];
        }

        javascript('/modules/hms/page_refresh');

        $form = &new PHPWS_Form('dropdown_selector');
        $form->setMethod('get');
        $form->addSelect('rlc', $communities);
        if( isset($_REQUEST['rlc']) ) {
            $form->setMatch('rlc', $_REQUEST['rlc']);
        }
        $form->setExtra('rlc', 'onChange="refresh_page(form)"');
        //$form->setMethod('post');

        $form->addHidden('type', 'rlc');
        $form->addHidden('op', 'assign_applicants_to_rlcs');

        return $form->getTemplate();
    }

    /**
     * Marks an RLC application as denied
     */
    public function deny_rlc_application()
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id', $_REQUEST['id']);
        $app = $db->select('row');

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id', $_REQUEST['id']);
        $db->addValue('denied', 1);

        $result = $db->update();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return HMS_Learning_Community::assign_applicants_to_rlcs(null, 'There was an error working with the database. The application was not modified.');
        }else{
            $result = $db->select();
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($app['user_id'], 28, Current_User::getUsername(), 'Application Denied');

            return HMS_Learning_Community::assign_applicants_to_rlcs('Application denied.');
        }
    }

    /**
     * Marks an RLC application as un-denied
     */
    public function un_deny_rlc_application()
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id', $_REQUEST['id']);
        $db->addValue('denied', 0);

        $result = $db->update();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return HMS_Learning_Community::show_view_denied(null, 'There was an error working with the database. The application was not modified.');
        }else{
            $result = $db->select();
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($result['user_id'], 29, Current_User::getUsername(), "Application un-denied");

            return HMS_Learning_Community::show_view_denied('Application un-denied.');
        }
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
    
    /*
     * Does the checks required before showing the rlc application, and then shows it
     */
    public function show_rlc_application_form()
    {
        if(HMS_RLC_Application::check_for_application($_SESSION['asu_username'], $_SESSION['application_term']) !== FALSE){
            $template['MESSAGE'] = "Sorry, you can only submit one RLC application.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }

        # Check deadlines
        PHPWS_Core::initModClass('hms', 'HMS_Deadlines.php');
        $deadlines = HMS_Deadlines::get_deadlines($_SESSION['application_term']);

        if(!HMS_Deadlines::check_deadline_past('submit_application_begin_timestamp',$deadlines)){
            $template['MESSAGE'] = "Sorry, it is too soon to fill out an RLC application.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }else if(HMS_Deadlines::check_deadline_past('submit_rlc_application_end_timestamp', $deadlines)){
            $template['MESSAGE'] = "Sorry, the RLC application deadline has already passed. Please contact Housing & Residence Life if you are interested in applying for a RLC.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }else{
            return HMS_RLC_Application::show_rlc_application_form_page1();
        }
    }

    # Displays the RLC application form
    public function show_rlc_application_form_page1($message = NULL)
    {
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        PHPWS_Core::initModClass('hms','HMS_Learning_Community.php');

        $template = array();
        
        $rlc_form = & new PHPWS_Form();
        $rlc_form->addHidden('type', 'student');
        $rlc_form->addHidden('op','rlc_application_page1_submit');


        # Make sure the user is eligible for an RLC
        if(HMS_SOAP::get_credit_hours($_SESSION['asu_username']) > 15){
            $template['MESSAGE'] = 'Sorry, you are not eligible for a Unique Housing Option for Underclassmen. Please visit the <a href="http://housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=293" target="_blank">Unique Housing Options for Upperclassmen website</a> for information on applying for Unique Housing Options for Upperclassmen.';
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }

        # 1. About You Section
        $template['MESSAGE'] = $message;

        $username = $_SESSION['asu_username'];
        
        $first_name  = HMS_SOAP::get_first_name($username);
        $middle_name = HMS_SOAP::get_middle_name($username);
        $last_name   = HMS_SOAP::get_last_name($username);
        
        # Check for error in SOAP communication. isset doesn't work to check these, for some reason
        if(!(isset($first_name) && isset($last_name))){
            $template['MESSAGE'] = "Error: There was a problem communicating with the student information server. Please try again later.";
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }

        $template['APPLENET_USERNAME']       = $username;
        $template['APPLENET_USERNAME_LABEL'] = 'Applenet User Name: ';

        $template['FIRST_NAME']        = $first_name;
        $template['FIRST_NAME_LABEL']  = 'First Name: ';
        
        $template['MIDDLE_NAME']       = $middle_name;
        $template['MIDDLE_NAME_LABEL'] = 'Middle Name: ';
        
        $template['LAST_NAME']         = $last_name;
        $template['LAST_NAME_LABEL']   = 'Last Name: ';

        $rlc_form->addHidden('first_name',  $first_name);
        $rlc_form->addHidden('middle_name', $middle_name);
        $rlc_form->addHidden('last_name',   $last_name);

        # 2. Rank Your RLC Choices

        # Get the list of RLCs from the database
        $rlc_choices = HMS_Learning_Community::getRLCList(FALSE);
       
        # Add an inital element to the list.
        $rlc_choices[-1] = "Select";
        
        # Make a copy of the RLC choices list, replacing "Select" with "None".
        # To be used with the second and third RLC choices
        $rlc_choices_none = $rlc_choices;
        $rlc_choices_none[-1] = "None";

        $rlc_form->addDropBox('rlc_first_choice', $rlc_choices);
        $rlc_form->setLabel('rlc_first_choice','First Choice: ');
        if(isset($_REQUEST['rlc_first_choice'])){
            $rlc_form->setMatch('rlc_first_choice', $_REQUEST['rlc_first_choice']); # Select previous choice
        }else{
            $rlc_form->setMatch('rlc_first_choice', -1); # Select the default
        }
        
        $rlc_form->addDropBox('rlc_second_choice', $rlc_choices_none);
        $rlc_form->setLabel('rlc_second_choice','Second Choice: ');
        if(isset($_REQUEST['rlc_second_choice'])){
            $rlc_form->setMatch('rlc_second_choice', $_REQUEST['rlc_second_choice']); # Select previous choice
        }else{
            $rlc_form->setMatch('rlc_second_choice', -1); # Select the default
        }
        
        $rlc_form->addDropBox('rlc_third_choice', $rlc_choices_none);
        $rlc_form->setLabel('rlc_third_choice','Third Choice: ');
        if(isset($_REQUEST['rlc_third_choice'])){
            $rlc_form->setMatch('rlc_third_choice', $_REQUEST['rlc_third_choice']);
        }else{
            $rlc_form->setMatch('rlc_third_choice', -1); # Select the default
        }

        # 3. About Your Choices

        if(isset($_REQUEST['why_specific_communities'])){
            $rlc_form->addTextarea('why_specific_communities',$_REQUEST['why_specific_communities']);
        }else{
            $rlc_form->addTextarea('why_specific_communities');
        }
        $rlc_form->setLabel('why_specific_communities',
                            'Why are you interested in the specific communities you have chosen?');
        $rlc_form->setMaxSize('why_specific_communities',2048);

        if(isset($_REQUEST['strengths_weaknesses'])){
            $rlc_form->addTextarea('strengths_weaknesses', $_REQUEST['strengths_weaknesses']);
        }else{
            $rlc_form->addTextarea('strengths_weaknesses');
        }
        $rlc_form->setLabel('strengths_weaknesses',
                            'What are your strengths and in what areas would you like to improve?');
        $rlc_form->setMaxSize('strengths_weaknesses',2048);

        $rlc_form->addButton('cancel','Cancel');
        $rlc_form->setExtra('cancel','onClick="document.location=\'index.php?module=hms&type=student&op=show_main_menu\'"');

        $rlc_form->addSubmit('submit', 'Continue'); 
    
        $rlc_form->mergeTemplate($template);
        $template = $rlc_form->getTemplate();
                
        PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
        $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_RLC);
        $side_thingie->show();

        return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
    }

    /*
     * Validates the first page of the rlc application form
     * Returns true upon successful validation, or an error
     *         message otherwise.
     * Requires:    first, middle and last name are set
     *              rlc choices are set and are numeric
     *              text fields are set
     */               
    public function validate_rlc_application_page1(){

        # Make sure username and first, middle, last name was submitted
        if(!(isset($_REQUEST['first_name'])        &&
             isset($_REQUEST['middle_name'])       &&
             isset($_REQUEST['last_name'])
          )){
            return "Error: Missing a name or username field.";
        }
        
        # Make sure rlc choices were selected.
        if(!(isset($_REQUEST['rlc_first_choice'])  &&
             isset($_REQUEST['rlc_second_choice']) &&
             isset($_REQUEST['rlc_third_choice'])
           )){
            return "Error: No communitiess submitted.";
        }

        # Make sure rlc choices are numeric
        if(!(is_numeric($_REQUEST['rlc_first_choice'])  &&
             is_numeric($_REQUEST['rlc_second_choice']) &&
             is_numeric($_REQUEST['rlc_third_choice'])
           )){
            return "Error: Invalid community choices.";
        }

        # Make sure rlc choice indicies are > 0 (i.e. not default value)
        # Only check first choice, allowing second and third choices to be "none".
        if($_REQUEST['rlc_first_choice']  < 0 ){
               return "Error: Please rank your community choices.";
        }

        # Make sure that if 2nd choice is "none", that there isn't a third choice
        if($_REQUEST['rlc_second_choice'] == -1 && $_REQUEST['rlc_third_choice'] > -1){
            return "Error: You cannot choose a third community without also choosing a second.";
        }
        
        # Make sure none of the rlc choices match, but allow for second and third choices to match as long as they're both "none".
        if(($_REQUEST['rlc_first_choice']  == $_REQUEST['rlc_second_choice']) ||
           ($_REQUEST['rlc_second_choice'] == $_REQUEST['rlc_third_choice'] && ($_REQUEST['rlc_second_choice'] > -1 && $_REQUEST['rlc_third_choice'] > -1))  ||
           ($_REQUEST['rlc_first_choice']  == $_REQUEST['rlc_third_choice'])){
            return "Error: While ranking your community choices, you cannot select a community more than once.";
        }

        if(!(isset($_REQUEST['why_specific_communities']) &&
           isset($_REQUEST['strengths_weaknesses']))){
            return "Error: Please complete both of the questions in section 3.";
        }

        if(strlen($_REQUEST['why_specific_communities']) > RLC_RESPONSE_LIMIT){
            return 'Error: Your response to the first question in section 3 too long. Your response may not exceed ' . RLC_RESPONSE_LIMIT . ' characters.';
        }

        if(strlen($_REQUEST['strengths_weaknesses']) > RLC_RESPONSE_LIMIT){
            return 'Error: Your response to the second question in section 3 too long. Your response may not exceed ' . RLC_RESPONSE_LIMIT . ' characters.';
        }

        return TRUE;
    }

    /*
     * Displays page 2 of the rlc application form.
     */
    public function show_rlc_application_form_page2($message = NULL){
        
        $template = array();

        if(isset($message)){
            $template['MESSAGE'] = $message;
        }
        
        $rlc_form2 = new PHPWS_Form();
        $rlc_form2->addHidden('type','student');
        $rlc_form2->addHidden('op','rlc_application_page2_submit');

        # Add hidden fields for fields from page 1
        $rlc_form2->addHidden('first_name', $_REQUEST['first_name']);
        $rlc_form2->addHidden('middle_name',$_REQUEST['middle_name']);
        $rlc_form2->addHidden('last_name',  $_REQUEST['last_name']);
        $rlc_form2->addHidden('rlc_first_choice',  $_REQUEST['rlc_first_choice']);
        $rlc_form2->addHidden('rlc_second_choice', $_REQUEST['rlc_second_choice']);
        $rlc_form2->addHidden('rlc_third_choice',  $_REQUEST['rlc_third_choice']);
        $rlc_form2->addHidden('why_specific_communities', $_REQUEST['why_specific_communities']);
        $rlc_form2->addHidden('strengths_weaknesses', $_REQUEST['strengths_weaknesses']);

        $choices = array($_REQUEST['rlc_first_choice'], $_REQUEST['rlc_second_choice'], $_REQUEST['rlc_third_choice']);

        $db = &new PHPWS_DB('hms_learning_community_questions');
        
        for($i = 0; $i < 3; $i++){
            # Skip the question lookup if "none" was selected
            if($choices[$i] == -1){
                continue;
            }

            $db->reset();
            $db->addWhere('learning_community_id',$choices[$i]);
            $result = $db->select('row');

            
            if(PEAR::isError($result)){
                $template['MESSAGE'] = "There was an error looking up the community questions.";
                return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page2.tpl');
            }

            $rlc_form2->addTextArea("rlc_question_$i");
            $rlc_form2->setLabel("rlc_question_$i", $result['question_text']);
            $rlc_form2->setMaxSize("rlc_question_$i", 2048);
        }
        
        $rlc_form2->addSubmit('submit','Submit Application');

        $rlc_form2->addButton('cancel','Cancel');
        $rlc_form2->setExtra('cancel','onClick="document.location=\'index.php?module=hms&type=student&op=show_main_menu\'"');

        $rlc_form2->mergeTemplate($template);
        $template = $rlc_form2->getTemplate();
                
        PHPWS_Core::initModClass('hms', 'HMS_Side_Thingie.php');
        $side_thingie = new HMS_Side_Thingie(HMS_SIDE_STUDENT_RLC);
        $side_thingie->show();
        
        return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page2.tpl');
        
    }
    
    /*
     * Validates the second page of the rlc application form
     * Returns true upon successful validation, or an error
     *         message otherwise.
     * Requires:    Verification from page 1
     *              All three text areas to have some content
     */               
    public function validate_rlc_application_page2(){

        # Verify that all information from page 1 is still in the request
        $message = HMS_RLC_Application::validate_rlc_application_page1();
        if($message !== TRUE){
            return "Error on page 1!";
        }

        # Verify that all three text areas have content
        if(($_REQUEST['rlc_first_choice'] > -1  && $_REQUEST['rlc_question_0'] == '') ||
           ($_REQUEST['rlc_second_choice'] > -1 && $_REQUEST['rlc_question_1'] == '') ||
           ($_REQUEST['rlc_third_choice'] > -1  && $_REQUEST['rlc_question_2'] == '')
          ){
            return "Error: Please answer all of the questions below.";
        }


        if(strlen($_REQUEST['rlc_question_0']) > RLC_RESPONSE_LIMIT){
            return 'Error: Your response to the first question is too long. Your response may not exceed ' . RLC_RESPONSE_LIMIT . ' characters.';
        }

        if(strlen($_REQUEST['rlc_question_1']) > RLC_RESPONSE_LIMIT){
            return 'Error: Your response to the second question is too long. Your response may not exceed ' . RLC_RESPONSE_LIMIT . ' characters.';
        }

        if(strlen($_REQUEST['rlc_question_2']) > RLC_RESPONSE_LIMIT){
            return 'Error: Your response to the third question is too long. Your response may not exceed ' . RLC_RESPONSE_LIMIT . ' characters.';
        }

        return TRUE;
    }

    /* 
     * Displays a RLC Application
     * If no options passed, shows for the currently logged student
     * If an username is provided, shows the RLC application for that student
     */
    public function view_rlc_application($username = NULL)
    {
       if($username == NULL) {
            $username = $_SESSION['asu_username'];
            $tags['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to Menu'), 'hms', array('type'=>'student', 'op'=>'main'));
       } else {
            $tags['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to RLC Applications'), 'hms', array('type'=>'rlc', 'op'=>'assign_applicants_to_rlcs'));
       }

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $tags['FULL_NAME'] = HMS_SOAP::get_first_name($username) . " " . HMS_SOAP::get_last_name($username);

        $tags['FIRST_CHOICE_LABEL'] = "First choice RLC is: ";
        $tags['SECOND_CHOICE_LABEL'] = "Second choice is: ";
        $tags['THIRD_CHOICE_LABEL'] =  "Third choice is: ";
        
        $tags['WHY_SPECIFIC_LABEL'] = "Specific communities chosen because: ";
        $tags['STRENGTHS_AND_WEAKNESSES_LABEL'] = "Strengths and weaknesses: ";
        $tags['WHY_FIRST_CHOICE_LABEL'] = "First choice selected because: ";
        $tags['WHY_SECOND_CHOICE_LABEL'] = "Second choice selected because: ";
        $tags['WHY_THIRD_CHOICE_LABEL'] = "Third choice selected because: ";

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        if(isset($_SESSION['application_term'])){
            $rlc_app = new HMS_RLC_Application($username, $_SESSION['application_term']);
        }else{
            $rlc_app = new HMS_RLC_Application($username, HMS_SOAP::get_application_term($username));
        }
        
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        $rlcs_raw = $db->select();
        
        foreach($rlcs_raw as $rlc) {
            $rlcs[$rlc['id']] = $rlc['community_name'];
        }

        $tags['FIRST_CHOICE'] = $rlcs[$rlc_app->rlc_first_choice_id];
        
        if(isset($rlc_app->rlc_second_choice_id)){
            $tags['SECOND_CHOICE'] = $rlcs[$rlc_app->rlc_second_choice_id];
        }else{
            $tags['SECOND_CHOICE'] = 'None';
        }

        if(isset($rlc_app->rlc_third_choice_id)){
            $tags['THIRD_CHOICE'] = $rlcs[$rlc_app->rlc_third_choice_id];
        }else{
            $tags['THIRD_CHOICE'] = 'None';
        }

        $tags['WHY_SPECIFIC'] = $rlc_app->why_specific_communities;
        $tags['STRENGTHS_AND_WEAKNESSES'] = $rlc_app->strengths_weaknesses;
        $tags['WHY_FIRST_CHOICE'] = $rlc_app->rlc_question_0;

        if(isset($rlc_app->rlc_second_choice_id)){
            $tags['WHY_SECOND_CHOICE'] = $rlc_app->rlc_question_1;
        }else{
            $tags['WHY_SECOND_CHOICE'] = 'n/a';
        }
        
        if(isset($rlc_app->rlc_second_choice_id)){
            $tags['WHY_THIRD_CHOICE'] = $rlc_app->rlc_question_2;
        }else{
            $tags['WHY_THIRD_CHOICE'] = 'n/a';
        }

        return PHPWS_Template::process($tags, 'hms', 'student/rlc_application.tpl');

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
