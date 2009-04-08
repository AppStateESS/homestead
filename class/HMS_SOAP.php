<?php

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

class HMS_SOAP{

    /**
     * Main public function for getting student info.
     * Used by the rest of the "get" public functions
     */
    public function get_student_info($username, $term = NULL)
    {
        /**
         * This variable is a hash table that stores student
         * data, so we only have to ask for it from banner once
         * per seesion.
         */
        static $student_info_table;

        # If no term was passed, then use the current term
        if(!isset($term)){     
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Term::get_current_term();
        }

        // Check the hash table 
        $hash_key = $username . $term;
        if(isset($student_info_table) && array_key_exists($hash_key, $student_info_table)){
            return $student_info_table[$hash_key];
        }

        /**
         * sepcial exceptions for me
         */
        if($username == 'jb67803'){
            $student = HMS_SOAP::get_test_info();
            $student->first_name    = 'Jeremy';
            $student->middle_name   = 'L';
            $student->last_name     = 'Booker';
            $student->gender        = 'M';
            $student->application_term  = '200810'; // a freshmen/rising sophomore
            $student->projected_class   = 'SO';
            $student->student_type      = 'C';

            return $student;
        }

        // Return canned data
        if(SOAP_INFO_TEST_FLAG) {
            $student = HMS_SOAP::get_test_info(); 
            // insert canned data into hash table
            $student_info_table[$hash_key] = $student;
            return $student;
        }        

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $student = $proxy->GetStudentProfile($username, $term);
        
        # Check for an PEAR error and log it
        if(HMS_SOAP::is_soap_fault($student)){
            HMS_SOAP::log_soap('get_student_info', 'PEAR error', $username,
                $term);
            HMS_SOAP::log_soap_fault($student,'get_student_info',$username);
            HMS_SOAP::handle_soap_fault(); 
        }

        # Check for a banner error
        if(is_numeric($student) && $student > 0){
            HMS_SOAP::log_soap('get_student_info', "Banner Error: $student",
                $username, $term);
            HMS_SOAP::log_soap_error('error code: ' . $student, 'get_student_info', $username);
            return false;
        }

        HMS_SOAP::log_soap('get_student_info', 'success', $username, $term);

        /**************
         * Exceptions *
         **************/
         // Because Banner sux0rz
        if($username == 'wilsonkm'){
            $student->application_term = 200640;
        }

        if($username == 'nw78795'){
            $studnet->application_term = 200540;
        }

        if($username == 'ms78595'){
            $student->application_term = 200540;
        }

        if($username == 'nf67284'){
            $student->application_term = 200540;
        }

        if($username == 'bossardetbp'){
            $student->application_term = 200840;
        }
        
        if($username == 'watsonmc'){
            $student->application_term = 200840;
        }
        
        // insert into hash table
        $student_info_table[$hash_key] = $student;
        
        return $student;
    }

    /**
     * Returns the ASU Username for the given banner id
     */
    public function get_username($banner_id)
    {
        if(SOAP_INFO_TEST_FLAG){
            return HMS_SOAP::get_test_username();
        }

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $username = $proxy->GetUserName($banner_id);

        if(is_soap_fault($username)) {
            log_soap_error($username, 'get_username', $bannerid);
            HMS_SOAP::handle_soap_fault();
        }

        return $username;
    }


    /**
     * Report that a housing application has been received.
     * Makes First Connections stop bugging the students.
     */
    public function report_application_received($username, $term, $plan_code = 'HOME', $meal_code = NULL)
    {
        if(SOAP_REPORT_TEST_FLAG) {
            $result = HMS_SOAP::get_test_report();
        } else {
            include_once('SOAP/Client.php');
            $wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
            $proxy = $wsdl->getProxy();
            $result = $proxy->CreateHousingApp($username, $term, $plan_code, $meal_code);
        }

        # Check for an error and log it
        if(HMS_SOAP::is_soap_fault($result)){
            HMS_SOAP::log_soap('report_application_received', 'PEAR error',
                $username, $term, $plan_code, $meal_code);
            HMS_SOAP::log_soap_fault($result, 'report_application_received', $username . ' ' . $term);
            HMS_SOAP::handle_soap_fault(); 
        }

        # It's not a SOAP Fault, so hopefully it's an int.
        $result = (int)$result;

        # Check for a banner error
        if($result > 0){
            HMS_SOAP::log_soap('report_application_received',
                "Banner error: $result", $username, $term, $plan_code,
                $meal_code);
            HMS_SOAP::log_soap_error($result, 'report_application_received', $username);
            return $result;
        }
        
        HMS_SOAP::log_soap('report_application_received', 'success', $username,
            $term, $plan_code, $meal_code);
        
        return $result;
    }

