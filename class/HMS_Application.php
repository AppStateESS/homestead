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
    var $term;
    var $student_status;
    var $term_classification;
    var $gender;
    var $meal_option;
    var $lifestyle_option;
    var $preferred_bedtime;
    var $room_condition;
    var $rlc_interest;
    var $agreed_to_termsi = NULL;
    var $aggregate;

    var $physical_disability    = 0;
    var $psych_disability       = 0;
    var $medical_need           = 0;
    var $gender_need            = 0;

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
    function HMS_Application($hms_student_id = NULL, $term = NULL)
    {

        if(isset($hms_student_id)){
            $this->setStudentID($hms_student_id);
        }else if(isset($_SESSION['asu_username'])){
            $this->setStudentID($_SESSION['asu_username']);
        }else{
            return;
        }
        
        $result = $this->init($term);
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_Application()','Caught error from init');
            return $result;
        }
    }

    function init($term = NULL)
    {
        # Check if an application for this user and semester already exists.
        $result = HMS_Application::check_for_application($this->hms_student_id, $term);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','init',"Caught error from check_for_application.");
            #return "<i>ERROR!</i><br />Could not check for existing application!<br />";
            return $result;
        }
        
        # If an application exists, then load its data into this object. 
        if($result == FALSE || $result == NULL) return;
        
        $this->setID($result['id']);
        $this->setStudentID($result['hms_student_id']);
        $this->setTerm($result['term']);
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
        $this->setAggregate($result['aggregate']);

        return $result;
    }
    
    /**
     * Creates a new application object from $_REQUEST data and saves it to the database.
     */
    function save_application()
    {
        $question = &new HMS_Application($_SESSION['asu_username'], $_SESSION['application_term']);
        
        $question->setStudentStatus($_REQUEST['student_status']);
        $question->setTerm($_REQUEST['term']);
        $question->setTermClassification($_REQUEST['classification_for_term']);
        $question->setGender($_REQUEST['gender_type']);
        $question->setMealOption($_REQUEST['meal_option']);
        $question->setLifestyle($_REQUEST['lifestyle_option']);
        $question->setPreferredBedtime($_REQUEST['preferred_bedtime']);
        $question->setRoomCondition($_REQUEST['room_condition']);
        $question->setRlcInterest($_REQUEST['rlc_interest']);
        $question->setAgreedToTerms($_REQUEST['agreed_to_terms']);

        if(isset($_REQUEST['special_needs']['physical_disability'])){
            $question->physical_disability = 1;
        }

        if(isset($_REQUEST['special_needs']['psych_disability'])){
            $question->psych_disability = 1;
        }

        if(isset($_REQUEST['special_needs']['medical_need'])){
            $question->medical_need = 1;
        }

        if(isset($_REQUEST['special_needs']['gender_need'])){
            $question->gender_need = 1;
        }

        $result = $question->save();
        
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','Caught error from Application::save()');
            $error = "<i>Error!</i><br />Could not create/update your application!<br />";
            return $error;
        }else{

            # Log the successful submission of an application.
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_SUBMITTED_APPLICATION, $_SESSION['asu_username'], NULL);
            
            if($question->getRlcInterest() == 1) {
                PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
                return HMS_Learning_Community::show_rlc_application_form();
            } else {
                $success  = "Your application was successfully saved.<br /><br />";
                $success .= "You may logout or view your application responses.<br /><br />";
                $success .= PHPWS_Text::secureLink(_('View My Application'), 'hms', array('type'=>'student', 'op'=>'review_application'));
                $success .= "<br /><br />";
                PHPWS_Core::initModClass('hms','HMS_Entry_Term.php');
                if(HMS_Entry_Term::get_entry_semester($_SESSION['asu_username']) == TERM_FALL){
                    $success .= PHPWS_Text::secureLink(_('Unique Housing Options Application'), 'hms', array('type'=>'student', 'op'=>'show_rlc_application_form'));
                    $success .= "<br /><br />";
                }
                $success .= PHPWS_Text::secureLink(_('Back to Main Menu'), 'hms', array('type'=>'student','op'=>'main'));
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
        $db->addvalue('term',$this->getTerm());
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
        $db->addValue('aggregate',$this->calculateAggregate());

        $db->addValue('physical_disability',$this->physical_disability);
        $db->addValue('psych_disability',   $this->psych_disability);
        $db->addValue('medical_need',       $this->medical_need);
        $db->addValue('gender_need',        $this->gender_need);
        
        # If this object has an ID, then do an update. Otherwise, do an insert.
        if(!$this->getID() || $this->getID() == NULL){
            # do an insert
            $this->setCreatedOn();
            $this->setCreatedBy($_SESSION['asu_username']);
            
            $db->addValue('hms_student_id',$this->getStudentID());
            $db->addValue('created_on',$this->getCreatedOn());
            $db->addValue('created_by', $this->getCreatedBy());
            
            $result = $db->insert();
            if(!PEAR::isError($result)) {
                PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
                PHPWS_Core::initModClass('hms', 'HMS_Term.php');
                $plancode = HMS_SOAP::get_plan_meal_codes($_SESSION['asu_username'], 'lawl', $this->getMealOption());
                $result = HMS_SOAP::report_application_received($_SESSION['asu_username'], HMS_Term::get_current_term(), $plancode['plan'], $plancode['meal']);
                
                # If there was an error it will have already been logged
                # but send out a notification anyway
                # TODO: Improve the notification system
                if(!$result){
                    PHPWS_Core::initCoreClass('Mail.php');
                    $send_to = array();
                    $send_to[] = 'jbooker@tux.appstate.edu';
                    $send_to[] = 'jtickle@tux.appstate.edu';
                    
                    $mail = &new PHPWS_Mail;

                    $mail->addSendTo($send_to);
                    $mail->setFrom('hms@tux.appstate.edu');
                    $mail->setSubject('HMS Application Error!');

                    $body = "Username: {$this->hms_student_id}\n";
                    $mail->setMessageBody($body);
                    $result = $mail->send();
                }

            }
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
     * If no term is given, then the "current term" is used.
     */
    function check_for_application($asu_username = NULL, $term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        
        $db = &new PHPWS_DB('hms_application');
        if(isset($asu_username)) {
            $db->addWhere('hms_student_id',$asu_username,'ILIKE');
        } else {
            $db->addWhere('hms_student_id',$this->getStudentID(),'ILIKE');
        }
        
        #$db->addWhere('term', 200810);
        if(isset($term)){
            $db->addWhere('term', $term);
        } else {
            $db->addWhere('term', HMS_Term::get_current_term());
        }
        
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
        if($view != NULL) {
            return HMS_Application::display_application_results();
        } else {
            return HMS_Application::begin_application();
        }
    }

    /**
     * Allows an admin to view a student application
     * TODO: This is duplicated code from the show_application function above.
     *       Consider removing this, and changing whatever uses it to use 'show_application' instead.
     */
    function view_housing_application($username)
    {
        $tpl['TITLE']   = 'Residence Hall Application';
        $tpl['MESSAGE'] = $message;

        $tpl['REDO']    = PHPWS_Text::secureLink("Return to Student", 'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$_REQUEST['student']));
        $tpl['NEWLINES']= "<br /><br />";
       
        $application = &new HMS_Application($username);

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
        
        if($application->getRlcInterest() == 0) $tpl['RLC_INTEREST_1'] = "No";
        else if($application->getRlcInterest() == 1) $tpl['RLC_INTEREST_1'] = "Yes";
   
        $master['APPLICATION']  = PHPWS_Template::process($tpl, 'hms', 'student/student_application.tpl');
        return PHPWS_Template::process($master,'hms','student/student_application_combined.tpl');
    }

    /**
     * Calculates a new aggregate number that is used to autoassign students.
     *
     * The aggregate number is a bitmask that will end up looking like this:
     *
     * Bits Meaning             Options
     * 43   term_classification (freshman, sophomore, junior, senior)
     * 2    student_status      (transfer, new)
     * 1    preferred_bedtime   (early, late)
     * 0    room_condition      (clean, messy)
     *
     * This code was duplicated for a one-time use in update.php.
     *
     * @author Jeff Tickle <jeff at tux dot appstate dot edu>
     */
    function calculateAggregate(){
        $aggregate = 0;
        $aggregate |= ($this->getTermClassification() - 1) << 3;
        $aggregate |= ($this->getStudentStatus()      - 1) << 2;
        $aggregate |= ($this->getPreferredBedtime()   - 1) << 1;
        $aggregate |= ($this->getRoomCondition()      - 1);
        return $aggregate;
    }

    function begin_application($message = NULL)
    {
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        PHPWS_Core::initMOdClass('hms','HMS_Term.php');
        PHPWS_Core::initMOdClass('hms','HMS_Entry_Term.php');
        
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form;

        # Try to load the user's application, in case it already exists
        $application = new HMS_Application($_SESSION['asu_username'], $_SESSION['application_term']);

        # If the 'agreed_to_terms' flag was passed in the request, then use it.
        # Otherwise look for it from an existing application.
        if(isset($_REQUEST['agreed_to_terms'])){
            $form->addHidden('agreed_to_terms', $_REQUEST['agreed_to_terms']);
        }else if(isset($application->agreed_to_terms)){
            $form->addHidden('agreed_to_terms',$application->agreed_to_terms);
        }else{
            $form->addHidden('agreed_to_terms', 0);
        }
        
        $form->addDropBox('student_status', array('1'=>_('New Freshman'),
                                                  '2'=>_('Transfer')));
        
        if(isset($_REQUEST['student_status'])) {
            $form->setMatch('student_status', $_REQUEST['student_status']);
        } else {
            $form->setMatch('student_status', 1);
        }
      
        /**
        * Commented out to hard code for freshmen
        *
        $form->addDropBox('classification_for_term', array('1'=>_('Freshman'),
                                                           '2'=>_('Sophomore'),
                                                           '3'=>_('Junior'),
                                                           '4'=>_('Senior')));
        */

        if(HMS_SOAP::get_student_type($_SESSION['asu_username'] == 'T')) {
            $form->addDropBox('classification_for_term', array('1'=>_('Freshman'),
                                                               '2'=>_('Sophomore'),
                                                               '3'=>_('Junior'),
                                                               '4'=>_('Senior')));
        } else {
            $form->addDropBox('classification_for_term', array('1'=>_('Freshman')));
        }

        if(isset($_REQUEST['classification_for_term'])){
            $form->setMatch('classification_for_term',$_REQUEST['classification_for_term']);
        }else{
            $form->setMatch('classification_for_term', '1');
        }

        # Use a hidden field for the entry term, pull from banner
        $form->addHidden('term', HMS_SOAP::get_application_term($_SESSION['asu_username']));

        # Use a hidden field for gender, pull from banner
        $form->addHidden('gender_type', HMS_SOAP::get_gender($_SESSION['asu_username'], TRUE));
        
        # Don't show *low* meal option to freshmen
        if(HMS_SOAP::get_student_class($_SESSION['asu_username']) != "FR"){
            $form->addDropBox('meal_option', array(HMS_MEAL_LOW=>_('Low'),
                                                   HMS_MEAL_STD=>_('Standard'),
                                                   HMS_MEAL_HIGH=>_('High'),
                                                   HMS_MEAL_SUPER=>_('Super')));
        }else{
            $form->addDropBox('meal_option', array(HMS_MEAL_STD=>_('Standard'),
                                                   HMS_MEAL_HIGH=>_('High'),
                                                   HMS_MEAL_SUPER=>_('Super')));
        }
            
        if(isset($_REQUEST['meal_option'])){
            $form->setMatch('meal_option',$_REQUEST['meal_option']);
        }else{
            $form->setMatch('meal_option', HMS_MEAL_STD);
        }

        $form->addDropBox('lifestyle_option', array('1'=>_('Single Gender Building'),
                                                    '2'=>_('Co-Ed Building')));
        if(isset($_REQUEST['lifestyle_option'])){
            $form->setMatch('lifestyle_option',$_REQUEST['lifestyle_option']);
        }else{
            $form->setMatch('lifestyle_option', '1');
        }

        $form->addDropBox('preferred_bedtime', array('1'=>_('Early'),
                                                     '2'=>_('Late')));
        if(isset($_REQUEST['preferred_bedtime'])){
            $form->setMatch('preferred_bedtime',$_REQUEST['preferred_bedtime']);
        }else{
            $form->setMatch('preferred_bedtime', '1');
        }

        $form->addDropBox('room_condition', array('1'=>_('Neat'),
                                                  '2'=>_('Cluttered')));
        if(isset($_REQUEST['room_condition'])){
            $form->setMatch('room_condition',$_REQUEST['room_condition']);
        }else{
            $form->setMatch('room_condition', '1');
        }

        $form->addCheck('special_needs', array('physical_disability','psych_disability','medical_need','gender_need'));
        $form->setLabel('special_needs', array('Physical disability', 'Psychological disability', 'Medical', 'Gender'));

        if(isset($_REQUEST['special_needs'])){
            $form->setMatch('special_needs', $_REQUEST['special_needs']);
        }
        
        if(HMS_Entry_Term::get_entry_semester($_SESSION['asu_username']) != TERM_FALL){
            $form->addHidden('rlc_interest', 0);
        }else{
            $form->addRadio('rlc_interest', array(0, 1));
            $form->setLabel('rlc_interest', array(_("No"), _("Yes")));
            if(isset($_REQUEST['rlc_interest'])){
                $form->setMatch('rlc_interest',$_REQUEST['rlc_interest']);
            }else{
                $form->setMatch('rlc_interest', '0');
            }
        }

        $form->addSubmit('submit', _('Submit Application'));
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'review_application');

        $tpl = $form->getTemplate();
        $tpl['TITLE']   = 'Residence Hall Application';
        $tpl['MESSAGE'] = $message;
        $tpl['STUDENT_NAME'] = HMS_SOAP::get_full_name($_SESSION['asu_username']);
        $tpl['GENDER'] = (HMS_SOAP::get_gender($_SESSION['asu_username'],TRUE) == '0') ? 'Female' : 'Male';
        $tpl['ENTRY_TERM'] = HMS_Term::term_to_text(HMS_SOAP::get_application_term($_SESSION['asu_username']), TRUE);

        $master['TITLE']   = 'Residence Hall Application';
        $master['APPLICATION']  = PHPWS_Template::process($tpl, 'hms', 'student/student_application.tpl');
        return PHPWS_Template::process($master,'hms','student/student_application_combined.tpl');
    }

    function display_application_results()
    {
        PHPWS_Core::initModClass('hms','HMS_Term.php');
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');



        if(!HMS_Application::check_for_application($_SESSION['asu_username'], $_SESSION['application_term']) 
            && !HMS_Application::check_valid_application_values()) {
            $message = "You have supplied incorrect values for your application.<br />";
            $message .= "Please fill out the application again.";
            return HMS_Form::begin_application($message);
        }

        $application = new HMS_Application($_SESSION['asu_username'], $_SESSION['application_term']);

        $master['TITLE']   = 'Residence Hall Application';
        if(isset($_REQUEST['student_status'])){
            $message  = "You have supplied the following values.<br />";
            $message .= "Click Submit to continue or Modify to change your selections.<br /><br />";

            $form = &new PHPWS_Form;

            $form->addHidden('agreed_to_terms',$_REQUEST['agreed_to_terms']);
            $form->addHidden('term',$_REQUEST['term']);
            $form->addHidden('classification_for_term', $_REQUEST['classification_for_term']);
            $form->addHidden('student_status',$_REQUEST['student_status']);
            $form->addHidden('gender_type',$_REQUEST['gender_type']);
            $form->addHidden('meal_option',$_REQUEST['meal_option']);
            $form->addHidden('lifestyle_option',$_REQUEST['lifestyle_option']);
            $form->addHidden('preferred_bedtime',$_REQUEST['preferred_bedtime']);
            $form->addHidden('room_condition',$_REQUEST['room_condition']);
            if(isset($_REQUEST['special_needs'])){
                $form->addHidden('special_needs',$_REQUEST['special_needs']);
            }
            $form->addHidden('rlc_interest',$_REQUEST['rlc_interest']);
            $form->addHidden('module', 'hms');
            $form->addHidden('type', 'student');
            $form->addHidden('op', 'save_application');

            $form->addSubmit('submit', _('Submit Application'));

            $tpl = $form->getTemplate();

            $redo_form = & new PHPWS_Form('redo_form');
            $redo_form->addSubmit('submit','Modify Application');
            $redo_form->addHidden('type','student');
            $redo_form->addHidden('op','begin_application');
            $redo_form->addHidden('agreed_to_terms',$_REQUEST['agreed_to_terms']);
            $redo_form->addHidden('term',$_REQUEST['term']);
            $redo_form->addHidden('classification_for_term', $_REQUEST['classification_for_term']);
            $redo_form->addHidden('student_status',$_REQUEST['student_status']);
            $redo_form->addHidden('gender_type',$_REQUEST['gender_type']);
            $redo_form->addHidden('meal_option',$_REQUEST['meal_option']);
            $redo_form->addHidden('lifestyle_option',$_REQUEST['lifestyle_option']);
            $redo_form->addHidden('preferred_bedtime',$_REQUEST['preferred_bedtime']);
            $redo_form->addHidden('room_condition',$_REQUEST['room_condition']);
            if(isset($_REQEUST['special_needs'])){
                $redo_form->addHidden('special_needs',$_REQUEST['special_needs']);
            }
            $redo_form->addHidden('rlc_interest',$_REQUEST['rlc_interest']);
            
            $redo_tpl = $redo_form->getTemplate();

            PHPWS_Core::initModClass('hms','HMS_SOAP.php');
            $tpl['STUDENT_NAME'] = HMS_SOAP::get_full_name($_SESSION['asu_username']);

            $tpl['MESSAGE'] = $message;
            $tpl['NEWLINES']= "<br /><br />";

            $tpl['ENTRY_TERM'] = HMS_Term::term_to_text($_REQUEST['term'], TRUE);
            
            if($_REQUEST['student_status'] == 1) $tpl['STUDENT_STATUS'] = "New Freshman";
            else if ($_REQUEST['student_status'] == 2) $tpl['STUDENT_STATUS'] = "Transfer";

            if($_REQUEST['classification_for_term'] == 1) $tpl['CLASSIFICATION_FOR_TERM'] = "Freshman";
            else if($_REQUEST['classification_for_term'] == 2) $tpl['CLASSIFICATION_FOR_TERM'] = "Sophomore";
            else if($_REQUEST['classification_for_term'] == 3) $tpl['CLASSIFICATION_FOR_TERM'] = "Junior";
            else if($_REQUEST['classification_for_term'] == 4) $tpl['CLASSIFICATION_FOR_TERM'] = "Senior";
            
            if($_REQUEST['gender_type'] == 0) $tpl['GENDER'] = "Female";
            else if($_REQUEST['gender_type'] == 1) $tpl['GENDER'] = "Male";
            
            if($_REQUEST['meal_option'] == HMS_MEAL_LOW) $tpl['MEAL_OPTION'] = "Low";
            else if($_REQUEST['meal_option'] == HMS_MEAL_STD) $tpl['MEAL_OPTION'] = "Standard";
            else if($_REQUEST['meal_option'] == HMS_MEAL_HIGH) $tpl['MEAL_OPTION'] = "High";
            else if($_REQUEST['meal_option'] == HMS_MEAL_SUPER) $tpl['MEAL_OPTION'] = "Super";
           
            if($_REQUEST['lifestyle_option'] == 1) $tpl['LIFESTYLE_OPTION'] = "Single Gender";
            else if($_REQUEST['lifestyle_option'] == 2) $tpl['LIFESTYLE_OPTION'] = "Co-Ed";
            
            if($_REQUEST['preferred_bedtime'] == 1) $tpl['PREFERRED_BEDTIME'] = "Early";
            else if($_REQUEST['preferred_bedtime'] == 2) $tpl['PREFERRED_BEDTIME'] = "Late";

            if($_REQUEST['room_condition'] == 1) $tpl['ROOM_CONDITION'] = "Clean";
            else if($_REQUEST['room_condition'] == 2) $tpl['ROOM_CONDITION'] = "Dirty";

            $special_needs = "";
            if(isset($_REQUEST['special_needs']['physical_disability'])){
                $special_needs = "Physical disability<br />";
            }
            if(isset($_REQUEST['special_needs']['psych_disability'])){
                $special_needs .= "Psychological disability<br />";
            }
            if(isset($_REQUEST['special_needs']['medical_need'])){
                $special_needs .= "Medical need<br />";
            }
            if(isset($_REQUEST['special_needs']['gender_need'])){
                $special_needs .= "Gender need<br />";
            }

            if($special_needs == ""){
                $special_needs = "None";
            }

            $tpl['SPECIAL_NEEDS_RESULT'] = $special_needs;

            if($_REQUEST['rlc_interest'] == 0) $tpl['RLC_INTEREST_1'] = "No";
            else if($_REQUEST['rlc_interest'] == 1) $tpl['RLC_INTEREST_1'] = "Yes";
       
            $master['APPLICATION']  = PHPWS_Template::process($tpl, 'hms', 'student/student_application.tpl');
            $master['REDO'] = PHPWS_Template::process($redo_tpl,'hms','student/student_application_redo.tpl');
        
            return PHPWS_Template::process($master,'hms','student/student_application_combined.tpl');
       
        } else {
            
            $tpl['TITLE']   = 'Residence Hall Application';
            if(isset($message)){
                $tpl['MESSAGE'] = $message;
            }
            $tpl['REDO']    = PHPWS_Text::secureLink("Return to Menu", 'hms', array('type'=>'hms', 'op'=>'main'));
            $tpl['NEWLINES']= "<br /><br />";
            
            $tpl['ENTRY_TERM'] = HMS_Term::term_to_text($application->getTerm(), TRUE);
            $tpl['STUDENT_NAME'] = HMS_SOAP::get_full_name($_SESSION['asu_username']);
            
            if($application->getStudentStatus() == 1) $tpl['STUDENT_STATUS'] = "New Freshman";
            else if ($application->getStudentStatus() == 2) $tpl['STUDENT_STATUS'] = "Transfer";

            if($application->getTermClassification() == 1) $tpl['CLASSIFICATION_FOR_TERM'] = "Freshman";
            else if($application->getTermClassification() == 2) $tpl['CLASSIFICATION_FOR_TERM'] = "Sophomore";
            else if($application->getTermClassification() == 3) $tpl['CLASSIFICATION_FOR_TERM'] = "Junior";
            else if($application->getTermClassification() == 4) $tpl['CLASSIFICATION_FOR_TERM'] = "Senior";
            
            if($application->getGender() == 0) $tpl['GENDER'] = "Female";
            else if($application->getGender() == 1) $tpl['GENDER'] = "Male";

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

            $special_needs = "";
            if($application->physical_disability == 1) $special_needs .= "Physical disability<br />";
            if($application->psych_disability == 1) $special_needs .= "Psychological disability<br />";
            if($application->medical_need == 1) $special_needs .= "Medical need<br />";
            if($application->gender_need == 1) $special_needs .= "Gender need<br />";

            if($special_needs == "") $special_needs = "None";

            $tpl['SPECIAL_NEEDS_RESULT'] = $special_needs;
            
            
            if($application->getRlcInterest() == 0) $tpl['RLC_INTEREST_1'] = "No";
            else if($application->getRlcInterest() == 1) $tpl['RLC_INTEREST_1'] = "Yes";
       
            $master['APPLICATION']  = PHPWS_Template::process($tpl, 'hms', 'student/student_application.tpl');
            return PHPWS_Template::process($master,'hms','student/student_application_combined.tpl');
        }
        
    }

    function check_valid_application_values()
    {
        return (is_numeric($_REQUEST['student_status']) &&
                is_numeric($_REQUEST['classification_for_term']) &&
                is_numeric($_REQUEST['gender_type']) &&
                is_numeric($_REQUEST['meal_option']) &&
                is_numeric($_REQUEST['lifestyle_option']) &&
                is_numeric($_REQUEST['preferred_bedtime']) &&
                is_numeric($_REQUEST['room_condition']) &&
                is_numeric($_REQUEST['rlc_interest']));
    }

    function get_unassigned_applicants()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        
        $db = new PHPWS_DB('hms_application');
        $db->addJoin('left outer', 'hms_application', 'hms_assignment', 'hms_student_id', 'asu_username');
        $db->addWhere('hms_application.term', HMS_Term::get_current_term());
        $db->addWhere('hms_assignment.asu_username', null);
        for($i = 0; $i < func_num_args(); $i++) {
            $db->addOrder(func_get_arg($i));
        }
        
        return $db->getObjects('HMS_Application');
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

    function setTerm($term){
        $this->term = $term;
    }

    function getTerm(){
        return $this->term;
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

    function setAggregate($aggregate){
        $this->aggregate = $aggregate;
    }

    // Use setAggregate(calculateAggregate()) to generate a new one
    function getAggregate() {
        return $this->aggregate;
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
