<?php

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

class HMS_SOAP{

    function is_valid_student($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return true;
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }

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

    function get_gender_class($username)
    {
        if(SOAP_TEST_FLAG) {
            $person['gender'] = "M";
            $person['class']  = "NFR";
            return $person;
        }

        $student = HMS_SOAP::get_student_info($username);

        $person['gender'] = $student->gender;
        $person['class']  = $student->projected_class;
        $person['type']   = $student->student_type;
        if($student->student_type == 'F' && $student->credhrs_completed == 0)
            $person['class'] = "NFR";

        return $person;
    }

    function get_name($username){
        if(SOAP_TEST_FLAG){
            # return canned data
            return "Jeremy Booker";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_first_name',$username);
            return $student;
        }else if($student->first_name == NULL){
            return NULL;
        }else{
            return $student->first_name . " " . $student->last_name;
        }
        
    }

    function get_first_name($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "Jeremy";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }
        
        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_first_name',$username);
            return $student;
        }else if($student->first_name == NULL){
            return NULL;
        }else{
            return $student->first_name;
        }
    }

    function get_middle_name($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "Lee";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_middle_name',$username);
            return $student;
        }else if($student->middle_name == NULL){
            return NULL;
        }else{
            return $student->middle_name;
        }
    }

    function get_last_name($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "Booker";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_last_lame',$username);
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
    function get_full_name($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "Jeremy Lee Booker";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }

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
    function get_full_name_inverted($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "Booker, Jeremy Lee";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }

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
    function get_gender($username, $numeric = FALSE)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            if($numeric){
                return 1;
            }else{
                return 'M';
            }
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }

        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_gender',$username);
            return $student;
        }else if($student->gender == NULL){
            return NULL;
        }else{
            if($numeric){
                if($student->gender == 'F'){
                    return 0;
                }else if($student->gender == 'M'){
                    return 1;
                }
            }else{
                return $student->gender;
            }
        }
    }

    /**
     * Returns an associate array with keys:
     * line1, line2, line3, city, county, state, zip
     * 'county' is a county code 
     * 'state' is a two character abbrev.
     */
    function get_address($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return array('line1'  => '123 Rivers St.',
                         'line2'  => 'Apt 12',
                         'line3'  => 'who has a line 3??',
                         'city'   => 'Boone',
                         'county' => '123',
                         'state'  => 'NC',
                         'zip'    => '27591');
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }
        
        if(PEAR::isError($student)){
            HMS_SOAP::log_soap_error($student,'get_address',$username);
            return $student;
        }else{
            return PHPWS_SOAP::get_for_realz_address($student);
        }
    }

    /**
     * This is a HACK and should be treated as such.
     */
    function get_for_realz_address($student)
    {
       if(is_object($student->address)) {
           return $student->address;
       }


       $ps_address = NULL;
       foreach($student->address as $address) {
           if($address->atyp_code == 'PS') {
               $ps_address = $address;
           }
           if($address->atyp_code == 'PR') {
               return $address;
           }
       }

       if(!is_null($ps_address)) {
           return $ps_address;
       }

       test($student);
    }

    /**
     * Returns an address formatted as one line, like so:
     * "line1, (line 2, )(line 3, )city, state, zip"
     * Uses data returned from get_data.
     */
    function get_address_line($username)
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
    function get_student_type($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "T";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }
        
        if(PEAR::isError($student)) {
            HMS_SOAP::log_soap_error($student, 'get_student_type', $username);
            return $student;
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
    function get_student_class($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "SR";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }
        
        if(PEAR::isError($student)) {
            HMS_SOAP::log_soap_error($student, 'get_student_class', $username);
            return $student;
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
    function get_dob($username)
    {
        if(SOAP_TEST_FLAG){
            # return canned data
            return "1986-09-05";
        }else{
            $student = HMS_SOAP::get_student_info($username);
        }
        
        if(PEAR::isError($student)) {
            HMS_SOAP::log_soap_error($student, 'get_student_type', $username);
            return $student;
        }else if($student->dob == NULL){
            return NULL;
        }else{
            return $student->dob;
        }
    }

    /**
     * Main function for getting student info.
     * Used by the rest of the "get" functions
     */
    function get_student_info($username)
    {
        if(SOAP_TEST_FLAG) {
            $student->first_name = "kevin";
            $student->middle_name = "michael";
            $student->last_name = "wilcox";
            $student->gender = "M";
            $student->dob = "1980-03-31";
            $student->student_type = 'C';
            $student->projected_class = 'SR';
            $student->address['line1'] = '275 Honey Hill Drive';
            $student->address['line2'] = '';
            $student->address['line3'] = '';
            $student->address['city'] = 'Blowing Rock';
            $student->address['county'] = '99';
            $student->address['state'] = 'NC';
            $student->address['zip'] = '28605';
            $student->phone['area_code'] = '828';
            $student->phone['number'] = '2780579';
            return $student;
        }

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $student = $proxy->GetStudentProfile($username, '200740');
        
        # Check for an error and log it
        if(HMS_SOAP::is_soap_fault($student)){
            HMS_SOAP::log_soap_error($student,'get_student_info',$username);
        }

        return $student;
    }

    /**
     * Returns a student's current assignment information
     * $opt is one of:
     *  'All'
     *  'HousingApp'
     *  'RoomAssign'
     *  'MealAssign'
     */
    function get_hous_meal_register($username, $termcode, $opt)
    {
        if(SOAP_TEST_FLAG) {
            # return canned data
            return array();
        }
        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $student = $proxy->GetHousMealRegister($username, $termcode, $opt);

        # Check for an error and log it
        if(HMS_SOAP::is_soap_fault($student)) {
            HMS_SOAP::log_soap_error($student, 'get_hous_meal_register', $username);
        }

        return $student;
    }

    function report_application_received($username, $term, $plan_code, $meal_code = NULL)
    {
        if(SOAP_TEST_FLAG){
            return;
        }
        
        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $assignment = $proxy->CreateHousingApp($username, $term, $plan_code, $meal_code);

        return $assignment;
    }

    function this_term()
    {
        // TODO: make this not static
        return '200740';
    }

    function report_room_assignment($username, $term, $building_code, $room_code, $plan_code, $meal_code)
    {
        if(SOAP_TEST_FLAG){
            return;
        }

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $assignment = $proxy->CreateRoomAssignment($username, $term, $building_code, $room_code, $plan_code, $meal_code);

        return $assignment;
/*        test(array($username,$term,$building_code,$room_code,$plan_code,$meal_code));
        return 1001;*/
    }

    function move_room_assignment($username, $term, $cur_bldg, $cur_room, $new_bldg, $new_room)
    {
/*        if(SOAP_TEST_FLAG){
            return;
        }

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $movement = $proxy->MoveRoomAssignment($username, $term, $cur_bldg, $cur_room, $new_bldg, $new_room);

        return $movement;*/
        test(array($username,$term,$cur_bldg,$cur_room,$new_bldg,$new_room));
        return 0;
    }

    function remove_room_assignment($username, $term, $building, $room)
    {
        if(SOAP_TEST_FLAG) {
            return;
        }

        include_once('SOAP/Client.php');
        $wsdl = new SOAP_WSDL(PHPWS_SOURCE_DIR . 'mod/hms/inc/shs0001.wsdl', 'true');
        $proxy = $wsdl->getProxy();
        $removal = $proxy->RemoveRoomAssignment($username, $term, $building, $room);

        return $removal;
/*        test(array($username,$term,$building,$room));
        return 0;*/
    }

    /**
     * Returns TRUE if an error object is of class 'soap_fault'
     */
    function is_soap_fault($object)
    {
        if(is_a($object, 'soap_fault')){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /** 
     * Uses the PHPWS_Core log function to 'manually' log soap errors to soap_error.log.
     */
    function log_soap_error($soap_fault, $function, $extra_info)
    {
        $error_msg = $soap_fault['message'] . "in function: " . $function . " Extra info: " . $extra_info;    
        PHPWS_Core::log($error_msg, 'soap_error.log', _('Error'));
    }

    /**
     * Returns the student's phone number in either xxx.xxx.xxxx or (xxx)xxx-xxxx fashion
     */
    function get_phone_number($username, $alt = NULL)
    {
        if(SOAP_TEST_FLAG) {
            return '123.456.7890';
        }
       
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
    function get_plan_meal_codes($username, $building, $hms_meal_code)
    {
        $type = HMS_SOAP::get_student_type($username);

        $retval['plan'] = 'HOME';

        switch($hms_meal_code) {
            case 0:
                if($type == 'NFR')
                    $retval['meal'] = '1';
                else
                    $retval['meal'] = '2';
                break;
            case 1:
                $retval['meal'] = '1';
                break;
            case 2:
                $retval['meal'] = '2';
                break;
            case 3:
                $retval['meal'] = '8';
                break;
            case 4:
                if(($building == 'MAR' || $building == 'AHR') &&
                        $type != 'NFR') {
                    // HOUS, if we ever do that craziness again
                } else {
                    $retval['meal'] = '1';
                }
        }

        return $retval;
    }
}

?>