    /**
     * Sends a room assignment to banner. Will cause students to be billed, etc.
     */
    public function report_room_assignment($username, $term, $building_code, $room_code, $plan_code, $meal_code)
    {
        if(SOAP_REPORT_TEST_FLAG){
            return HMS_SOAP::get_test_report();
        }

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $assignment = $proxy->CreateRoomAssignment($username, $term, $building_code, $room_code, $plan_code, $meal_code);
        
        # Check for an error and log it
        if(HMS_SOAP::is_soap_fault($assignment)){
            HMS_SOAP::log_soap('report_room_assignment', 'PEAR error',
                $username, $term, $building_code, $room_code, $plan_code,
                $meal_code);
            HMS_SOAP::log_soap_fault($assignment, 'report_room_assignment', $username . ' ' . $term);
            HMS_SOAP::handle_soap_fault(); 
        }
        
        # Check for a banner error
        if(is_numeric($assignment) && $assignment > 0){
            HMS_SOAP::log_soap('report_room_assignment',
                "Banner error: $assignment", $username, $term, $building_code,
                $room_code, $plan_code, $meal_code);
            HMS_SOAP::log_soap_error('Banner error: ' . $assignment, 'report_room_assignment', $username);
            return false;
        }
        
        HMS_SOAP::log_soap('report_room_assignment', 'success', $username,
            $term, $building_code, $room_code, $plan_code, $meal_code);
        
        return $assignment;
    }

    /**
     * Remove the deletion of a room assignment to Banner.
     * Will cause students to be credited, etc.
     */
    public function remove_room_assignment($username, $term, $building, $room)
    {
        if(SOAP_REPORT_TEST_FLAG) {
            return HMS_SOAP::get_test_report();
        }

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $removal = $proxy->RemoveRoomAssignment($username, $term, $building, $room);

        # Check for an error and log it
        if(HMS_SOAP::is_soap_fault($removal)){
            HMS_SOAP::log_soap('remove_room_assignment', 'PEAR error',
                $username, $term, $building, $room);
            HMS_SOAP::log_soap_fault($removal, 'remove_room_assignment', $username . ' ' . $term);
            HMS_SOAP::handle_soap_fault(); 
        }
        
        # Check for a banner error
        if(is_numeric($removal) && $removal > 0){
            HMS_SOAP::log_soap('remove_room_assignemnt',
                "Banner error: $removal", $username, $term, $building, $room);
            HMS_SOAP::log_soap_error('Banner error: ' . $removal, 'remove_room_assignment', $username);
            return $removal;
        }
        
        HMS_SOAP::log_soap('remove_room_assignment', 'success', $username,
            $term, $building, $room);
        
        return $removal;
    }
    
