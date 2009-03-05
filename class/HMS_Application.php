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

    var $asu_username;
    var $term;
    var $student_status;
    var $term_classification;
    var $gender;
    var $cell_phone;
    var $meal_option        = NULL;
    var $lifestyle_option   = NULL;
    var $preferred_bedtime  = NULL;
    var $room_condition     = NULL;
    var $agreed_to_terms    = NULL;
    var $rlc_interest;
    var $aggregate;

    var $physical_disability    = 0;
    var $psych_disability       = 0;
    var $medical_need           = 0;
    var $gender_need            = 0;

    var $created_on;
    var $created_by;

    var $withdrawn = 0;

    /**
     * Constructor
     * Set $asu_username equal to the ASU email of the student you want
     * to create/load a application for.
     */
    public function HMS_Application($asu_username = NULL, $term = NULL)
    {

        if(isset($asu_username)){
            $this->asu_username = $asu_username;
        }else{
            return;
        }

        $this->term = $term;
        
        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_Application()','Caught error from init');
            return $result;
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('hms_application');
        $db->addWhere('asu_username', $this->asu_username);
        $db->addWhere('term', $this->term);
        $result = $db->loadObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
            $this->id = 0;
        }
    }
    
    public function save()
    {
        $db = new PHPWS_DB('hms_application');

        if(!$this->id || is_null($this->id)){
            $this->created_on = mktime();
            $this->created_by = $this->asu_username;
        }

        $result = $db->saveObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }
    
    public function delete()
    {
        $db = new PHPWS_DB('hms_application');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)){
            return $result;
        }
        return TRUE;
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
     * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
     */
    public function calculateAggregate(){
        $aggregate = 0;
        $aggregate |= ($this->term_classification   - 1) << 3;
        $aggregate |= ($this->student_status        - 1) << 2;
        $aggregate |= ($this->preferred_bedtime     - 1) << 1;
        $aggregate |= ($this->room_condition        - 1);
        return $aggregate;
    }

    /**
     * Reports 'this' application to Banner
     */
    public function report_to_banner()
    {
        $plancode = HMS_SOAP::get_plan_meal_codes($this->asu_username, 'lawl', $this->meal_option);
        $result = HMS_SOAP::report_application_received($this->asu_username, $this->term, $plancode['plan'], $plancode['meal']);

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

            $body = "Username: {$this->asu_username}\n";
            $mail->setMessageBody($body);
            $result = $mail->send();
        }else{
            # Log the fact that the application was sent to banner
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            if(Current_User::getUsername() == 'hms_student'){
                HMS_Activity_Log::log_activity($this->asu_username, ACTIVITY_APPLICATION_REPORTED, $this->asu_username);
            }else{
                HMS_Activity_Log::log_activity($this->asu_username, ACTIVITY_APPLICATION_REPORTED, Current_User::getUsername());
            }
        }
    }

    /******************
     * Static Methods *
     ******************/

    /**
     * Checks to see if a application already exists for the given asu username.
     * If so, it returns the true, otherwise it returns false.
     * If no term is given, then the "current term" is used.
     * 
     * The 'withdrawn' parameter is optional. If set to true, then check_for_application will
     * return true for withdrawn applications. If false (default), then check_for_application will
     * ignore withdrawn applications.
     */
    public function check_for_application($asu_username = NULL, $term = NULL, $withdrawn = FALSE)
    {
        $db = &new PHPWS_DB('hms_application');
        if(isset($asu_username)) {
            $db->addWhere('asu_username',$asu_username,'ILIKE');
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
            PHPWS_Error::log($result,'hms','check_for_application',"asu_username:{$_SESSION['asu_username']}");
            return FALSE;
        }
        
        if(sizeof($result) > 1){
            return $result;
        }else{
            return FALSE;
        }
    }

    public function get_all_applicants()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $db = new PHPWS_DB('hms_application');
        $db->addWhere('hms_application.term', HMS_Term::get_selected_term());
        //TODO: Get rid of the use of magic numbers here
        $db->addWhere('hms_application.student_status', 1); // We don't care about Transfers
        for($i = 0; $i < func_num_args(); $i++) {
            $db->addOrder(func_get_arg($i));
        }

        return $db->getObjects('HMS_Application');
    }

    public function get_unassigned_applicants()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        
        $db = new PHPWS_DB('hms_application');
        $db->addJoin('left outer', 'hms_application', 'hms_assignment', 'asu_username', 'asu_username');
        $db->addWhere('hms_application.term', HMS_Term::get_selected_term());
        $db->addWhere('hms_assignment.asu_username', null);
        for($i = 0; $i < func_num_args(); $i++) {
            $db->addOrder(func_get_arg($i));
        }
        
        return $db->getObjects('HMS_Application');
    }

    public function is_feature_enabled($term, $feature){
        $db = &new PHPWS_DB('hms_application_features');
        $db->addWhere('term', $term);
        $db->addWhere('feature', $feature);
        $db->addWhere('enabled', 1);
        $result = $db->select();

        if(PHPWS_Error::logIfError($result) || sizeof($result) == 0){
            return false;
        }

        return true;
    }
}
