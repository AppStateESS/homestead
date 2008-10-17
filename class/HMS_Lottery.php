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

        HMS_Activity_Log::log_activity(HMS_ADMIN_USER, ACTIVITY_LOTTERY_EXECUTED, HMS_ADMIN_USER);

        # One-time date/time calculations, setup for later on
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $term_year = HMS_Term::get_term_year($term);
        $now = mktime();
        $expire_time = $now + (INVITE_TTL_HRS * 3600);
        $year = HMS_Term::term_to_text($term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($term),TRUE);

        $output = array(); // An array for holding the text output, one line per array element.

        $output[] = "Lottery system invoked on " . date("d M, Y @ g:i:s", $now) . " ($now)";
        
        HMS_Lottery::send_winning_reminder_emails($term);

        HMS_Lottery::send_roommate_reminder_emails($term);

        # Count the number of female outstanding invites
        $female_invites_outstanding = HMS_Lottery::count_outstanding_invites($term, FEMALE);
        if($female_invites_outstanding === FALSE){
            $output[] = "error counting outstanding female invites";
            HMS_Lottery::lottery_complete("FAILED", $output);
        }else{
            $output[] = "$female_invites_outstanding female invites outstanding";
        }

        $male_invites_outstanding = HMS_Lottery::count_outstanding_invites($term, MALE);
        if($male_invites_outstanding === FALSE){
            $output[] = "error counting outstanding male invites";
            HMS_Lottery::lottery_complete("FAILED", $output);
        }else{
            $output[] = "$male_invites_outstanding male invites oustanding";
        }

        # Get a total number of invites outstanding
        $outstanding_invite_count = $male_invites_outstanding + $female_invites_outstanding;

        $output[] = "$outstanding_invite_count total invites outstanding";

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

            # Get the number of used rooms in this hall
            $used_rooms = $hall->count_lottery_used_rooms();

            if($used_rooms === FALSE){
                $output[] = 'Error while counting full rooms. Check the error logs.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            $output[] = "$used_rooms lottery rooms used";

            # Calculate the remaining number of rooms allowed for the lottery in this hall
            $remaining_rooms_this_hall = $lottery_rooms - $used_rooms;

            $output[] = "$remaining_rooms_this_hall remaining rooms available for lottery";

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

            $output[] = "Running totals:";
            $output[] = "$remaining_rooms remaining lottery rooms total";

            $remaining_coed_rooms   += $coed_rooms_this_hall;
            $remaining_male_rooms   += $male_rooms_this_hall;
            $remaining_female_rooms += $female_rooms_this_hall;

            $output[] = "$remaining_coed_rooms total remaining coed rooms";
            $output[] = "$remaining_male_rooms total remaining male rooms";
            $output[] = "$remaining_female_rooms total remaining female rooms";
        }

        # If there are no free rooms and no outstanding invites, then we're done
        if($remaining_rooms == 0 && $outstanding_invite_count == 0){
            $output[] = "No remaining rooms and no outstanding invites, done!";
            HMS_Lottery::lottery_complete("SUCCESS", $output, TRUE);
        }

        # Calculate the number of new invites that can be sent
        # If there are co-ed rooms, then only send as many invites as there are co-ed rooms.
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
            $output[] = "Can send $male_invites_avail new male invites";
            $output[] = "Can send $female_invites_avail new female invites";
        }

        # Make sure we aren't sending more than our max at once
        if($invites_to_send > MAX_INVITES_PER_BATCH){
            $invites_to_send = MAX_INVITES_PER_BATCH;
            $output[] = "Batch size limited to $invites_to_send";
        }

        $output[] = "Sending $invites_to_send new invites";

        # Randomly select the appropriate number of new winners
        for($i=0; $i < $invites_to_send; $i++){

            # Make sure we're not out of lottery entires to choose from
            $sql = "SELECT count(*) FROM hms_lottery_entry WHERE term = $term AND (invite_expires_on IS NULL OR invite_expires_on < $now)
                    AND physical_disability = 0
                    AND psych_disability = 0
                    AND medical_need = 0
                    AND gender_need = 0";

            $num_remaining_entries = PHPWS_DB::getOne($sql);

            if(PEAR::isError($num_remaining_entries)){
                PHPWS_Error::log($num_remaining_entries);
                $output[] = 'Error counting the number of lottery entries remaining.';
                HMS_Lottery::lottery_complete('FAILED', $output); 
            }

            if($num_remaining_entries == 0){
                $output[] = "No entries remaining, quitting!";
                HMS_Lottery::lottery_complete("SUCCESS", $output, TRUE);
            }

            $output[] = "$num_remaining_entries entries remaining";

            # Setup the percentages for weighting by class
            $soph_percent   = PHPWS_Settings::get('hms', 'lottery_per_soph');
            $jr_percent     = PHPWS_Settings::get('hms', 'lottery_per_jr');
            $senior_percent = PHPWS_Settings::get('hms', 'lottery_per_senior');

            # While we haven't found a winner yet
            $winning_student = NULL;
            $db = new PHPWS_DB('hms_lottery_entry');
            while(!isset($winning_student)){
                $db->reset();

                if($co_ed_only){
                    $db->addWhere('gender', MALE, '=', 'OR', 'gender');
                    $db->addWhere('gender', FEMALE, '=', 'OR', 'gender');
                }else{
                    # Decide if we need to pick a male, female, or either
                    if($male_invites_avail > 0){
                        $db->addWhere('gender', MALE, '=', 'OR', 'gender');
                    }

                    if($female_invites_avail > 0){
                        $db->addWhere('gender', FEMALE, '=', 'OR', 'gender');
                    }
                }

                # Make sure we don't select anyone who has disability flags set
                $db->addWhere('physical_disability', 0);
                $db->addWhere('psych_disability', 0);
                $db->addWhere('medical_need', 0);
                $db->addWhere('gender_need', 0);

                # Choose a random number
                $random_num = mt_rand(0, 100);

                # Decide which application terms the random number says to use and add
                # WHERE clauses for those application terms
                if($random_num >= 0 && $random_num < $soph_percent){
                    // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
                    $db->addWhere('application_term', ($term_year - 1) . '20', '=', 'OR', 'appterm');
                    $db->addWhere('application_term', ($term_year - 1) . '30', '=', 'OR', 'appterm');
                    $db->addWhere('application_term', ($term_year - 1) . '40', '=', 'OR', 'appterm');
                    $db->addWhere('application_term', $term_year . '10', '=', 'OR', 'appterm');
                }else if($random_num >= $jr_percent && $random_num < ($soph_percent + $jr_percent)){
                    // Choose a rising jr
                    $db->addWhere('application_term', ($term_year - 2) . '20', '=', 'OR', 'appterm');
                    $db->addWhere('application_term', ($term_year - 2) . '30', '=', 'OR', 'appterm');
                    $db->addWhere('application_term', ($term_year - 2) . '40', '=', 'OR', 'appterm');
                    $db->addWhere('application_term', ($term_year - 1) . '10', '=', 'OR', 'appterm');
                }else{
                    // Choose a rising senior or beyond
                    $db->addWhere('application_term', ($term_year - 2) . '10', '<=');
                }

                # Randomly select a student with the given entry term, who does not have an outstanding invite
                $db->addWhere('invite_expires_on', $now, '<', 'OR', 'expiration_group');
                $db->addWhere('invite_expires_on', NULL, 'IS NULL', 'OR', 'expiration_group');

                $db->addWhere('term', $term);

                $result = $db->select();

                if(PEAR::isError($result)){
                    PHPWS_Error::log($result);
                    $output[] = 'Error while trying to select a winning student.';
                    HMS_Lottery::lottery_complete('FAILED', $output); 
                }

                # If there aren't any in that bin, choose another number and start over
                if(sizeof($result) < 1){
                    continue;
                }

                # Randomly pick a student from result
                $winning_student = $result[mt_rand(0, sizeof($result)-1)];

                $winning_username = $winning_student['asu_username'];
                $output[] = "Inviting $winning_username";
            }

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

            # Send them an invite
            HMS_Email::send_lottery_invite($winning_username . '@appstate.edu', HMS_SOAP::get_name($winning_student), $expire_time, $year);

            # Log that the invite was sent
            HMS_Activity_Log::log_activity($winning_username, ACTIVITY_LOTTERY_INVITED, HMS_ADMIN_USER, 'Expires: ' . HMS_Util::get_long_date_time($expire_time));
        }
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
            $query .= " AND hms_lottery_entry.gender = " . $gender;
        }

        $result = PHPWS_DB::getOne($query);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            //TODO: use the messaging system to let someone know..
            return false;
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
        if(!HMS_Assignment::check_for_assignment($username, $term)){
            return E_ASSIGN_ALREADY_ASSIGNED;
        }

        # Actually make the assignment
        $assign_result = HMS_Assignment::assign_student($username, NULL, $invite['bed_id'], $meal_plan, 'Confirmed roommate invite', TRUE);
        if($assign_result != E_SUCCESS){
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
            default:
                break;
        }
    }


    function save_lottery_settings($lottery_term, $lottery_per_soph, $lottery_per_jr, $lottery_per_senior)
    {

        PHPWS_Settings::set('hms','lottery_term',       $lottery_term);
        PHPWS_Settings::set('hms','lottery_per_soph',   $lottery_per_soph);
        PHPWS_Settings::set('hms','lottery_per_jr',     $lottery_per_jr);
        PHPWS_Settings::set('hms','lottery_per_senior', $lottery_per_senior);

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

        $form->addText('lottery_per_soph', PHPWS_Settings::get('hms', 'lottery_per_soph'));
        $form->setSize('lottery_per_soph', 2, 3);

        $form->addText('lottery_per_jr', PHPWS_Settings::get('hms', 'lottery_per_jr'));
        $form->setSize('lottery_per_jr', 2, 3);

        $form->addText('lottery_per_senior', PHPWS_Settings::get('hms', 'lottery_per_senior'));
        $form->setSize('lottery_per_senior', 2, 3);

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
        $per_soph   = $_REQUEST['lottery_per_soph'];
        $per_jr     = $_REQUEST['lottery_per_jr'];
        $per_senior = $_REQUEST['lottery_per_senior'];

        # Make sure the percents add up to exactly 100
        if(($per_soph + $per_jr + $per_senior) != 100){
            return HMS_Lottery::show_lottery_settings(NULL, 'Error: Percents must add up to 100');
        }

        # Save the settings
        HMS_Lottery::save_lottery_settings($_REQUEST['lottery_term'], $per_soph, $per_jr, $per_senior);

        return HMS_Lottery::show_lottery_settings('Lottery settings updated.');
    }
}

?>