    /**
     * Returns a student's current assignment information
     * $opt is one of:
     *  'All'
     *  'HousingApp'
     *  'RoomAssign'
     *  'MealAssign'
     */
    public function get_hous_meal_register($username, $termcode, $opt)
    {
        if(SOAP_INFO_TEST_FLAG) {
            return HMS_SOAP::get_test_hous_meal();
        }
        
        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL('file://' . PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $student = $proxy->GetHousMealRegister($username, $termcode, $opt);

        # Check for an error and log it
        if(HMS_SOAP::is_soap_fault($student)) {
            HMS_SOAP::log_soap('get_hous_meal_register', 'PEAR Error',
                $username, $termcode, $opt);
            HMS_SOAP::log_soap_fault($student, 'get_hous_meal_register', $username);
            HMS_SOAP::handle_soap_fault(); 
        }
        
        # Check for a banner error
        if(is_numeric($student) && $student > 0){
            HMS_SOAP::log_soap('get_hous_meal_register',
                "Banner error: $student", $username, $termcode, $opt);
            HMS_SOAP::log_soap_error('Banner error: ' . $student, 'get_hous_meal_register', $username);
            return false;
        }
        
        HMS_SOAP::log_soap('get_hous_meal_register', 'success',
            $username, $termcode, $opt);

        return $student;
    }


    /*******************************
     * Individual 'get' public functions' *
     *******************************
     *
     * The public functions below pull various pieces from the 
     * student info object returned by 'get_student_info()'
     */
    
    
    public function is_valid_student($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'is_valid_student',$username);
            return $student;
        }
        
