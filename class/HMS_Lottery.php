<?php

define('MAX_INVITES_PER_BATCH', 75);
define('INVITE_TTL_HRS', 96);

class HMS_Lottery {


    function run_lottery()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        require_once(PHPWS_SOURCE_DIR . '/mod/hms/inc/accounts.php');


        /******************
         * Initialization *
         ******************/
        HMS_Activity_Log::log_activity(HMS_ADMIN_USER, ACTIVITY_LOTTERY_EXECUTED, HMS_ADMIN_USER);

        # One-time date/time calculations, setup for later on
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $term_year = HMS_Term::get_term_year($term);
        $now = mktime();
        $expire_time = $now + (INVITE_TTL_HRS * 3600);
        $year = HMS_Term::term_to_text($term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($term),TRUE);

        $output = array(); // An array for holding the text output, one line per array element.

        /*******************
         * Reminder Emails *
         *******************/
        $output[] = "Lottery system invoked on " . date("d M, Y @ g:i:s", $now) . " ($now)";
        
        $output[] = "Sending invite reminder emails...";
        HMS_Lottery::send_winning_reminder_emails($term);

        $output[] = "Sending roommate invite reminder emails";
        HMS_Lottery::send_roommate_reminder_emails($term);

        /*****************
         * Invite totals *
         *****************/
        $output[] = 'Counting invites sent so far... ';

        # Count the number of invites sent (outstanding or confirmed) per class
        $senior_invites_sent = HMS_Lottery::count_invites_by_class($term, CLASS_SENIOR);
        $junior_invites_sent = HMS_Lottery::count_invites_by_class($term, CLASS_JUNIOR);
        $soph_invites_sent   = HMS_Lottery::count_invites_by_class($term, CLASS_SOPHOMORE);

        if($senior_invites_sent === FALSE || $junior_invites_sent === FALSE || $soph_invites_sent === FALSE){
            $output[] = 'Error counting previously sent invites!';
            HMS_Lottery::lottery_complete('FAILED', $output);
        }else{
            $output[] = "$senior_invites_sent senior invites previously sent";
            $output[] = "$junior_invites_sent junior invites previously sent";
            $output[] = "$soph_invites_sent sophomore invites previously sent";
        }

        # Count the number of outstanding female invites
        $female_invites_outstanding = HMS_Lottery::count_outstanding_invites($term, FEMALE);
        if($female_invites_outstanding === FALSE){
            $output[] = 'error counting outstanding female invites';
            HMS_Lottery::lottery_complete("FAILED", $output);
        }else{
            $output[] = "$female_invites_outstanding female invites outstanding";
        }

        # Count the number of outstanding male invites
        $male_invites_outstanding = HMS_Lottery::count_outstanding_invites($term, MALE);
        if($male_invites_outstanding === FALSE){
            $output[] = 'error counting outstanding male invites';
            HMS_Lottery::lottery_complete("FAILED", $output);
        }else{
            $output[] = "$male_invites_outstanding male invites oustanding";
        }

        # Get a total number of invites outstanding
        $outstanding_invite_count = $male_invites_outstanding + $female_invites_outstanding;

        $output[] = "$outstanding_invite_count total invites outstanding";

        # Get the total number of outstanding roommate invites
        $outstanding_roommate_invites = HMS_Lottery::count_outstanding_roommate_invites($term);

        if($outstanding_roommate_invites === FALSE){
            $output[] = 'error counting outstanding roommate invites';
            HMS_Lottery::lottery_complete("FAILED", $output);
        }else{
            $output[] = "$outstanding_roommate_invites outstanding roommate invites";
        }


        /**************
         * Bed Totals *
         **************/

        # Get the halls
        $halls = HMS_Residence_Hall::get_halls($term);

