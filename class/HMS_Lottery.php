<?php

define('MAX_INVITES_PER_BATCH', 150);
define('INVITE_TTL_HRS', 48);

class HMS_Lottery {


    public static function runLottery()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        /******************
         * Initialization *
         ******************/
        HMS_Activity_Log::log_activity('hms', ACTIVITY_LOTTERY_EXECUTED, 'hms');

        # One-time date/time calculations, setup for later on
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $term_year = Term::getTermYear($term);
        $now = mktime();
        $expire_time = $now + (INVITE_TTL_HRS * 3600);
        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));

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

        try{
            # Count the number of invites sent (outstanding or confirmed) per class
            $senior_invites_sent = HMS_Lottery::count_invites_by_class($term, CLASS_SENIOR);
            $junior_invites_sent = HMS_Lottery::count_invites_by_class($term, CLASS_JUNIOR);
            $soph_invites_sent   = HMS_Lottery::count_invites_by_class($term, CLASS_SOPHOMORE);
        }catch(Exception $e){
            $output[] = 'Error counting previously sent invites! Exception: ' . $e->getMessage();
            HMS_Lottery::lottery_complete('FAILED', $output);
        }

        $output[] = "$senior_invites_sent senior invites previously sent";
        $output[] = "$junior_invites_sent junior invites previously sent";
        $output[] = "$soph_invites_sent sophomore invites previously sent";

        # Count the number of outstanding female invites
        try{
            $female_invites_outstanding = HMS_Lottery::count_outstanding_invites($term, FEMALE);
        }catch(Exception $e){
            $output[] = 'error counting outstanding female invites Exception: ' . $e->getMessage();
            HMS_Lottery::lottery_complete("FAILED", $output);
        }

        $output[] = "$female_invites_outstanding female invites outstanding";

        # Count the number of outstanding male invites
        try{
            $male_invites_outstanding = HMS_Lottery::count_outstanding_invites($term, MALE);
        }catch(Exception $e){
            $output[] = 'error counting outstanding male invites Exception: ' . $e->getMessage();
            HMS_Lottery::lottery_complete("FAILED", $output);
        }

        $output[] = "$male_invites_outstanding male invites oustanding";

        # Get a total number of invites outstanding
        $outstanding_invite_count = $male_invites_outstanding + $female_invites_outstanding;

        $output[] = "$outstanding_invite_count total invites outstanding";

        # Get the total number of outstanding roommate invites
        try{
            $outstanding_roommate_invites = HMS_Lottery::count_outstanding_roommate_invites($term);
        }catch(Exception $e){
            $output[] = 'error counting outstanding roommate invites Exception: ' . $e->getMessage();
            HMS_Lottery::lottery_complete("FAILED", $output);
        }

        $output[] = "$outstanding_roommate_invites outstanding roommate invites";


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
            try{
                $full_rooms = $hall->count_lottery_full_rooms();
            }catch(Exception $e){
                $output[] = 'Error while counting full rooms. Exception: ' . $e->getMessage();
                HMS_Lottery::lottery_complete('FAILED', $output);
            }

            $output[] = "$full_rooms full lottery rooms";

            # Get the number of used rooms in this hall
            try{
                $used_rooms = $hall->count_lottery_used_rooms();
            }catch(Exception $e){
                $output[] = 'Error while counting full rooms. Check the error logs. Exception: ' . $e->getMessage();
                HMS_Lottery::lottery_complete('FAILED', $output);
            }

            $output[] = "$used_rooms lottery rooms used";

            # Calculate the remaining number of rooms allowed for the lottery in this hall
            $remaining_rooms_this_hall = $lottery_rooms - $full_rooms;

            if($remaining_rooms_this_hall < 0){
                $remaining_rooms_this_hall = 0;
            }

            $output[] = "$remaining_rooms_this_hall remaining rooms available for lottery";

            if($remaining_rooms_this_hall <= 0){
                continue;
            }

            # Count the number of non-full male/female rooms in this hall
            try{
                $female_rooms_this_hall = $hall->count_avail_lottery_rooms(FEMALE);
            }catch(Exception $e){
                $output[] = 'Error counting non-full female rooms. Exception: ' . $e->getMessage();
                HMS_Lottery::lottery_complete('FAILED', $output);
            }

            try{
                $male_rooms_this_hall = $hall->count_avail_lottery_rooms(MALE);
            }catch(Exception $e){
                $output[] = 'Error counting non-full male rooms. Exception: ' . $e->getMessage();;
                HMS_Lottery::lottery_complete('FAILED', $output);
            }

            # Count the number of co-ed rooms
            try{
                $coed_rooms_this_hall = $hall->count_avail_lottery_rooms(COED);
            }catch(Exception $e){
                $output[] = 'Error counting non-full co-ed rooms. Exception: ' . $e->getMessage();
                HMS_Lottery::lottery_complete('FAILED', $output);
            }

            $output[] = "$coed_rooms_this_hall remaining coed rooms";
            $output[] = "$male_rooms_this_hall remaining male rooms";
            $output[] = "$female_rooms_this_hall remaining female rooms";

            # Add that number to total number of lottery invites to send
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

        /*
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
         $female_invites_avail   = $remaining_female_rooms - $female_invites_outstanding;

         $output[] = "No co-ed rooms remaining, can send $invites_to_send invites";
         $output[] = "$male_invites_avail male invites available";
         $output[] = "$female_invites_avail female invites available";
         }
         */

        # Calculate the maximum number of male/female/coed invites we can send
        $male_invites_avail     = $remaining_male_rooms - $male_invites_outstanding;
        $female_invites_avail   = $remaining_female_rooms - $female_invites_outstanding;
        $coed_invites_avail     = $remaining_coed_rooms - $outstanding_invite_count;

        if($male_invites_avail < $female_invites_avail){
            $invites_to_send = $male_invites_avail;
        }else{
            $invites_to_send = $female_invites_avail;
        }

        if($coed_invites_avail < 0){
            $coed_invites_avail = 0;
        }

        $invites_to_send += $coed_invites_avail;

        # Make sure we're not sending out more invites than we have rooms
        if($invites_to_send > ($remaining_rooms - $outstanding_invite_count)){
            $invites_to_send = $remaining_rooms - $outstanding_invite_count;
        }

        $output[] = "$male_invites_avail male invites available";
        $output[] = "$female_invites_avail female invites available";
        $output[] = "$coed_invites_avail  co-ed invites";
        $output[] = "Could send $invites_to_send total invites";

        # Make sure we aren't sending more than our max at once
        if($invites_to_send > MAX_INVITES_PER_BATCH){
            $invites_to_send = MAX_INVITES_PER_BATCH;
            $output[] = "Batch size limited to $invites_to_send";
        }

        $output[] = "Sending up to $invites_to_send new invites";

        if($invites_to_send <= 0){
            $output[] = "Cannout send any new entries, quitting.";
            HMS_Lottery::lottery_complete('SUCCESS', $output);
        }

        # Count the number of remaining entries
        try{
            $remaining_entries = HMS_Lottery::count_remaining_entries($term);
        }catch(Exception $e){
            $output[] = 'Error counting outstanding lottery entires, quitting. Exception: ' . $e->getMessage();
            HMS_Lottery::lottery_complete('FAILED', $output);
        }

        $output[] = "$remaining_entries lottery entries remaining";

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
            if($remaining_entries <= 0){
                $output[] = 'No entries remaining, quitting!';
                HMS_Lottery::lottery_complete('SUCCESS', $output, TRUE);
            }

            $output[] = "$remaining_entries entries remaining";

            $winning_row = NULL;

            # Check to see if we have a 'magic winner first
            $winning_row = HMS_Lottery::check_magic_winner($term);

            $j = 0;
            // Loop until we have a winner, stop if we do this 200 times without a winner
            while(is_null($winning_row) && $j < 200){

                # Decide which gender we need to invite
                if($coed_invites_avail > 0){
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
                    if($senior_invites_sent < $senior_max_invites && HMS_Lottery::count_remaining_entries_by_class($term, CLASS_SENIOR) > 0){
                        $class = CLASS_SENIOR;
                    }elseif($junior_invites_sent < $jr_max_invites && HMS_Lottery::count_remaining_entries_by_class($term, CLASS_JUNIOR) > 0){
                        // Choose a rising jr
                        $class = CLASS_JUNIOR;
                    }elseif($soph_invites_sent < $soph_max_invites && HMS_Lottery::count_remaining_entries_by_class($term, CLASS_SOPHOMORE) > 0){
                        // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
                        $class = CLASS_SOPHOMORE;
                    }else{
                        // If this ever happens, it means we reached our invite caps for all calsses before all the available lottery rooms were filled
                        $output[] = "All invite caps (by class) reached or out of students to invite, quitting.";
                        HMS_Lottery::lottery_complete('SUCCESS', $output);
                    }
                }

                # If we're in the first 100 iterations, don't allow a previous winner. After the first 100 iterations, allow previous winners.
                if($j < 100){
                    $winning_row = HMS_Lottery::choose_winner($gender, $class, $term, FALSE);
                }else{
                    $winning_row = HMS_Lottery::choose_winner($gender, $class, $term, TRUE);
                }

                $j++;
            }

            if($j >= 200){
                $output[] = "Couldn't find a winner. Stopping.";
                HMS_Lottery::lottery_complete('SUCCESS', $output);
            }

            $winning_username = $winning_row['username'];
            $output[] = "Inviting $winning_username";

            # Update the winning student's invite
            try{
            $entry = HousingApplication::getApplicationByUser($winning_username, $term);
            $entry->invite_expires_on = $expire_time;
            
            $result = $entry->save();
            }catch(Exception $e){
                $output[] = 'Error while trying to select a winning student. Exception: ' . $e->getMessage();
                HMS_Lottery::lottery_complete('FAILED', $output);
            }

            # Update the counts of male/female invites available
            if($winning_row['gender'] == MALE){
                $male_invites_avail--;
            }else if($winning_row['gender'] == FEMALE){
                $female_invites_avail--;
            }

            # Update the number of entries remaining
            $remaining_entries--;

            $actual_class = HMS_Lottery::application_term_to_class($term, $winning_row['application_term']);

            # increment the number of invites sent by class
            if($actual_class == CLASS_SENIOR){
                $senior_invites_sent++;
            }else if($actual_class == CLASS_JUNIOR){
                $junior_invites_sent++;
            }else if($actual_class == CLASS_SOPHOMORE){
                $soph_invites_sent++;
            }
            
            $student = StudentFactory::getStudentByUsername($winning_username, $term);

            # Send them an invite
            HMS_Email::send_lottery_invite($winning_username, $student->getName(), $expire_time, $year);

            # Log that the invite was sent
            HMS_Activity_Log::log_activity($winning_username, ACTIVITY_LOTTERY_INVITED, 'hms', 'Expires: ' . HMS_Util::get_long_date_time($expire_time));
        }

        HMS_Lottery::lottery_complete('SUCCESS', $output);
    }

    /*
     * Chooses a winner and returns that stuent's row from the hms_lottery_entry table
     */
    public function choose_winner($gender, $class, $term, $allow_previous_winners)
    {
        $winning_student = NULL;
        $now = mktime();

        $query = "SELECT * FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL ";

        if($allow_previous_winners){
            $query .= "AND (hms_lottery_application.invite_expires_on < $now OR hms_lottery_application.invite_expires_on IS NULL) ";
        }else{
            $query .= "AND hms_lottery_application.invite_expires_on IS NULL ";
        }

        $query .= "AND hms_new_application.term = $term
                   AND hms_new_application.physical_disability = 0
                   AND hms_new_application.psych_disability = 0
                   AND hms_new_application.medical_need = 0
                   AND hms_new_application.gender_need = 0
                   AND hms_lottery_application.special_interest IS NULL ";

        if($gender == MALE){
            $query .= "AND hms_new_application.gender = 1 ";
        }else if($gender == FEMALE){
            $query .= "AND hms_new_application.gender = 0 ";
        }

        $term_year = Term::getTermYear($term);
        if($class == CLASS_SOPHOMORE){
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20 ';
            $query .=   'OR application_term = ' . ($term_year - 1) . '30 ';
            $query .=   'OR application_term = ' . ($term_year - 1) . '40 ';
            $query .=   'OR application_term = ' . $term_year . '10';
            $query .= ') ';
        }else if($class == CLASS_JUNIOR){
            // Choose a rising jr
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20 ';
            $query .=   'OR application_term = ' . ($term_year - 2) . '30 ';
            $query .=   'OR application_term = ' . ($term_year - 2) . '40 ';
            $query .=   'OR application_term = ' . ($term_year - 1) . '10';
            $query .= ') ';
        }else{
            // Choose a rising senior or beyond
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10 ';
        }

        $result = PHPWS_DB::getAll($query);

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
     * Looks for an entry with the 'magic_winner' flag set and returns it, otherwise it returns null
     */
    public function check_magic_winner($term){

        $now = mktime();

        $query = "SELECT * FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL AND (hms_lottery_application.invite_expires_on < $now OR hms_lottery_application.invite_expires_on IS NULL)
                    AND hms_new_application.term = $term
                    AND hms_lottery_application.magic_winner = 1";

        $result = PHPWS_DB::getRow($query);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return NULL;
        }

        if(!isset($result) || empty($result)){
            return NULL;
        }else{
            return $result;
        }
    }

    /**
     * Returns the number of lottery entries currently outstanding (i.e. non-winners)
     */
    public function count_remaining_entries($term)
    {
        $now = mktime();
        
        $sql = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL AND (hms_lottery_application.invite_expires_on < $now OR hms_lottery_application.invite_expires_on IS NULL)
                AND hms_new_application.term = $term
                AND physical_disability = 0
                AND psych_disability = 0
                AND medical_need = 0
                AND gender_need = 0
                AND special_interest IS NULL";
        
        $num_remaining_entries = PHPWS_DB::getOne($sql);

        if(PEAR::isError($num_remaining_entries)){
            PHPWS_Error::log($num_remaining_entries);
            return FALSE;
        }

        return $num_remaining_entries;
    }

    public function count_outstanding_invites($term, $gender = NULL)
    {
        $now = mktime();
        $query = "select count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_application.invite_expires_on > $now
                AND hms_new_application.term = $term";
        if(isset($gender)){
            $query .= ' AND hms_new_application.gender = ' . $gender;
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
    public function count_outstanding_roommate_invites($term)
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
    public function count_invites_by_class($term, $class)
    {
        $now = mktime();
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_new_application.username = foo.asu_username
                WHERE ((foo.asu_username IS NULL AND hms_lottery_application.invite_expires_on > $now) OR (foo.asu_username IS NOT NULL AND hms_lottery_application.invite_expires_on IS NOT NULL))
                AND hms_new_application.term = $term ";

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

    public function count_remaining_entries_by_class($term, $class)
    {
        $now = mktime();
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL AND (hms_lottery_application.invite_expires_on < $now OR hms_lottery_application.invite_expires_on IS NULL)
                    AND hms_new_application.term = $term
                    AND hms_new_application.physical_disability = 0
                    AND hms_new_application.psych_disability = 0
                    AND hms_new_application.medical_need = 0
                    AND hms_new_application.gender_need = 0
                    AND special_interest IS NULL ";

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

    function count_outstanding_invites_by_class($term, $class){
        $now = mktime();
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL
                    AND hms_lottery_application.invite_expires_on > $now
                    AND hms_new_application.term = $term ";

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

    function count_applications_by_class($term, $class){
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from from hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    WHERE term = $term
                    AND special_interest IS NULL
                    AND gender_need = 0
                    AND medical_need = 0
                    AND physical_disability = 0
                    AND psych_disability = 0 ";

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

    function count_assignments_by_class($term, $class){
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from hms_assignment
                    JOIN hms_new_application ON hms_assignment.asu_username = hms_new_application.username
                    JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id 
                    WHERE hms_assignment.term = $term 
                    AND hms_assignment.lottery = 1
                    AND hms_new_application.term = $term ";

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

    public function send_winning_reminder_emails($term)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModclass('hms', 'StudentFactory.php');

        # Get a list of lottery winners who have not chosen a room yet, send them reminder emails
        $query = "select hms_new_application.username, hms_lottery_application.invite_expires_on FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_application.invite_expires_on > " . mktime();

        $result = PHPWS_DB::getAll($query);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            test($result,1);
        }

        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));

        foreach($result as $row){
            $student = StudentFactory::getStudentByUsername($row['asu_username'], $term);
            HMS_Email::send_lottery_invite_reminder($row['asu_username'], $student->getName(), $row['invite_expires_on'], $year);
            HMS_Activity_Log::log_activity($row['asu_username'], ACTIVITY_LOTTERY_REMINDED, 'hms');
        }
    }

    public function send_roommate_reminder_emails($term)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModclass('hms', 'StudentFactory.php');

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

        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));

        foreach($result as $row){
            $student = StudentFactory::getStudentByUsername($row['asu_username'], $term);
            $requestor = StudentFactory::getStudentByUsername($row['requestor'], $term);
            
            $bed = new HMS_Bed($row['bed_id']);
            $hall_room = $bed->where_am_i();
            HMS_Email::send_lottery_roommate_reminder($row['asu_username'], $student->getName(), $row['expires_on'], $requestor->getName(), $hall_room, $year);
            HMS_Activity_Log::log_activity($row['asu_username'], ACTIVITY_LOTTERY_ROOMMATE_REMINDED, 'hms');
        }
    }

    public function lottery_complete($status, $log, $unschedule = FALSE)
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


    /**
     * Retuns an array of lottery roommate invites
     */
    public function get_lottery_roommate_invites($username, $term)
    {
        $db = new PHPWS_DB('hms_lottery_reservation');

        $db->addWhere('asu_username', $username);
        $db->addWhere('term', $term);
        $db->addWhere('expires_on', mktime(), '>'); // make sure the request hasn't expired

        $result = $db->select();

        if(!$result || PHPWS_Error::logIfError($result)){
            return FALSE;
        }

        return $result;
    }

    public function get_lottery_roommate_invite_by_id($id)
    {
        $db = new PHPWS_DB('hms_lottery_reservation');

        $db->addWhere('expires_on', mktime(), '>'); // make sure the request hasn't expired
        $db->addWhere('id', $id);

        $result = $db->select('row');

        if(!$result || PHPWS_Error::logIfError($result)){
            return FALSE;
        }

        return $result;
    }

    public function confirm_roommate_request($username, $requestId, $meal_plan)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $term = PHPWS_Settings::get('hms', 'lottery_term');

        # Get the roommate invite
        $invite = HMS_Lottery::get_lottery_roommate_invite_by_id($requestId);

        # If the invite wasn't found, show an error
        if($invite === FALSE){
            return E_LOTTERY_ROOMMATE_INVITE_NOT_FOUND;
        }

        # Check that the reserved bed is still empty
        $bed = new HMS_Bed($invite['bed_id']);
        if(!$bed->has_vacancy()){
            return E_ASSIGN_BED_NOT_EMPTY;
        }

        # Make sure the student isn't assigned anywhere else
        if(HMS_Assignment::checkForAssignment($username, $term)){
            return E_ASSIGN_ALREADY_ASSIGNED;
        }
        
        $student = StudentFactory::getStudentByUsername($username, $term);
        $requestor = StudentFactory::getStudentByUsername($invite['requestor'], $term);

        # Actually make the assignment
        $assign_result = HMS_Assignment::assignStudent($student, $term, NULL, $invite['bed_id'], $meal_plan, 'Confirmed roommate invite', TRUE);

        # return successfully
        HMS_Email::send_roommate_confirmation($student, $requestor);
        return E_SUCCESS;
    }

    /*
     * Returns TRUE if the student is assigned in the current term
     * or if the student has an eligibility waiver.
     */
    public function determineEligibility($username){
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Eligibility_Waiver.php');

        # First, check for an assignment in the current term
        if(HMS_Assignment::checkForAssignment($username, Term::getCurrentTerm())){
            return TRUE;
            # If that didn't work, check for a waiver in the lottery term
        }elseif(HMS_Eligibility_Waiver::checkForWaiver($username, PHPWS_Settings::get('hms', 'lottery_term'))){
            return TRUE;
            # If that didn't work either, then the student is not elibible, so return false
        }else{
            return FALSE;
        }


    }

    public function main()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
        PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');

        switch($_REQUEST['op']){
            case 'show_lottery_settings':
                return HMS_Lottery::show_lottery_settings();
            case 'submit_lottery_settings':
                return HMS_Lottery::submit_lottery_settings();
            case 'view_lottery_needs':
                return HMS_Lottery_Entry::get_special_needs_interface();
            case 'show_admin_entry':
                return Lottery_UI::show_admin_entry();
            case 'submit_admin_entry':
                //return Lottery_UI::show_admin_entry(HMS_Lottery_Entry::parse_entry($_REQUEST));
                return HMS_Lottery_Entry::submit_admin_entry();
            case 'show_eligibility_waiver':
                return Lottery_UI::show_eligibility_waiver();
            case 'create_waiver':
                return Lottery_UI::create_eligibility_waiver();
            case 'magic':
                return Lottery_UI::show_magic_interface();
            case 'apply_magic':
                return HMS_Lottery::apply_magic($_REQUEST['asu_username'], $_REQUEST['magic']);
            case 'show_special_interest_approval':
                return Lottery_UI::show_special_interest_approval();
            case 'remove_special_interest':
                return HMS_Lottery::remove_special_interest();
            case 'show_waiting_list':
                return Lottery_UI::show_waiting_list();
            case 'waiting_list_remove':
                return HMS_Lottery_Entry::wait_list_remove();
            default:
                break;
        }
    }

    public function apply_magic($username, $enabled){
        PHPWS_Core::initModClass('hms', 'HMS_Lottery_Entry.php');
        PHPWS_Core::initModClass('hms', 'UI/Lottery_UI.php');
        PHPWS_Core::initModClass('hms', 'SOAP.php');
        
        $soap = SOAP::getInstance();

        if(preg_match("/^[0-9]{9}/", $username)){
            $username = $soap->getUsername($username);

            if($username == NULL){
                return Lottery_UI::show_magic_interface("Error updating user: $username, unknown banner ID");
            }
        }

        $entry = new HMS_Lottery_Entry($username, PHPWS_Settings::get('hms', 'lottery_term'));
        if(!is_null($enabled)){
            $entry->magic_winner = 1;
        } else {
            $entry->magic_winner = 0;
        }

        if($entry->save()){
            return Lottery_UI::show_magic_interface("User: $username successfully updated.");
        }

        return Lottery_UI::show_magic_interface("Error updating user: $username");
    }

    public function run_monte_carlo($term, $num_iterations, $soph_invites, $jr_invites, $sr_invites)
    {
        # Declare an array for the final output
        $output = array();

        $num_two_beds = 930;
        $num_four_beds = 492;

        # Query the database for the set of entry application terms, and the application terms of their roommates
        $db = new PHPWS_DB('hms_lottery_entry');
        $db->addColumn('application_term');
        $db->addColumn('roommate1_app_term');
        $db->addColumn('roommate2_app_term');
        $db->addColumn('roommate3_app_term');

        $db->addWhere('term', $term); // Only for the specified term

        $result = $db->select();

        # Transform the 2D array of application terms into a 2D array of classes (i.e. 'SOPH', 'JR', 'SR')
        $transformed_entries = HMS_Lottery::monte_carlo_translate_array($result, $term);

        # Separate the entries into individual lists of soph, jr, senior entires
        $soph_entries = $jr_entries = $sr_entries = array();

        foreach($transformed_entries as $entry){
            if($entry[0] == CLASS_SOPHOMORE){
                $soph_entries[] = $entry;
            }elseif($entry[0] == CLASS_JUNIOR){
                $jr_entries[] = $entry;
            }elseif($entry[0] == CLASS_SENIOR){
                $sr_entries[] = $entry;
            }
        }

        $soph_results   = array();
        $jr_results     = array();
        $sr_results     = array();

        # Run the simulation the requested number of times
        for($i = 0; $i < $num_iterations; $i++){
            $result = HMS_Lottery::monte_carlo_interation($soph_entries, $jr_entries, $sr_entries, $soph_invites, $jr_invites, $sr_invites, $num_two_beds, $num_four_beds);

            $soph_results[] = $result['soph'];
            $jr_results[]   = $result['jr'];
            $sr_results[]   = $result['sr'];
        }

        # Compute stats about the generated dataset
        $output['soph_min'] = min($soph_results);
        $output['soph_max'] = max($soph_results);
        $output['soph_avg'] = floor(HMS_Lottery::mean($soph_results));
        $output['soph_mode']= HMS_Lottery::mode($soph_results);

        $output['jr_min']   = min($jr_results);
        $output['jr_max']   = max($jr_results);
        $output['jr_avg']   = floor(HMS_Lottery::mean($jr_results));
        $output['jr_mode']  = HMS_Lottery::mode($jr_results);

        $output['sr_min']   = min($sr_results);
        $output['sr_max']   = max($sr_results);
        $output['sr_avg']   = floor(HMS_Lottery::mean($sr_results));
        $output['sr_mode']  = HMS_Lottery::mode($sr_results);

        return $output;
    }

    public function monte_carlo_interation($soph_entries, $jr_entries, $sr_entries, $soph_invites, $jr_invites, $sr_invites, $num_two_beds, $num_four_beds)
    {
        $actual_soph = $actual_jr = $actual_sr = 0;

        // Copy the value of these vars
        $two_beds   = $num_two_beds;
        $four_beds  = $num_four_beds;

        # Randomize the arrays
        shuffle($sr_entries);
        shuffle($jr_entries);
        shuffle($soph_entries);

        $result = HMS_Lottery::monte_carlo_choose_entries($sr_entries, $sr_invites, $two_beds, $four_beds);
        $actual_sr      += $result['sr'];
        $actual_jr      += $result['jr'];
        $actual_soph    += $result['soph'];

        $result = HMS_Lottery::monte_carlo_choose_entries($jr_entries, $jr_invites, $two_beds, $four_beds);
        $actual_sr      += $result['sr'];
        $actual_jr      += $result['jr'];
        $actual_soph    += $result['soph'];

        $result = HMS_Lottery::monte_carlo_choose_entries($soph_entries, $soph_invites, $two_beds, $four_beds);
        $actual_sr      += $result['sr'];
        $actual_jr      += $result['jr'];
        $actual_soph    += $result['soph'];

        return array('soph'=>$actual_soph, 'jr'=>$actual_jr, 'sr'=>$actual_sr);
    }

    public function monte_carlo_choose_entries($entries, $num_invites, &$two_beds, &$four_beds)
    {
        $actual_sr = $actual_jr = $actual_soph = 0;

        for($i = 0; $i < $num_invites && $i < count($entries); $i++){

            # Get the entry at the current index
            $entry = $entries[$i];

            # Reset these flags
            $use_two_beds   = FALSE;
            $use_foure_beds = FALSE;

            # Tally the number of each bed type used for this student
            if($four_beds > 0){
                $four_beds--;
            }else if($two_beds > 0){
                $two_beds--;
            }else{
                // We're out of beds, so quit before we actually count this student or his roommates
                break;
            }

            # Tally the student who entered
            if($entry[0] == CLASS_SENIOR){
                $actual_sr++;
            }elseif($entry[0] == CLASS_JUNIOR){
                $actual_jr++;
            }elseif($entry[0] == CLASS_SOPHOMORE){
                $actual_soph++;
            }

            # For each of the other roommates
            for($j = 1; $j < 4; $j++){

                if(is_null($entry[$j])){
                    continue;
                }

                // Tally the bed this student will occupy
                if($four_beds > 0){
                    $four_beds--;
                }else if($two_beds > 0){
                    $two_beds--;
                }else{
                    // We're out of beds, skip 'em and quit
                    break;
                }

                // Tally the student's class
                if($entry[$j] == CLASS_SENIOR){
                    $actual_sr++;
                }else if($entry[$j] == CLASS_JUNIOR){
                    $actual_jr++;
                }else if($entry[$j] == CLASS_SOPHOMORE){
                    $actual_soph++;
                }
            }
        }

        return array('soph'=>$actual_soph, 'jr'=>$actual_jr, 'sr'=>$actual_sr);
    }

    public function monte_carlo_translate_array($array, $term)
    {
        $result = array();

        foreach($array as $element){
            $entry = array();
            foreach($element as $entry_term){
                $entry[] = HMS_Lottery::application_term_to_class($term, $entry_term);
            }
            $result[] = $entry;
        }

        return $result;
    }

    // Translates an application term into a class (fr, soph, etc) based on the term given
    public function application_term_to_class($curr_term, $application_term)
    {

        // Break up the term and year
        $yr     = floor($application_term / 100);
        $sem    = $application_term - ($yr * 100);

        $curr_year = floor($curr_term / 100);
        $curr_sem  = $curr_term - ($curr_year * 100);

        if($curr_sem == 10){
            $curr_year -= 1;
            $curr_sem   = 40;
        }

        if(is_null($application_term) || !isset($application_term)){
            # If there's no application term, just return null
            return NULL;
        }else if($application_term >= $curr_term){
            // The application term is greater than the current term, then they're certainly a freshmen
            return CLASS_FRESHMEN;
        }else if(
        ($yr == $curr_year + 1 && $sem = 10) ||
        ($yr == $curr_year && $sem >= 20 && $sem <= 40)){
            // freshmen
            return CLASS_FRESHMEN;
        }else if(
        ($yr == $curr_year && $sem == 10) ||
        ($yr + 1 == $curr_year && $sem >= 20 && $sem <= 40)){
            // soph
            return CLASS_SOPHOMORE;
        }else if(
        ($yr + 1 == $curr_year && $sem == 10) ||
        ($yr + 2 == $curr_year && $sem >= 20 && $sem <= 40)){
            // jr
            return CLASS_JUNIOR;
        }else{
            // senior
            return CLASS_SENIOR;
        }
    }

    public function mean($array)
    {
        $sum = 0;

        foreach($array as $value){
            $sum += $value;
        }

        return $sum/count($array);
    }

    public function mode($array)
    {
        $counts = array();

        foreach($array as $value){
            if(isset($counts[$value])){
                $counts[$value] = $counts[$value] + 1;
            }else{
                $counts[$value] = 1;
            }
        }

        arsort($counts);
        $keys = array_keys($counts);
        return array($keys[0], $keys[1], $keys[2]);
    }

    public function get_special_interest_groups()
    {
        $special_interests['none']              = 'None';
        $special_interests['honors']            = 'Heltzer Honors Program';
        $special_interests['watauga_global']    = 'Watauga Global Community';
        $special_interests['teaching']          = 'Teaching Fellows';
        $special_interests['servant_leaders']   = 'Community of Servant Leaders';
        $special_interests['sciences']          = 'Academy of Sciences';
        $special_interests['language']          = 'Language & Culture Community';
        $special_interests['black_and_gold']    = 'Black & Gold Community';
        $special_interests['sophomore']         = 'Sophomore Year Experience';
        $special_interests['living_free']       = 'Living Free Community';
        $special_interests['quite_study']       = 'Quiet Study Community';
        $special_interests['educators']         = 'Community for Future Educators';
        $special_interests['man_floor']         = 'The Man Floor';
        $special_interests['sorority_adp']      = 'Alpha Delta Pi Sorority';
        $special_interests['sorority_ap']       = 'Aplha Phi Sorority';
        $special_interests['sorority_co']       = 'Chi Omega Sorority';
        $special_interests['sorority_dz']       = 'Delta Zeta Sorority';
        $special_interests['sorority_kd']       = 'Kappa Delta Sorority';
        $special_interests['sorority_pm']       = 'Phi Mu Sorority';
        $special_interests['sorority_sk']       = 'Sigma Kappa Sorority';
        $special_interests['sorority_aop']      = 'Alpha Omicron Pi Sorority';
        $special_interests['ra']                = 'Resident Assistants';
        $special_interests['international']     = 'International Programs';

        return $special_interests;
    }

}

?>
