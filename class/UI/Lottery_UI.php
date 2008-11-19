<?php

/**
 * Lottery_UI - A class for holding the static UI public function for the lottery interface.
 * 
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

define('ROOMMATE_INVITE_TTL', 345600); // 96 hours

class Lottery_UI {

    public function show_lottery_signup($error_msg = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
        # Check if the user is already entered for the lottery
        # if so, display the appropriate message
        $result = HMS_Lottery_Entry::check_for_entry($_SESSION['asu_username'], HMS_SOAP::get_application_term($_SESSION['asu_username']));
        if($result != FALSE && !PEAR::isError($result)){
            # Student already has a lottery entry
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'student/lottery_already_entered.tpl');
        }

        $tpl = array();

        $tpl['NAME'] = HMS_SOAP::get_name($_SESSION['asu_username']);

        $form = &new PHPWS_Form();
        if(isset($_REQUEST['roommate1'])){
            $form->addText('roommate1', $_REQUEST['roommate1']);
        }else{
            $form->addText('roommate1');
        }

        if(isset($_REQUEST['roommate2'])){
            $form->addText('roommate2', $_REQUEST['roommate2']);
        }else{
            $form->addText('roommate2');
        }

        if(isset($_REQUEST['roommate3'])){
            $form->addText('roommate3', $_REQUEST['roommate3']);
        }else{
            $form->addText('roommate3');
        }

        $form->addCheck('special_need', array('special_need'));
        $form->setLabel('special_need', array('Yes, I require special needs housing.'));

        if(isset($_REQUEST['special_need'])){
            $form->setMatch('special_need', $_REQUEST['special_need']);
        }

        $form->addCheck('terms_check', array('terms_check'));
        $form->setLabel('terms_check', 'I have read and I agree to the housing <a href=\'http://housing.appstate.edu\'>terms and conditions.</a>');
        if(isset($_REQUEST['terms_check'])){
            $form->setMatch('terms_check', $_REQUEST['terms_check']);
        }
        
        $form->addSubmit('submit', 'Enter the lottery');

        $form->addHidden('module', 'hms');
        $form->addHidden('op', 'lottery_signup_submit');

        if(isset($error_msg)){
            $tpl['ERROR_MESSAGE'] = $error_msg;
        }
        
        $form->mergeTemplate($tpl);
        
        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/lottery_signup.tpl');
    }

    public function lottery_signup_submit()
    {
        # Make sure the agreed to terms checkbox was checked
        if(!isset($_REQUEST['terms_check'])){
            return Lottery_UI::show_lottery_signup('You must agree to the housing terms & conditions.');
        }

        # Make sure each of the user names is valid.
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        if(isset($_REQUEST['roommate1']) && !HMS_SOAP::is_valid_student($_REQUEST['roommate1'])){
            return Lottery_UI::lottery_signup("Error: '{$_REQUEST['roommate1']}' is not a valid ASU user name. Hint: Your roommate's user name is the first part of his/her email address.");
        }

        if(isset($_REQUEST['roommate2']) && !HMS_SOAP::is_valid_student($_REQUEST['roommate2'])){
            return Lottery_UI::lottery_signup("Error: '{$_REQUEST['roommate2']}' is not a valid ASU user name. Hint: Your roommate's user name is the first part of his/her email address.");
        }

        if(isset($_REQUEST['roommate3']) && !HMS_SOAP::is_valid_student($_REQUEST['roommate3'])){
            return Lottery_UI::lottery_signup("Error: '{$_REQUEST['roommate3']}' is not a valid ASU user name. Hint: Your roommate's user name is the first part of his/her email address.");
        }

        # Check for special needs
        if(isset($_REQUEST['special_need'])){
            return Lottery_UI::show_lottery_special_needs();
        }

        # Otherwise, call the 'lottery_signup' public function like normal
        return Lottery_UI::lottery_signup();
    }

    public function show_lottery_special_needs()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form();

        $form->addCheck('special_needs', array('physical_disability','psych_disability','medical_need','gender_need'));
        $form->setLabel('special_needs', array('Physical disability', 'Psychological disability', 'Medical need', 'Transgender housing'));

        if(isset($_REQUEST['special_needs'])){
            $form->setMatch('special_needs', $_REQUEST['special_needs']);
        }

        # Carry over all the fields submitted on the first page of the application
        $form->addHidden('roommate1',$_REQUEST['roommate1']);
        $form->addHidden('roommate2',$_REQUEST['roommate2']);
        $form->addHidden('roommate3',$_REQUEST['roommate3']);
        $form->addHidden('terms_check',$_REQUEST['terms_check']);
        $form->addHidden('special_need',$_REQUEST['special_need']); // pass it on, just in case the user needs to redo their application

        $form->addHidden('module', 'hms');
        $form->addHidden('type','student');
        $form->addHidden('op','lottery_signup_special_needs');
        
        $form->addSubmit('submit', 'Continue');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/special_needs.tpl');
    }

    public function lottery_signup()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');

        $tpl = array();
        $roommates = array(); // An array to hold the list of roommates for later processing

        $lottery_term = PHPWS_Settings::get('hms', 'lottery_term'); // save this for later because it's used all over the place

        # Check if the user is already entered for the lottery
        # if so, display the appropriate message
        $result = HMS_Lottery_Entry::check_for_entry($_SESSION['asu_username'], $lottery_term);
        if($result != FALSE && !PEAR::isError($result)){
            # Student already has a lottery entry
            return PHPWS_Template::process($tpl, 'hms', 'student/lottery_already_entered.tpl');
        }

        $entry = new HMS_Lottery_Entry();
        $entry->asu_username    = $_SESSION['asu_username'];
        $entry->term            = $lottery_term;

        $application_term = HMS_SOAP::get_application_term($_SESSION['asu_username']);

        if(!isset($application_term) || is_null($application_term)){
            # TODO: perhaps make this send the user to an improved contact form instead of just showing an error
            $tpl['ERROR']   = ""; // another dummy tag
            return PHPWS_Template::process($tpl, 'hms', 'student/lottery_signup_thankyou.tpl');
        }

        $entry->application_term = $application_term;

        $gender = HMS_SOAP::get_gender($_SESSION['asu_username'], TRUE);

        if($gender === FALSE || !isset($gender) || is_null($gender)){
            # TODO: perhaps make this send the user to an improved contact form instead of just showing an error
            $tpl['ERROR']   = ""; // another dummy tag
            return PHPWS_Template::process($tpl, 'hms', 'student/lottery_signup_thankyou.tpl');
        }

        $entry->gender = $gender;


        if(isset($_REQUEST['roommate1']) && $_REQUEST['roommate1'] != ''){
            $entry->roommate1_username = $_REQUEST['roommate1'];
            # Only insert roommate user name into array if it doesn't already exist, avoids
            # duplicate roommate user names and sending multiple invites emails to the same person
            if(!in_array($_REQUEST['roommate1'], $roommates)){
                $roommates[] = $_REQUEST['roommate1'];
            }
        }

        if(isset($_REQUEST['roommate2']) && $_REQUEST['roommate2'] != ''){
            $entry->roommate2_username = $_REQUEST['roommate2'];
            if(!in_array($_REQUEST['roommate2'], $roommates)){
                $roommates[] = $_REQUEST['roommate2'];
            }
        }

        if(isset($_REQUEST['roommate3']) && $_REQUEST['roommate3'] != ''){
            $entry->roommate3_username = $_REQUEST['roommate3'];
            if(!in_array($_REQUEST['roommate3'], $roommates)){
                $roommates[] = $_REQUEST['roommate3'];
            }
        }

        if(isset($_REQUEST['special_needs']['physical_disability'])){
            $entry->physical_disability = 1;
        }

        if(isset($_REQUEST['special_needs']['psych_disability'])){
            $entry->psych_disability = 1;
        }

        if(isset($_REQUEST['special_needs']['medical_need'])){
            $entry->medical_need = 1;
        }

        if(isset($_REQUEST['special_needs']['gender_need'])){
            $entry->gender_need = 1;
        }

        $tpl['LOGOUT'] = PHPWS_Text::secureLink('Log Out', 'users', array('action'=>'user', 'command'=>'logout'));

        $result = $entry->save();

        if(!$result){
            $tpl['ERROR']   = ""; // dummy tag, set to turn display of a template section on/off.
            return PHPWS_Template::process($tpl, 'hms', 'student/lottery_signup_thankyou.tpl');
        }

        $tpl['SUCCESS'] = ""; // dummy tag, set to turn display of a template section on/off.

        # Log the fact that the entry was saved
        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_LOTTERY_ENTRY, $_SESSION['asu_username']);

        # Sanity checks on the preferred roommate user names
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $requestor_name = HMS_SOAP::get_name($_SESSION['asu_username']);
        $year = HMS_Term::term_to_text($lottery_term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($lottery_term),TRUE);
        foreach($roommates as $roomie){
            # Check for invalid chars
            if(!PHPWS_Text::isValidInput($roomie)){
                return Lottery_UI::show_lottery_signup("You entered an invalid user name. Please try again.");
            }

            # Check banner to make sure the user name is valid
            if(!HMS_SOAP::is_valid_student($roomie)){
                return Lottery_UI::show_lottery_signup("\"$roommie\" is not a valid user name. Please try again.");
            }

            # Check to make sure the user name is not the same as the current user
            if($roomie == $_SESSION['asu_username']){
                return Lottery_UI::show_lottery_signup("You cannot choose yourself as a roommate, please try again.");
            }
        }

        # If all those roommate names are ok, then send them all invite emails if they're not already entered in the lottery
        foreach($roommates as $roomie){
            if(HMS_Lottery_Entry::check_for_entry($roomie, $lottery_term) === FALSE){
                HMS_Email::send_signup_invite($roomie, HMS_SOAP::get_name($roomie), $requestor_name, $year);
                HMS_Activity_Log::log_activity($roomie, ACTIVITY_LOTTERY_SIGNUP_INVITE, $_SESSION['asu_username']); // log that we sent this invite
            }
        }

        HMS_Email::send_lottery_application_confirmation($_SESSION['asu_username'], null);
        
        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_signup_thankyou.tpl');
    }

    public function show_select_residence_hall()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $tpl['TERM'] = HMS_Term::term_to_text($term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($term),TRUE);
        
        $halls = HMS_Residence_Hall::get_halls($term);

        $output_list = array();

        foreach($halls as $hall){
            $row = array();
            $row['HALL_NAME']       = $hall->hall_name;
            $row['ROW_TEXT_COLOR']  = 'black';

            $rooms_used = $hall->count_lottery_full_rooms();
            # If we've used up the number of allotted rooms, then remove this hall from the list
            if($rooms_used >= $hall->rooms_for_lottery){
                $row['ROW_TEXT_COLOR'] = 'grey';
                $tpl['hall_list'][] = $row;
                continue;
            }
            
            # Make sure we have a room of the specified gender available in the hall (or a co-ed room)
            if($hall->count_avail_lottery_rooms(HMS_SOAP::get_gender($_SESSION['asu_username'],TRUE)) <= 0 &&
               $hall->count_avail_lottery_rooms(COED) <= 0){
                $row['ROW_TEXT_COLOR'] = 'grey';
                $tpl['hall_list'][] = $row;
                continue;
            }

            $row['HALL_NAME']   = PHPWS_Text::secureLink($hall->hall_name, 'hms', array('type'=>'student', 'op'=>'lottery_select_floor', 'residence_hall'=>$hall->id));
            $tpl['hall_list'][] = $row;
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_hall.tpl');
    }

    public function show_select_floor()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');

        $hall = new HMS_Residence_Hall($_REQUEST['residence_hall']);
        $hall_rooms_for_lottery = $hall->rooms_for_lottery;
        $hall_rooms_used        = $hall->count_lottery_used_rooms();

        $tpl['HALL']            = $hall->hall_name;

        if(isset($hall->exterior_image_id)){
            $tpl['EXTERIOR_IMAGE']  = Cabinet::getTag($hall->exterior_image_id);
        }

        if(isset($hall->room_plan_image_id)){
            $file = Cabinet::getFile($hall->room_plan_image_id);
            $tpl['ROOM_PLAN_IMAGE'] = $file->parentLinked();
        }

        if(isset($hall->map_image_id)){
            $tpl['MAP_IMAGE']       = Cabinet::getTag($hall->map_image_id);
        }

        if(isset($hall->other_image_id)){
            $file = Cabinet::getFile($hall->other_image_id);
            $tpl['OTHER_IMAGE'] = $file->parentLinked();
        }

        $floors = $hall->get_floors();

        foreach($floors as $floor){
            $used_rooms = $floor->count_lottery_used_rooms();
            $full_rooms = $floor->count_lottery_full_rooms();

            $row = array();

            if($floor->count_avail_lottery_rooms(HMS_SOAP::get_gender($_SESSION['asu_username'], TRUE)) <= 0 &&
               $floor->count_avail_lottery_rooms(COED) <= 0){
                $row['FLOOR']           = HMS_Util::ordinal($floor->floor_number);
                $row['ROW_TEXT_COLOR']  = 'grey';
                $tpl['floor_list'][]    = $row;
                continue;
            }

            if($hall_rooms_used >= $hall_rooms_for_lottery && $full_rooms >= $used_rooms){
                $row['FLOOR']           = HMS_Util::ordinal($floor->floor_number);
                $row['ROW_TEXT_COLOR']  = 'grey';
                $tpl['floor_list'][]    = $row;
                continue;
            }

            $row['FLOOR']           = PHPWS_Text::secureLink(HMS_Util::ordinal($floor->floor_number) . ' floor', 'hms', array('type'=>'student', 'op'=>'lottery_select_room', 'floor'=>$floor->id));
            $row['ROW_TEXT_COLOR']  = 'grey';
            $tpl['floor_list'][]    = $row;
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_floor.tpl');
    }

    public function show_select_room()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $floor  = new HMS_Floor($_REQUEST['floor']);
        $hall   = $floor->get_parent();

        $full_rooms = $hall->count_lottery_full_rooms();
        $used_rooms = $hall->count_lottery_used_rooms();

        $tpl['HALL_FLOOR'] = $floor->where_am_i();

        if(isset($floor->floor_plan_image_id)){
            $file = Cabinet::getFile($floor->floor_plan_image_id);
            $tpl['FLOOR_PLAN_IMAGE'] = $file->parentLinked();
        }

        $rooms = $floor->get_rooms();

        $tpl['room_list'] = array();

        foreach($rooms as $room){
            $row = array();

            $num_avail_beds = $room->count_avail_lottery_beds();
            $total_beds     = $room->get_number_of_beds();

            // We list the room dispite whether it's actually available to choose or not,
            // so decide whether to "gray out" this row in the room list or not
            if($room->gender_type != HMS_SOAP::get_gender($_SESSION['asu_username'], TRUE) 
                || $num_avail_beds     == 0 
                || $room->is_reserved  == 1 
                || $room->is_online    == 0 
                || $room->private_room == 1 
                || $room->ra_room      == 1 
                || $room->is_overflow  == 1){
        
                // Show a grayed out row and no link
                $row['ROOM_NUM']        = $room->room_number;
                $row['ROW_TEXT_COLOR']  = 'grey';
                $row['AVAIL_BEDS']      = 0; // show 0 available beds since this room is unavailable to the user
            
            }else if($used_rooms >= $hall->rooms_for_lottery && $num_avail_beds == $total_beds){
                // Check for if we've reached the room cap, and this room isn't partially used
                // Show a grayed out row and no link
                $row['ROOM_NUM']        = $room->room_number;
                $row['ROW_TEXT_COLOR']  = 'grey';
                $row['AVAIL_BEDS']      = 0; // show 0 available beds since this room is unavailable to the user

            }else{
                // Show the room number as a link
                $row['ROOM_NUM']        = PHPWS_Text::secureLink($room->room_number, 'hms', array('type'=>'student', 'op'=>'lottery_select_roommates', 'room'=>$room->id));
                $row['ROW_TEXT_COLOR']  = 'black';
                $row['AVAIL_BEDS']      = $num_avail_beds;
            }

            $row['NUM_BEDS']    = $room->get_number_of_beds();

            $tpl['room_list'][] = $row;
        }



        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_room.tpl');
    }

    public function show_select_roommates($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');

        javascript('/jquery/');

        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $tpl = array();

        #TODO: place a temporary reservation on the entire room

        # Grab all of their preferred roommates
        $db = &new PHPWS_DB('hms_lottery_entry');

        $db->addColumn('roommate1_username');
        $db->addColumn('roommate2_username');
        $db->addColumn('roommate3_username');

        $db->addWhere('asu_username', $_SESSION['asu_username']);
        $results = $db->select('row');

        # If all the roommate usernames are null, show the "no roommate specified" message
        if(is_null($results['roommate1_username']) && is_null($results['roommate2_username']) && is_null($results['roommate3_username'])){
            $tpl['NO_ROOMMATES'] = "";
        }

        foreach($results as $roommate)
        {
            # Skip null roommates
            if(is_null($roommate)){
                continue; 
            }


            $status = array();

            $status['NAME'] = HMS_SOAP::get_name($roommate['asu_username']);

            if(HMS_Lottery_Entry::check_for_entry($roommate, $term) === FALSE){
                $status['STATUS'] = 'Did not enter lottery.';
                $status['COLOR'] = 'red';
            }else if(!is_null(HMS_Assignment::get_assignment($roommate, $term))){
                $status['STATUS'] = 'Already assigned.';
                $status['COLOR'] = 'red';
            }else{
                $status['STATUS'] = "<a href=\"\" onClick=\"choose_roommate('$roommate'); return false;\">Choose this roommate</a>";
                $status['COLOR'] = 'green';
            }

            $tpl['roommate_status'][] = $status;
        }

        # List each bed in the room and if it's available, assigned, or reserved
        $room = new HMS_Room($_REQUEST['room']);
        $beds = $room->get_beds();

        $tpl['ROOM'] = $room->where_am_i();

        $form = &new PHPWS_Form();
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'lottery_show_confirm_roommates');
        $form->addHidden('room', $_REQUEST['room']);

        $assigned_self = FALSE; // Whether or not we've placed *this* student in a bed yet

        // Search the request to see if the student has already assigned themselves previously (this is only used if the user is being
        // set back from a subsequent page after an error).
        if(isset($_REQUEST['roommates']) && !(array_search($_SESSION['asu_username'], $_REQUEST['roommates']) === FALSE)){
            $assigned_self = TRUE;
        }

        for($i = 0; $i < count($beds); $i++){
            $bed = $beds[$i];
            $bed_row = array();

            $bed_row['BEDROOM_LETTER']  = $bed->bedroom_label;

            # Check for an assignment
            $bed->loadAssignment();
            # Check for a reservation
            $reservation = $bed->get_lottery_reservation_info();

            if($bed->_curr_assignment != NULL){
                # Bed is assigned, so show who's in it
                $bed_row['TEXT'] = HMS_SOAP::get_name($bed->_curr_assignment->asu_username) . ' (assigned)';
            }else if($reservation != NULL){
                # Bed is reserved
                $bed_row['TEXT'] = HMS_SOAP::get_name($reservation['asu_username']) . ' (unconfirmed invitation)';
            }else{
                # Bed is empty, so decide what we should do with it
                if(isset($_REQUEST['roommates'][$bed->id])){
                    # The user already submitted the form once, put the value in the request in the text box by default
                    $bed_row['TEXT'] = "<input type=\"text\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\" value=\"{$_REQUEST['roommates'][$bed->id]}\">@appstate.edu";
                }else if(!$assigned_self){
                    # No value in the request, this bed is empty, and this user hasn't been assigned anywhere yet
                    # So put their user name in this field by default
                    $bed_row['TEXT'] = "<input type=\"text\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\" value=\"{$_SESSION['asu_username']}\">@appstate.edu";
                    $assigned_self = TRUE;
                }else{
                    $bed_row['TEXT'] = "<input type=\"text\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\">@appstate.edu";
                }
            }

            $tpl['beds'][] = $bed_row;
        }

        # Decide which meal plan drop box to show based on whether or not the chosen room
        # is in a hall which requires a meal plan
        $floor  = $room->get_parent();
        $hall   = $floor->get_parent();
        if($hall->meal_plan_required == 0){
            $form->addDropBox('meal_plan', array(BANNER_MEAL_NONE   =>_('None'),
                                                 BANNER_MEAL_LOW    =>_('Low'),
                                                 BANNER_MEAL_STD    =>_('Standard'),
                                                 BANNER_MEAL_HIGH   =>_('High'),
                                                 BANNER_MEAL_SUPER  =>_('Super')));
        }else{
            $form->addDropBox('meal_plan', array(BANNER_MEAL_LOW    =>_('Low'),
                                                 BANNER_MEAL_STD    =>_('Standard'),
                                                 BANNER_MEAL_HIGH   =>_('High'),
                                                 BANNER_MEAL_SUPER  =>_('Super')));
            $form->setMatch('meal_plan', BANNER_MEAL_STD);
        }

        $form->addSubmit('submit_form', 'Review Roommate & Room Selection');
       
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_select_roommate.tpl');
    }

    public function show_confirm_roommates($error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $roommates = $_REQUEST['roommates'];
        $term = PHPWS_Settings::get('hms', 'lottery_term');

        # Put everything into lowercase before we get started
        foreach($roommates as $key => $username){
            $roommates[$key] = strtolower($username);
        }

        /**
         * Sanity checking
         */
         
        # Make sure the student assigned his/her self to a bed
        if(!in_array($_SESSION['asu_username'], $roommates)){
            return Lottery_UI::show_select_roommates("You must assign yourself to a bed. Please try again.");
        }

        # Get a count of how many times each user name appears
        $counts = array_count_values($roommates);

        foreach($roommates as $roommate){
            if($roommate == NULL || $roommate == ''){
                continue;
            }

            # Make sure this user name only appears once
            if($counts[$roommate] > 1){
                return Lottery_UI::show_select_roommates("$roommate may only be assigned to one bed. Please try again.");
            }

            # Make sure every user name is a valid student
            if(!HMS_SOAP::is_valid_student($roommate)){
                return Lottery_UI::show_select_roommates("$roommate is not a valid user name. Please try again.");
            }

            # Make sure none of the students are type freshmen
            if(HMS_SOAP::get_student_type($roommate, $term) != TYPE_CONTINUING){
                return Lottery_UI::show_select_roommates("$roommate is not a continuing student. Only continuing students (i.e. not a first semester freshmen) may be selected as roommates. Please select a different roommate.");
            }

            # Make sure every student entered the lottery
            if(HMS_Lottery_Entry::check_for_entry($roommate, $term) === FALSE){
                return Lottery_UI::show_select_roommates("$roommate did not enter the lottery. Please select a different roommate.");
            }

            # Make sure every student's gender matches, and that those are compatible with the room
            if(HMS_SOAP::get_gender($roommate) != HMS_SOAP::get_gender($_SESSION['asu_username'])){
                return Lottery_UI::show_select_roommates("$roommate is not the same gender as you. Please choose a roommate of the same gender.");
            }

            # Make sure none of the students are assigned yet
            if(HMS_Assignment::check_for_assignment($roommate, $term) === TRUE){
                return Lottery_UI::show_select_roommates("$roommate is already assigned to a room. Please choose a different roommate.");
            }
        }

        $tpl = array();

        $form = &new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'lottery_confirmed');
        $form->addHidden('room', $_REQUEST['room']);

        # Add the beds and user names back to the form so they end up in the request in a pretty way
        foreach($roommates as $key => $value){
            if(isset($value) && $value != ''){
                $form->addHidden("roommates[$key]", $value);
            }
        }

        # List the student's room
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        $room = &new HMS_Room($_REQUEST['room']);
        $tpl['ROOM'] = $room->where_am_i();

        # List all the students which will be assigned and their beds
        $beds = $room->get_beds();
        
        foreach($beds as $bed){
            $bed_row = array();

            # Check for an assignment
            $bed->loadAssignment();
            # Check for a reservation
            $reservation = $bed->get_lottery_reservation_info();
            
            $bed_row['BEDROOM_LETTER']  = $bed->bedroom_label;

            if($bed->_curr_assignment != NULL){
                # Bed is assigned
                $bed_row['TEXT'] = HMS_SOAP::get_name($bed->_curr_assignment->asu_username);
            }else if($reservation != NULL){
                # Bed is reserved
                $bed_row['TEXT'] = HMS_SOAP::get_name($reservation['asu_username']) . ' (reserved)';
            }else{
                # Get the new roommate name out of the request
                if(!isset($roommates[$bed->id]) || $roommates[$bed->id] == ''){
                    $bed_row['TEXT'] = 'Empty';
                }else{
                    $bed_row['TEXT'] = HMS_SOAP::get_name($roommates[$bed->id]) . ' ' . $roommates[$bed->id];
                }
            }

            $tpl['beds'][] = $bed_row;
        }

        # Show the meal plan
        $tpl['MEAL_PLAN'] = HMS_Util::formatMealOption($_REQUEST['meal_plan']);
        $form->addHidden('meal_plan', $_REQUEST['meal_plan']);
 
        PHPWS_Core::initCoreClass('Captcha.php');
        $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());

        $form->addSubmit('submit_form', 'Confirm room & roommates');

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/lottery_confirm.tpl');
    }

    public function show_confirmed()
    {
        PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE); // returns the words entered if correct, FALSE otherwise
        if($captcha === FALSE) {
            return Lottery_UI::show_confirm_roommates('Sorry, please try again.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $room = &new HMS_Room($_REQUEST['room']);
        $hall_room = $room->where_am_i(); // get hall and room number description for later

        $roommates = $_REQUEST['roommates'];

        foreach($roommates as $bed_id => $username){
            # Make sure the bed is still empty
            $bed = &new HMS_Bed($bed_id);
            
            if($bed->has_vacancy() != TRUE){
                return Lottery_UI::show_select_roommates('One or more of the beds in the room you selected is no longer available. Please try again.');
            }

            # Make sure none of the needed beds are reserved
            if($bed->is_lottery_reserved()){
                return Lottery_UI::show_select_roommates('One or more of the beds in the room you selected is no longer available. Please try again.');
            }

            # Double check the genders
            if($room->gender_type != COED && HMS_SOAP::get_gender($username, TRUE) != $room->gender_type){
                return Lottery_UI::show_select_roommates("$username is a different gender. Please choose a roommate of the same gender.");
            }

            # Double check the students' elligibilities
            #TODO: determine what housing wants to do here
        }

        # Assign the student to the requested bed
        $bed_id = array_search($_SESSION['asu_username'], $roommates); // Find the bed id of the student who's logged in
        $result = HMS_Assignment::assign_student($_SESSION['asu_username'], PHPWS_Settings::get('hms', 'lottery_term'), NULL, $bed_id, $_REQUEST['meal_plan'], 'Confirmed lottery invite', TRUE);

        if($result != E_SUCCESS){
            return Lottery_UI::show_select_roommates('Sorry, there was an error creating your room assignment. Please try again or contact Housing & Residence Life');
        }

        # Log the assignment
        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_LOTTERY_ROOM_CHOSEN, $_SESSION['asu_username'], 'Captcha: ' . $captcha);

        $requestor_name = HMS_SOAP::get_name($_SESSION['asu_username']);

        foreach($roommates as $bed_id => $username){
            // Skip the current user
            if($username == $_SESSION['asu_username']){
                continue;
            }

            # Reserve the bed for the roommate
            $expires_on = mktime() + ROOMMATE_INVITE_TTL;
            $bed = &new HMS_Bed($bed_id);
            if(!$bed->lottery_reserve($username, $_SESSION['asu_username'], $expires_on)){
                $tpl['ERROR_MSG'] = "You were assigned, but there was a problem reserving space for your roommates. Please contact Housing & Residence Life.";
                return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_room_thanks.tpl');
            }

            HMS_Activity_Log::log_activity($username, ACTIVITY_LOTTERY_REQUESTED_AS_ROOMMATE, $_SESSION['asu_username'], 'Expires: ' . HMS_Util::get_long_date_time($expires_on));

            # Invite the selected roommates
            $name = HMS_SOAP::get_name($username);
            $term = PHPWS_Settings::get('hms', 'lottery_term');
            $year = HMS_Term::term_to_text($term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($term),TRUE);
            HMS_Email::send_lottery_roommate_invite($username, $name, $expires_on, $requestor_name, $hall_room, $year);
        }

        $tpl['SUCCESS_MSG'] = "Congratulations, you have been assigned to $hall_room. An email invite has been sent to each of your requested roommates.";

        //Send a confirmation email
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::send_lottery_assignment_confirmation($_SESSION['asu_username'], $hall->hall_name . ' ' . $room->room_number);

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_room_thanks.tpl');
    }

    public function show_lottery_roommate_request()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        # Get the roommate request record from the database
        $invite = HMS_Lottery::get_lottery_roommate_invite($_SESSION['asu_username'], PHPWS_Settings::get('hms', 'lottery_term'));
        $bed = new HMS_Bed($invite['bed_id']);
        $room = $bed->get_parent();

        $tpl = array();

        $tpl['REQUESTOR']       = HMS_SOAP::get_name($invite['requestor']);
        $tpl['HALL_ROOM']      = $bed->where_am_i();

        # List all the students which will be assigned and their beds
        $beds = $room->get_beds();
        
        foreach($beds as $bed){
            $bed_row = array();

            # Check for an assignment
            $bed->loadAssignment();
            # Check for a reservation
            $reservation = $bed->get_lottery_reservation_info();
            
            $bed_row['BEDROOM_LETTER']  = $bed->bedroom_label;

            if($bed->_curr_assignment != NULL){
                # Bed is assigned
                $bed_row['TEXT'] = HMS_SOAP::get_name($bed->_curr_assignment->asu_username);
            }else if($reservation != NULL){
                # Bed is reserved
                $bed_row['TEXT'] = HMS_SOAP::get_name($reservation['asu_username']) . ' (reserved)';
            }else{
                $bed_row['TEXT'] = 'Empty';
            }

            $tpl['beds'][] = $bed_row;
        }

        $form = &new PHPWS_Form();

        # Decide which meal plan drop box to show based on whether or not the chosen room
        # is in a hall which requires a meal plan
        $floor  = $room->get_parent();
        $hall   = $floor->get_parent();
        if($hall->meal_plan_required == 0){
            $form->addDropBox('meal_plan', array(BANNER_MEAL_NONE   =>_('None'),
                                                 BANNER_MEAL_LOW    =>_('Low'),
                                                 BANNER_MEAL_STD    =>_('Standard'),
                                                 BANNER_MEAL_HIGH   =>_('High'),
                                                 BANNER_MEAL_SUPER  =>_('Super')));
        }else{
            $form->addDropBox('meal_plan', array(BANNER_MEAL_LOW    =>_('Low'),
                                                 BANNER_MEAL_STD    =>_('Standard'),
                                                 BANNER_MEAL_HIGH   =>_('High'),
                                                 BANNER_MEAL_SUPER  =>_('Super')));
            $form->setMatch('meal_plan', BANNER_MEAL_STD);
        }

        $form->addSubmit('continue', 'Continue');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'lottery_show_confirm_roommate_request');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_roommate_request.tpl');
    }

    public function show_confirm_lottery_roommate_request($error_msg)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        # Get the roommate request record from the database
        $invite = HMS_Lottery::get_lottery_roommate_invite($_SESSION['asu_username'], PHPWS_Settings::get('hms', 'lottery_term'));
        $bed = new HMS_Bed($invite['bed_id']);
        $room = $bed->get_parent();

        $tpl = array();

        $tpl['REQUESTOR']       = HMS_SOAP::get_name($invite['requestor']);
        $tpl['HALL_ROOM']       = $bed->where_am_i();

        # List all the students which will be assigned and their beds
        $beds = $room->get_beds();
        
        foreach($beds as $bed){
            $bed_row = array();

            # Check for an assignment
            $bed->loadAssignment();
            # Check for a reservation
            $reservation = $bed->get_lottery_reservation_info();
            
            $bed_row['BEDROOM_LETTER']  = $bed->bedroom_label;

            if($bed->_curr_assignment != NULL){
                # Bed is assigned
                $bed_row['TEXT'] = HMS_SOAP::get_name($bed->_curr_assignment->asu_username);
            }else if($reservation != NULL){
                # Bed is reserved
                $bed_row['TEXT'] = HMS_SOAP::get_name($reservation['asu_username']) . ' (reserved)';
            }else{
                $bed_row['TEXT'] = 'Empty';
            }

            $tpl['beds'][] = $bed_row;
        }

        $tpl['MEAL_PLAN'] = HMS_Util::formatMealOption($_REQUEST['meal_plan']);
        
        PHPWS_Core::initCoreClass('Captcha.php');
        $tpl['CAPTCHA'] = Captcha::get();

        if(isset($error_msg)){
            $tpl['ERROR_MSG'] = $error_msg;
        }
        
        $form = new PHPWS_Form();
        $form->addHidden('meal_plan', $_REQUEST['meal_plan']);

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'lottery_confirm_roommate_request');

        $form->addSubmit('confirm', 'Confirm Roommate');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_confirm_roommate_request.tpl');
    }

    public function handle_lottery_roommate_confirmation()
    {
        # Confirm the captcha
        PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE);
        if($captcha === FALSE){
            return Lottery_UI::show_confirm_lottery_roommate_request('The words you entered were incorrect. Please try again.');
        }

        # Try to actually make the assignment
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        $result = HMS_Lottery::confirm_roommate_request($_SESSION['asu_username'], $_REQUEST['meal_plan']);
        if($result != E_SUCCESS){
            return Lottery_UI::show_confirm_lottery_roommate_request('Sorry, there was an error confirming your roommate invitation. Please contact Housing & Residence Life.');
        }

        # Log the fact that the roommate was accepted and successfully assigned
        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_LOTTERY_CONFIRMED_ROOMMATE,$_SESSION['asu_username'], "Captcha: \"$captcha\"");
        
        $invite = HMS_Lottery::get_lottery_roommate_invite($_SESSION['asu_username'], PHPWS_Settings::get('hms', 'lottery_term'));
        $bed = new HMS_Bed($invite['bed_id']);

        $tpl['SUCCESS'] = 'Your roommate request was successfully confirmed. You have been assigned to ' . $bed->where_am_i() . ".";

        return PHPWS_Template::process($tpl, 'hms', 'student/student_success_failure_message.tpl');
    }

    public function show_admin_entry($message = null)
    {
        $tpl = array();
        $tpl['MESSAGE'] = $message;

        $form = &new PHPWS_Form('admin_entry');
        $form->addText('asu_username');
        $form->setLabel('asu_username', 'ASU Username');

        $form->addCheck('physical_disability');
        $form->setLabel('physical_disability', 'Physical Disability');

        $form->addCheck('psych_disability');
        $form->setLabel('psych_disability', 'Psychological Disability');

        $form->addCheck('medical_need');
        $form->setLabel('medical_need', 'Medical Need');

        $form->addCheck('gender_need');
        $form->setLabel('gender_need', 'Gender Need');

        $form->addHidden('type',    'lottery');
        $form->addHidden('op',      'submit_admin_entry');
        $form->addSubmit('enter_into_lottery', 'Add to lottery');

        $form->mergeTemplate($tpl);

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/add_to_lottery.tpl');
    }
}
?>