        $output[] = "Checking remaining rooms...";
        $remaining_rooms        = 0;
        $remaining_coed_rooms   = 0;
        $remaining_male_rooms   = 0;
        $remaining_female_rooms = 0;
        # Foreach hall
        foreach($halls as $hall){
            $output[] = "Checking $hall->hall_name";
            # Get the number of rooms allowed for the lottery
            $lottery_rooms = $hall->rooms_for_lottery;
            $output[] = "$lottery_rooms rooms reserved for lottery";

            # Get the number of totally full rooms in this hall
            $full_rooms = $hall->count_lottery_full_rooms();

            if($full_rooms === FALSE){
                $output[] = 'Error while counting full rooms.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            $output[] = "$full_rooms full lottery rooms";

            # Get the number of used rooms in this hall
            $used_rooms = $hall->count_lottery_used_rooms();

            if($used_rooms === FALSE){
                $output[] = 'Error while counting full rooms. Check the error logs.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            $output[] = "$used_rooms lottery rooms used";

            # Calculate the remaining number of rooms allowed for the lottery in this hall
            $remaining_rooms_this_hall = $lottery_rooms - $full_rooms;

            $output[] = "$remaining_rooms_this_hall remaining rooms available for lottery";

            if($remaining_rooms_this_hall == 0){
                continue;
            }

            # Count the number of non-full male/female rooms in this hall
            $female_rooms_this_hall = $hall->count_avail_lottery_rooms(FEMALE);
            if($female_rooms_this_hall === FALSE){
                $output[] = 'Error counting non-full female rooms.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            $male_rooms_this_hall = $hall->count_avail_lottery_rooms(MALE);
            if($male_rooms_this_hall === FALSE){
                $output[] = 'Error counting non-full male rooms.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            # Count the number of co-ed rooms
            $coed_rooms_this_hall = $hall->count_avail_lottery_rooms(COED);
            if($coed_rooms_this_hall === FALSE){
                $output[] = 'Error counting non-full co-ed rooms.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            $output[] = "$coed_rooms_this_hall remaining coed rooms";
            $output[] = "$male_rooms_this_hall remaining male rooms";
            $output[] = "$female_rooms_this_hall remaining female rooms";

            # Add that number to to total number of lottery invites to send
            $remaining_rooms += $remaining_rooms_this_hall;


            $remaining_coed_rooms   += $coed_rooms_this_hall;
            $remaining_male_rooms   += $male_rooms_this_hall;
            $remaining_female_rooms += $female_rooms_this_hall;

        }

        $output[] = "$remaining_rooms remaining lottery rooms total";
        $output[] = "$remaining_coed_rooms total remaining coed rooms";
        $output[] = "$remaining_male_rooms total remaining male rooms";
        $output[] = "$remaining_female_rooms total remaining female rooms";

        # If there are no free rooms and no outstanding invites, then we're done
        # This takes into account outstanding roommate invites, which if they fail would mean we'd need another lottery round
        if($remaining_rooms <= 0 && $outstanding_invite_count <= 0 && $outstanding_roommate_invites <= 0){
            $output[] = 'No remaining rooms and no outstanding invites, done!';
            HMS_Lottery::lottery_complete('SUCCESS', $output, TRUE);
        }

        if($remaining_rooms <= 0){
            $output[] = 'No remaining rooms, but there are outstanding invites, quitting for now.';
            HMS_Lottery::lottery_complete('SUCCESS', $output);
        }


        # Calculate the number of new invites that can be sent
        # If there are co-ed rooms, then only send as many invites as there are co-ed rooms.
        # TODO: move this inside the for loop below
        if($remaining_coed_rooms > 0){
            $invites_to_send = $remaining_coed_rooms - $outstanding_invite_count;
            $co_ed_only = TRUE;
            $output[] = "Co-ed rooms remaining, can send $invites_to_send co-ed invites";
        }else{
            $invites_to_send = $remaining_rooms - $outstanding_invite_count;
            $co_ed_only = FALSE;

            # Calculate the maximum number of male/female invites we can send
            $male_invites_avail     = $remaining_male_rooms - $male_invites_outstanding;
            $female_invites_avail  = $remaining_female_rooms - $female_invites_outstanding;

            $output[] = "No co-ed rooms remaining, can send $invites_to_send invites";
            $output[] = "$male_invites_avail male invites available";
            $output[] = "$female_invites_avail female invites available";
        }

        # Make sure we aren't sending more than our max at once
        if($invites_to_send > MAX_INVITES_PER_BATCH){
            $invites_to_send = MAX_INVITES_PER_BATCH;
            $output[] = "Batch size limited to $invites_to_send";
        }

        $output[] = "Sending $invites_to_send new invites";

        # Count the number of remaining entries
        $remaining_entries = HMS_Lottery::count_remaining_entries($term);

        if($remaining_entries === FALSE){
            $output[] = 'Error counting outstanding lottery entires, quitting.';
            HMS_Lottery::lottery_complete('FAILED', $output);
        }

        # Setup the lottery type
        $lottery_type = PHPWS_Settings::get('hms', 'lottery_type');

        # Setup the percentages for weighting by class
        $soph_percent   = PHPWS_Settings::get('hms', 'lottery_per_soph');
        $jr_percent     = PHPWS_Settings::get('hms', 'lottery_per_jr');
        $senior_percent = PHPWS_Settings::get('hms', 'lottery_per_senior');

        # Setup the max invites counts for multi-phase lottery
        $soph_max_invites   = PHPWS_Settings::get('hms', 'lottery_max_soph');
        $jr_max_invites     = PHPWS_Settings::get('hms', 'lottery_max_jr');
        $senior_max_invites = PHPWS_Settings::get('hms', 'lottery_max_senior');

        # Select the appropriate number of new winners
        for($i=0; $i < $invites_to_send; $i++){

            # Make sure we have students left who need to win
            if($num_remaining_entries == 0){
                $output[] = 'No entries remaining, quitting!';
                HMS_Lottery::lottery_complete('SUCCESS', $output, TRUE);
            }

            $output[] = "$remaining_entries entries remaining"; 

            $winning_row = NULL;
            while(is_null($winning_row)){

                # Decide which gender we need to invite
                if($co_ed_only){
                   $gender = COED; 
                }else{
                    # Decide if we need to pick a male, female, or either
                    if($male_invites_avail > 0 && $female_invites_avail > 0){
                        $gender = COED;
                    }else if($male_invites_avail > 0){
                        $gender = MALE;
                    }else if($female_invites_avail > 0){
                        $gender = FEMALE;
                    }
                }

                # Decide which class we need to invite
                #TODO: 'AND' these conditions with the number of invites for that class being greater than 0 to prevent infinite loops
                if($lottery_type == 'single_phase'){
                    # Using a single-phase lottery, so choose which application term to use based on class weights
                    # Choose a random number
                    $random_num = mt_rand(0, 100);

                    if($random_num < $soph_percent){
                        $class = CLASS_SOPHOMORE;
                    }else if($random_num >= $soph_percent && $random_num < ($soph_percent + $jr_percent)){
                        $class = CLASS_JUNIOR;
                    }else{
                        $class = CLASS_SENIOR;
                    }
                }else{
                    # Using a multi-phase lottery, so determine which phase we're in
                    #TODO: 'AND' these conditions with the number of invites for that class being greater than 0 to prevent infinite loops
                    if($senior_invites_sent < $senior_max_invites){
                        $class = CLASS_SENIOR;
                    }elseif($junior_invites_sent < $jr_max_invits){
                        // Choose a rising jr
                        $class = CLASS_JUNIOR;
                    }elseif($soph_invites_sent < $soph_max_invites){
                        // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
                        $class = CLASS_SOPHOMORE;
                    }else{
                        // If this ever happens, it means we reached our invite caps for all calsses before all the available lottery rooms were filled
                        $output[] = "All invite caps (by class) reached, quitting.";
                        HMS_Lottery::lottery_complete('SUCCESS', $output); 
                    }
                }

                $winning_row = HMS_Lottery::choose_winner($gender, $class, $term);
            }

            $output[] = "Inviting $winning_username";

            # Update the winning student's invite
            $entry = HMS_Lottery_Entry::get_entry($winning_username, $term);
            $entry->invite_expires_on = $expire_time;
            $result = $entry->save();

            if(!$result || PHPWS_Error::logIfError($result)){
                $output[] = 'Error while trying to select a winning student.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            # Update the counts of male/female invites available
            if(!$co_ed_only){
                if($winning_student['gender'] == MALE){
                    $male_invites_avail--;
                }else if($winning_student['gender'] == FEMALE){
                    $female_invites_avail--;
                }
            }

            #TODO: need to increment the number of invites sent by class

            # Send them an invite
            HMS_Email::send_lottery_invite($winning_username . '@appstate.edu', HMS_SOAP::get_name($winning_student), $expire_time, $year);

            # Log that the invite was sent
            HMS_Activity_Log::log_activity($winning_username, ACTIVITY_LOTTERY_INVITED, HMS_ADMIN_USER, 'Expires: ' . HMS_Util::get_long_date_time($expire_time));
        }

        HMS_Lottery::lottery_complete('SUCCESS', $output);
    }

    /*
     * Chooses a winner and returns that stuent's row from the hms_lottery_entry table
     */
    function choose_winner($gender, $class, $term)
    {
        $db = new PHPWS_DB('hms_lottery_entry');

        $winning_student = NULL;

        if($gender == COED){
            $db->addWhere('gender', MALE, '=', 'OR', 'gender');
            $db->addWhere('gender', FEMALE, '=', 'OR', 'gender');
        }else if($gender == MALE){
            $db->addWhere('gender', MALE, '=');
        }else if($gender == FEAMLE){
            $db->addWhere('gender', FEMALE, '=', 'OR', 'gender');
        }

        # Make sure we don't select anyone who has disability flags set
        $db->addWhere('physical_disability', 0);
        $db->addWhere('psych_disability', 0);
        $db->addWhere('medical_need', 0);
        $db->addWhere('gender_need', 0);

        if($class = CLASS_SOPHOMORE){
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $db->addWhere('application_term', ($term_year - 1) . '20', '=', 'OR', 'appterm');
            $db->addWhere('application_term', ($term_year - 1) . '30', '=', 'OR', 'appterm');
            $db->addWhere('application_term', ($term_year - 1) . '40', '=', 'OR', 'appterm');
            $db->addWhere('application_term', $term_year . '10', '=', 'OR', 'appterm');
        }else if($class == CLASS_JUNIOR){
            // Choose a rising jr
            $db->addWhere('application_term', ($term_year - 2) . '20', '=', 'OR', 'appterm');
            $db->addWhere('application_term', ($term_year - 2) . '30', '=', 'OR', 'appterm');
            $db->addWhere('application_term', ($term_year - 2) . '40', '=', 'OR', 'appterm');
            $db->addWhere('application_term', ($term_year - 1) . '10', '=', 'OR', 'appterm');
        }else{
            // Choose a rising senior or beyond
            $db->addWhere('application_term', ($term_year - 2) . '10', '<=');
        }

        # Only select students who either haven't been invited, or their invite has expired
        $db->addWhere('invite_expires_on', $now, '<', 'OR', 'expiration_group');
        $db->addWhere('invite_expires_on', NULL, 'IS NULL', 'OR', 'expiration_group');

        $db->addWhere('term', $term);

        $result = $db->select();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            $output[] = 'Error while trying to select a winning student.';
            HMS_Lottery::lottery_complete('FAILED', $output); 
        }

        # If there aren't any students which fit the parameters specified, return NULL
        if(sizeof($result) < 1){
            return NULL;
        }

        # Randomly pick a student from result
        $winning_student = $result[mt_rand(0, sizeof($result)-1)];

        return $winning_student;
    }

    /**
     * Returns the number of lottery entries currently outstanding (i.e. non-winners)
     */
    function count_remaining_entries($term)
    {
        $now = mktime();

        $sql = "SELECT count(*) FROM hms_lottery_entry WHERE term = $term AND (invite_expires_on IS NULL OR invite_expires_on < $now)
                AND physical_disability = 0
                AND psych_disability = 0
                AND medical_need = 0
                AND gender_need = 0";

        $num_remaining_entries = PHPWS_DB::getOne($sql);

        if(PEAR::isError($num_remaining_entries)){
            PHPWS_Error::log($num_remaining_entries);
            return FALSE;
        }

        return $num_remaining_entries;
    }

    function count_outstanding_invites($term, $gender = NULL)
    {
        $now = mktime();
        $query = "select count(*) FROM hms_lottery_entry
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_lottery_entry.asu_username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_entry.invite_expires_on > $now
                AND hms_lottery_entry.term = $term";
        if(isset($gender)){
            $query .= ' AND hms_lottery_entry.gender = ' . $gender;
        }

        $result = PHPWS_DB::getOne($query);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return false;
        }else{
            return $result;
        }
    }

    /*
     * Returns the number of outstanding *roommate* invites
     */
    function count_outstanding_roommate_invites($term)
    {
        $now = mktime();
        $query = "select count(*) FROM hms_lottery_reservation
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_lottery_reservation.asu_username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_reservation.expires_on > $now
                AND hms_lottery_reservation.term = $term";

        $result = PHPWS_DB::getOne($query);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return false;
        }else{
            return $result;
        }
    }

    /*
     * Returns the number of invites sent (confirmed or outstanding) for the given class
     */
    function count_invites_by_class($term, $class)
    {
        $now = mktime();
        $term_year = HMS_Term::get_term_year($term);

        $query = "SELECT count(*) FROM hms_lottery_entry
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_lottery_entry.asu_username = foo.asu_username
                WHERE ((foo.asu_username IS NULL AND hms_lottery_entry.invite_expires_on > $now) OR (foo.asu_username IS NOT NULL))
                AND hms_lottery_entry.term = $term ";

        if($class == CLASS_SOPHOMORE){
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        }else if($class == CLASS_JUNIOR){
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        }else{
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }
        
        $result = PHPWS_DB::getOne($query);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return FALSE;
        }else{
            return $result;
        }
    }

    function send_winning_reminder_emails($term)
    {
        # Get a list of lottery winners who have not chosen a room yet, send them reminder emails
        $query = "select hms_lottery_entry.asu_username, hms_lottery_entry.invite_expires_on FROM hms_lottery_entry
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_lottery_entry.asu_username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_entry.invite_expires_on > " . mktime();

        $result = PHPWS_DB::getAll($query);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            test($result,1);
        }

        $year = HMS_Term::term_to_text($term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($term),TRUE);

        foreach($result as $row){
            HMS_Email::send_lottery_invite_reminder($row['asu_username'], HMS_SOAP::get_name($row['asu_username']), $row['invite_expires_on'], $year);
            HMS_Activity_Log::log_activity($row['asu_username'], ACTIVITY_LOTTERY_REMINDED, HMS_ADMIN_USER);
        }
    }