        if($student->last_name == NULL) {
            return false;
        } else {
            return true;
        }
    }

    public function get_banner_id($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        
        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_banner_id',$username);
            return $student;
        }

        return $student->banner_id;
    }

    public function get_credit_hours($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_credit_hours',$username);
            return $student;
        }

        return $student->credhrs_completed;
    }

    public function get_name($username){
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_name',$username);
            return $student;
        }else if($student->first_name == NULL){
            return NULL;
        }else{
            return $student->first_name . " " . $student->last_name;
        }
        
    }

    public function get_first_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        
        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_first_name',$username);
            return $student;
        }else if($student->first_name == NULL){
            return NULL;
        }else{
            return $student->first_name;
        }
    }

    public function get_middle_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_middle_name',$username);
            return $student;
        }else if($student->middle_name == NULL){
            return NULL;
        }else{
            return $student->middle_name;
        }
    }

    public function get_last_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_last_name',$username);
            return $student;
        }else if($student->last_name == NULL){
            return NULL;
        }else{
            return $student->last_name;
        }
    }

    /**
     * Given a username, returns the full name of the student in
     * the natural order (first middle last).
     */
    public function get_full_name($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_full_name',$username);
            return $student;
        }else if($student->last_name == NULL){
            return NULL;
        }else{
            return ($student->first_name  . " " .
                    $student->middle_name . " " .
                    $student->last_name);
        }
    }

    /**
     * Given a username, returns the full name of the student in the 
     * (last, first middle) format.
     */
    public function get_full_name_inverted($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_full_name_inverted',$username);
            return $student;
        }else if($student->last_name == NULL){
            return NULL;
        }else{
            return ($student->last_name  . ", " . 
                    $student->first_name . " "  .
                    $student->middle_name);
        }
    }

    /**
     * Returns the gender of the given username as 'M' or 'F' by default.
     * If $numeric is set to true, returns the gender as an int where 0 => Female
     * and 1 => Male. (For database operations).
     */
    public function get_gender($username, $numeric = FALSE)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_gender',$username);
            return $student;
        }else if($student->gender == NULL){
            return NULL;
        }else{
            if($numeric){
                if($student->gender == 'F'){
                    return FEMALE;
                }else if($student->gender == 'M'){
                    return MALE;
                }
            }else{
                return $student->gender;
            }
        }
    }

    /**
     * Returns an array of all the address objects
     * associated with a student.
     */
    public function get_addresses($username)
    {
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            return $student;
        }

        return $student->address;
    }

    /**
     * Returns an associate array with keys:
     * line1, line2, line3, city, county, state, zip
     * 'county' is a county code 
     * 'state' is a two character abbrev.
     *
     * Passing a type of 'null' will cause a 'PR' address to
     * be returned, or a 'PS' addresses if no PR exists.
     * 
     * Valid options for 'type' are the address types defined in inc/defines.php:
     * null (default, returns 'PR' if exists, otherwise 'PS')
     * ADDRESS_PRMT_RESIDENCE ('PR' - permanent residence)
     * ADDRESS_PRMT_STUDENT   ('PS' - permanent student)
     */
    public function get_address($username, $type = ADDRESS_PRMT_RESIDENCE)
    {
        $student = HMS_SOAP::get_student_info($username);

        //test($student);
        
        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_address',$username);
            return $student;
        }

        $pr_address = null;
        $ps_address = null;

        // Determine if soap gave us just one object, or an array of objects
        if(is_array($student->address) && count($student->address) > 1){
            // multiple address, so loop over them
            foreach($student->address as $address){
                if(((string)$address->atyp_code) == ADDRESS_PRMT_RESIDENCE) {
                    $pr_address = $address;
                }else if(((string)$address->atyp_code) == ADDRESS_PRMT_STUDENT){
                    $ps_address = $address;
                }
            }
        }else{
            // one address, so just decide if we're interested in it
            if($student->address->atyp_code == ADDRESS_PRMT_RESIDENCE){
                $pr_address = $student->address;
            }
            if($student->address->atyp_code == ADDRESS_PRMT_STUDENT){
                $ps_address = $student->address;
            }
        }

        

        # Decide which address type to return, based a $type parameter
        if(is_null($type)){
            # Return the pr address, if it exists
            if(!is_null($pr_address)){
                return $pr_address;
            # Since there was no ps address, return the ps address, if it exists
            }else if(!is_null($ps_address)){
                return $ps_address;
            }else{
                # No address found, return false
                return false;
            }
        }else if($type == ADDRESS_PRMT_RESIDENCE && !is_null($pr_address)){
            return $pr_address;
        }else if($type == ADDRESS_PRMT_STUDENT && !is_null($ps_address)){
            return $ps_address;
        }else{
            # Either a bad type was specified (i.e. not null and not PS or PR)
            # or the specified type was not found
            return false;
        }

        # Since we got here without finding the requested address, just return false
        return false;
    }

    /**
     * Returns an address formatted as one line, like so:
     * "line1, (line 2, )(line 3, )city, state, zip"
     * Uses data returned from get_data.
     */
    public function get_address_line($username)
    {
        $addr = HMS_SOAP::get_address($username);
        if(PEAR::isError($addr)) {
            return $addr;
        } else if($addr == NULL) {
            return '';
        }

        $line2 = ($addr->line2 != NULL && $addr->line2 != '') ? 
                 ($addr->line2 . ', ') : '';
        $line3 = ($addr->line3 != NULL && $addr->line3 != '') ?
                 ($addr->line3 . ', ') : '';

        return "{$addr->line1}, $line2$line3{$addr->city}, " .
               "{$addr->state} {$addr->zip}";
    }

    /**
     * Returns the student type:
     * C => continuing
     * T => transfer
     */
    public function get_student_type($username, $term = NULL)
    {
        $student = HMS_SOAP::get_student_info($username, $term);
        
        if(PEAR::isError($student)) {
            HMS_SOAP::log_soap_error($student, 'get_student_type', $username);
            return FALSE;
        }else if($student->student_type == NULL){
            return NULL;
        }else{
            return $student->student_type;
        }
    }

    /**
     * Returns the student's class. Possible values:
     * FR => Freshmen
     * SO => Sophomore
     * JR => Junior
     * SR => Senior
     */
    public function get_student_class($username, $term = NULL)
    {
        $student = HMS_SOAP::get_student_info($username, $term);
        
        if(PEAR::isError($student)) {
            HMS_SOAP::log_soap_error($student, 'get_student_class', $username);
            return FALSE;
        } else if($student->projected_class == NULL) {
            return NULL;
        } else {
            return $student->projected_class;
        }
    }

    /**
     * Returns the student's date of birth
     * Format: yyyy-mm-dd
     */
    public function get_dob($username)
    {
        $student = HMS_SOAP::get_student_info($username);
        
        if(PEAR::isError($student)) {
            HMS_SOAP::log_soap_error($student, 'get_dob', $username);
            return FALSE;
        }else if($student->dob == NULL){
            return FALSE;
        }else{
            return $student->dob;
        }
    }


    /**
     * Returns the student's 'application term' in Banner
     * i.e. The term the student has applied for and will begin attending ASU
     * Format: yyyytt
     * Where 'tt' is a two digit term identifier,
     * 10 => Spring
     * 20 => Summer 1
     * 30 => Summer 2
     * 40 => Fall
     */
    public function get_application_term($username){
        $student = HMS_SOAP::get_student_info($username);

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student, 'get_application_term', $username);
            return FALSE;
        }else if($student->application_term == NULL){
            return NULL;
        }else{
            #   THIS IS THE NEW SUMMER HACK!!
            if($student->application_term == 200820 || $student->application_term == 200830){
                return 200840;
            }else{
                return $student->application_term;
            }
        }
    }

    /**
     * Returns the student's phone number in either xxx.xxx.xxxx or (xxx)xxx-xxxx fashion
     */
    public function get_phone_number($username, $alt = NULL)
    {
        $student = HMS_SOAP::get_student_info($username);
        
        if(PEAR::isError($student)) {
            HMS_SOAP::log_soap_error($student, 'get_phone_number', $username);
            return $student;
        }else if($student->phone == NULL){
            return NULL;
        }else if ($alt == NULL) {
            $phone = $student->phone->area_code . "." . substr($student->phone->number, 0, 3) . "." . substr($student->phone->number, 3);
            return $phone;
        } else {
            $phone = "(" . $student->phone->area_code . ")"  . substr($student->phone->number, 0, 3) . "-" . substr($student->phone->number, 3);
            return $phone;
        }
    }
    
    /*********************
     * Utility Functions *
     *********************/

    /**
     * Returns TRUE if an error object is of class 'soap_fault'
     */
    public function is_soap_fault($object)
    {
        if(is_object($object) && is_a($object, 'soap_fault')){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /** 
     * Uses the PHPWS_Core log public function to 'manually' log soap errors to soap_error.log.
     */
    public function log_soap_fault($soap_fault, $function, $extra_info)
    {
        $error_msg = $soap_fault->message . 'in public function: ' . $function . " Extra: " . $extra_info;    
        PHPWS_Core::log($error_msg, 'soap_error.log', _('Error'));
    }

    /**
     * Uses the PHPWS_Core log public function to 'manually' log soap erros to soap_error.log.
     */
    public function log_soap_error($message, $function, $extra)
    {
        PHPWS_Core::log('Banner error: ' . $message . ' in public function: ' . $function . ' Extra: ' . $extra, 'soap_error.log', 'Error');
    }

    /**
     * Uses the PHPWS_Core log public function to 'manually' log soap requests
     */
    public function log_soap($function, $result)
    {
        $arglist = func_get_args();
        $args = implode(', ', array_slice($arglist, 2));
        $msg = "$function($args) result: $result";
        PHPWS_Core::log($msg, 'soap.log', 'SOAP');
    }    

    /**
     * This thing takes a bunch of information you give it:
     * 
     * @param $username      The ASU username you are assigning
     * @param $building      The building they are going into
     * @param $hms_meal_code The HMS meal code
     * 
     * and gives you 
     * 
     * @returns an array: 'plan' => PLAN_CODE, 'meal' => MEAL_CODE
     *
     * Note that we no longer do HOUS, only HOME.  So 'plan' will
     * always be 'HOME'.
     *
     * TODO: It is a HACK that needs to be implemented more betterly.
     */
    public function get_plan_meal_codes($username, $building, $banner_meal_code)
    {
        $type = HMS_SOAP::get_student_type($username, HMS_SOAP::get_application_term($username));

        $retval['plan'] = 'HOME';

        if(is_null($banner_meal_code)) {
            $retval['meal'] = NULL;
            return $retval;
        }

        switch((String)$banner_meal_code) {
            case BANNER_MEAL_LOW: // low
                if($type == TYPE_FRESHMEN)
                    $retval['meal'] = BANNER_MEAL_STD;
                else
                    $retval['meal'] = BANNER_MEAL_LOW;
                break;
            case BANNER_MEAL_STD: // standard
                $retval['meal'] = BANNER_MEAL_STD;
                break;
            case BANNER_MEAL_HIGH: // high
                $retval['meal'] = BANNER_MEAL_HIGH;
                break;
            case BANNER_MEAL_SUPER: // super
                $retval['meal'] = BANNER_MEAL_SUPER;
                break;
            case BANNER_MEAL_SUMMER1:
                $retval['meal'] = BANNER_MEAL_SUMMER1;
                break;
            case BANNER_MEAL_SUMMER2:
                $retval['meal'] = BANNER_MEAL_SUMMER2;
                break;
            case NULL: // none
                if(($building == 'MAR' || $building == 'AHR') &&
                        $type != TYPE_FRESHMEN) {
                    $retval['meal'] = NULL;
                } else {
                    $retval['meal'] = BANNER_MEAL_STD;
                }
        }

        return $retval;
    }

   public function handle_soap_fault()
   {
        // Show an error page
        if(Current_User::getUsername() == 'hms_student'){
            Layout::add('An error occurred while trying to communicate with the primary Banner student information server on which the Housing Management System relies. The error has been logged, and server administrators have been notified. We apologize for any inconvenience this may have caused, please try again later. Please do not contact Housing & Residence Life regarding this error, as they will be unable to assist you.', 'hms');
        }else{
            Layout::add('An error occurred while trying to communicate with Banner. Please contact ESS.','hms');
        }
        echo Layout::display();
        exit;
   }

    /*************************
     * Canned data public functions *
     *************************/

    public function get_test_info(){
        $student->banner_id             = 900325006;
        $student->last_name             = 'Booker';
        $student->first_name            = 'Jeremy';
        $student->middle_name           = 'Lee';
        $student->dob                   = '1986-09-05';
        $student->gender                = 'M';
        $student->deposit_date          = '';
        $student->deposit_waved         = 'false';

        $student->student_type          = 'C';
        $student->application_term      = '200840';
        $student->projected_class       = 'SO';

        //$student->student_type          = 'C';
        //$student->application_term      = '200840';
        //$student->projected_class       = 'SR';

/*
        $student->student_type          = 'F';
        $student->application_term      = '200940';
        $student->projected_class       = 'FR';
*/

        $student->credhrs_completed     = 0;
        $student->credhrs_for_term      = 15;
        $student->on_campus             = 'false';
        
        $student->address = array();
        
        // Setup the address object
        $address->atyp_code = 'PS';
        $address->line1     = '123 Rivers St. - PS Address';
        $address->line2     = 'c/o Electronic Student Services';
        $address->line3     = 'Room 267';
        $address->city      = 'Boone';
        $address->county    = '095';
        $address->state     = 'NC';
        $address->zip       = '28608';

        $student->address[] = $address;

        // Setup a second address object
        $address->atyp_code = 'PR';
        $address->line1     = '123 Blowing Rock Road - PR Address';
        $address->line2     = 'c/o Electronic Student Services';
        $address->line3     = 'Room 267';
        $address->city      = 'Boone';
        $address->county    = '095';
        $address->state     = 'NC';
        $address->zip       = '28608';

        $student->address[] = $address;
        
        // Setup the phone number object
        $phone->area_code   = '123';
        $phone->number      = '4567890';
        $phone->ext         = '1337';

        $student->phone[] = $phone;

        return $student;
    }

    public function get_test_username(){
        return 'jb67803';
    }

    public function get_test_report()
    {
//        return 1337; //error
        return 0;
    }


    public function get_test_hous_meal()
    {
        // Assemble the housing_app object
        $housing_app->plan_code     = 'HOME';
        $housing_app->status_code   = 'AC';
        $housing_app->status_date   = '2007-02-20';

        // Assemble the room_assign object
        $room_assign->bldg_code     = 'JTR';
        $room_assign->room_code     = 02322;
        $room_assign->status_code   = 'AC';
        $room_assign->status_date   = '2008-01-14';

        // Asseble the meal_assign object
        $meal_assign->plan_code     = 1;
        $meal_assign->status_code   = 'AC';
        $meal_assign->status_date   = '2007-11-20';

        // Assemble the final object to be returned
        $hous_meal->housing_app     = $housing_app;
        $hous_meal->room_assign     = $room_assign;
        $hous_meal->meal_assign     = $meal_assign;

        return $hous_meal;
    }
}

?>