    function send_roommate_reminder_emails($term)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        # Get a list of outstanding roommate requests, send them reminder emails
        $query = "select hms_lottery_reservation.* FROM hms_lottery_reservation
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_lottery_reservation.asu_username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_reservation.expires_on > " . mktime();

        $result = PHPWS_DB::getAll($query);
        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            test($result,1);
        }

        $year = HMS_Term::term_to_text($term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($term),TRUE);

        foreach($result as $row){
            $bed = new HMS_Bed($row['bed_id']);
            $hall_room = $bed->where_am_i();
            HMS_Email::send_lottery_roommate_reminder($row['asu_username'], HMS_SOAP::get_name($row['asu_username']), $row['expires_on'], HMS_SOAP::get_full_name($row['requestor']), $hall_room, $year);
            HMS_Activity_Log::log_activity($row['asu_username'], ACTIVITY_LOTTERY_ROOMMATE_REMINDED, HMS_ADMIN_USER);
        }
    }

    function lottery_complete($status, $log, $unschedule = FALSE)
    {
        echo "Lottery complete, status: $status<br />\n";

        $email = "";

        # Output the logging info, transform for email
        foreach($log as $line){
            echo $line . "<br />\n";
            $email .= $line . "\n";
        }

        # TODO: unschedule from pulse here, if true

        HMS_Email::send_lottery_status_report($status, $email);
        exit;
    }

    function get_lottery_roommate_invite($username, $term)
    {
        $db = new PHPWS_DB('hms_lottery_reservation');

        $db->addWhere('asu_username', $username);
        $db->addWhere('term', $term);
        $db->addWhere('expires_on', mktime(), '>'); // make sure the request hasn't expired
        
        $result = $db->select('row');

        if(!$result || PHPWS_Error::logIfError($result)){
            return FALSE;
        }

        return $result;
    }

    function confirm_roommate_request($username,$meal_plan)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $term = PHPWS_Settings::get('hms', 'lottery_term');

        # Get the roommate invite
        $invite = HMS_Lottery::get_lottery_roommate_invite($_SESSION['asu_username'], $term);

        # If the invite wasn't found, show an error
        if($invite === FALSE){
            return E_LOTTERY_ROOMMATE_INVITE_NOT_FOUND;
        }


        # Check that the reserved bed is still empty
        $bed = &new HMS_Bed($invite['bed_id']);
        if(!$bed->has_vacancy()){
            return E_ASSIGN_BED_NOT_EMPTY;
        }

        # Make sure the student isn't assigned anywhere else
        if(HMS_Assignment::check_for_assignment($username, $term)){
            return E_ASSIGN_ALREADY_ASSIGNED;
        }

        # Actually make the assignment
        $assign_result = HMS_Assignment::assign_student($username, NULL, $invite['bed_id'], $meal_plan, 'Confirmed roommate invite', TRUE);
        if($assign_result != E_SUCCESS){
            HMS_Email::send_roommate_confirmation($username, null, $invite['requestor']);
            return $assign_result;
        }

        # return successfully
        return E_SUCCESS;
    }

    function main()
    {
        switch($_REQUEST['op']){
            case 'show_lottery_settings':
                return HMS_Lottery::show_lottery_settings();
                break;
            case 'submit_lottery_settings':
                return HMS_Lottery::submit_lottery_settings();
                break;
            case 'view_lottery_needs':
                PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
                return HMS_Lottery_Entry::get_special_needs_interface();
                break;
            case 'show_admin_entry':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                return Lottery_UI::show_admin_entry();
                break;
            case 'submit_admin_entry':
                PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
                PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
                return Lottery_UI::show_admin_entry(HMS_Lottery_Entry::parse_entry($_REQUEST));
                break;
            default:
                break;
        }
    }


    function save_lottery_settings($lottery_term, $type, $lottery_per_soph, $lottery_per_jr, $lottery_per_senior, $max_soph, $max_jr, $max_senior)
    {

        PHPWS_Settings::set('hms', 'lottery_term',       $lottery_term);
        PHPWS_Settings::set('hms', 'lottery_type',       $type);
        PHPWS_Settings::set('hms', 'lottery_per_soph',   $lottery_per_soph);
        PHPWS_Settings::set('hms', 'lottery_per_jr',     $lottery_per_jr);
        PHPWS_Settings::set('hms', 'lottery_per_senior', $lottery_per_senior);

        PHPWS_Settings::set('hms', 'lottery_max_soph',   $max_soph);
        PHPWS_Settings::set('hms', 'lottery_max_jr',     $max_jr);
        PHPWS_Settings::set('hms', 'lottery_max_senior', $max_senior);

        PHPWS_Settings::save('hms');
    }

    /***********************
     * Lottery Settings UI *
     ***********************/
    function show_lottery_settings($success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $tpl = array();

        $form = new PHPWS_Form();

        $form->addDropBox('lottery_term', HMS_Term::get_available_terms_list());
        $form->setMatch('lottery_term', PHPWS_Settings::get('hms', 'lottery_term'));

        $form->addRadio('phase_radio', array('single_phase', 'multi_phase'));
        $form->setMatch('phase_radio', PHPWS_Settings::get('hms', 'lottery_type'));
        $form->setLabel('phase_radio', array('Single phase', 'Multi-phase'));
        $form->setExtra('phase_radio', 'class="lotterystate"');

        # Percent invites per class for single phase lottery
        $form->addText('lottery_per_soph', PHPWS_Settings::get('hms', 'lottery_per_soph'));
        $form->setSize('lottery_per_soph', 2, 3);
        $form->setExtra('lottery_per_soph', 'class="single_phase"');

        $form->addText('lottery_per_jr', PHPWS_Settings::get('hms', 'lottery_per_jr'));
        $form->setSize('lottery_per_jr', 2, 3);
        $form->setExtra('lottery_per_jr', 'class="single_phase"');

        $form->addText('lottery_per_senior', PHPWS_Settings::get('hms', 'lottery_per_senior'));
        $form->setSize('lottery_per_senior', 2, 3);
        $form->setExtra('lottery_per_senior', 'class="single_phase"');

        # Absolute max invites to send per class for multi-phase lottery
        $form->addText('lottery_max_soph', PHPWS_Settings::get('hms', 'lottery_max_soph'));
        $form->setSize('lottery_max_soph', 2, 4);
        $form->setExtra('lottery_max_soph', 'class="multi_phase"');

        $form->addText('lottery_max_jr', PHPWS_Settings::get('hms', 'lottery_max_jr'));
        $form->setSize('lottery_max_jr', 2, 4);
        $form->setExtra('lottery_max_jr', 'class="multi_phase"');

        $form->addText('lottery_max_senior', PHPWS_Settings::get('hms', 'lottery_max_senior'));
        $form->setSize('lottery_max_senior', 2, 4);
        $form->setExtra('lottery_max_senior', 'class="multi_phase"');

        # Set the initial enabled/disabled state
        $type = PHPWS_Settings::get('hms', 'lottery_type');
        if(isset($type) && $type == 'single_phase'){
            $form->setDisabled('lottery_max_soph');
            $form->setDisabled('lottery_max_jr');
            $form->setDisabled('lottery_max_senior');
        }else{
            $form->setDisabled('lottery_per_soph');
            $form->setDisabled('lottery_per_jr');
            $form->setDisabled('lottery_per_senior');
        }

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'lottery');
        $form->addHidden('op', 'submit_lottery_settings');

        $form->addSubmit('submit');

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/lottery_settings.tpl');
    }

    function submit_lottery_settings()
    {
        $per_soph   = isset($_REQUEST['lottery_per_soph'])?$_REQUEST['lottery_per_soph']:0;
        $per_jr     = isset($_REQUEST['lottery_per_jr'])?$_REQUEST['lottery_per_jr']:0;
        $per_senior = isset($_REQUEST['lottery_per_senior'])?$_REQUEST['lottery_per_senior']:0;

        $max_soph   = isset($_REQUEST['lottery_max_soph'])?$_REQUEST['lottery_max_soph']:0;
        $max_jr     = isset($_REQUEST['lottery_max_jr'])?$_REQUEST['lottery_max_jr']:0;
        $max_senior = isset($_REQUEST['lottery_max_senior'])?$_REQUEST['lottery_max_senior']:0;

        # if using single phase lottery, Make sure the percents add up to exactly 100
        if($_REQUEST['phase_radio'] == 'single_phase' && ($per_soph + $per_jr + $per_senior) != 100){
            return HMS_Lottery::show_lottery_settings(NULL, 'Error: Percents must add up to 100');
        }

        # Save the settings
        HMS_Lottery::save_lottery_settings($_REQUEST['lottery_term'],$_REQUEST['phase_radio'], $per_soph, $per_jr, $per_senior, $max_soph, $max_jr, $max_senior);

        return HMS_Lottery::show_lottery_settings('Lottery settings updated.');
    }
}

?>
