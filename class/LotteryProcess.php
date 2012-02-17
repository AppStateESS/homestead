<?php

PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');
PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

define('MAX_INVITES_PER_BATCH', 500);
define('INVITE_TTL_HRS', 48);

class LotteryProcess {

    private $sendMagicWinners;
    private $inviteCounts;
    
    private $applicationsRemaining;

    private $term; // ex. 201240
    private $year; // ex. 2012
    private $academicYear; //ex: 'Fall 2012 - Spring 2013'
    private $now; // current unix timestamp
    private $expireTime;

    private $hardCap;

    private $output;  // An array for holding the text output, one line per array element.

    // Invites sent by the process so far this run, total and by class
    private $numInvitesSent;

    public function __construct($sendMagicWinners, Array $inviteCounts){

        //Gender and classes
        $this->genders = array(MALE, FEMALE);
        $this->classes = array(CLASS_SENIOR, CLASS_JUNIOR, CLASS_SOPHOMORE);
        
        // Send magic winners?
        $this->sendMagicWinners = $sendMagicWinners;

        // Invite counts to be sent
        $this->inviteCounts = $inviteCounts;

        // One-time date/time calculations, setup for later on
        $this->term = PHPWS_Settings::get('hms', 'lottery_term');
        $this->year = Term::getTermYear($this->term);
        $this->academicYear = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        $this->now = mktime();
        $this->expireTime = $this->now + (INVITE_TTL_HRS * 3600);

        // Hard Cap
        $this->hardCap = LotteryProcess::getHardCap();
        
        // Soft caps
        //TODO

        // Invites Sent by this process so far this run
        $this->numInvitesSent['TOTAL']         = 0;
        foreach($this->classes as $c){
            foreach($this->genders as $g){
                $this->numInvitesSent[$c][$g] = 0;
            }
        }
        
        $this->output = array();
    }

    public function sendInvites()
    {
        HMS_Activity_Log::log_activity('hms', ACTIVITY_LOTTERY_EXECUTED, 'hms');

        /****
         * Check the hard cap. Don't do anything if it's been reached.
         */

        if(LotteryProcess::hardCapReached($this->term)){
            $this->output[] = 'Hard cap reached. Done!';
        }
        
        /*******************
         * Reminder Emails *
         *******************/
        $this->output[] = "Lottery system invoked on " . date("d M, Y @ g:i:s", $this->now) . " ($this->now)";

        $this->output[] = "Sending invite reminder emails...";
        $this->sendWinningReminderEmails();

        $output[] = "Sending roommate invite reminder emails...";
        $this->sendRoommateReminderEmails();
        
        //TODO check the jr/sr soft caps
        
        /******
         * Count the number of remaining entries
         *********/
        try{
            // Count remaining applications by class and gender
            $this->applicationsRemaining = array();
            foreach($this->classes as $c){
                foreach($this->genders as $g){
                    $this->applicationsRemaining[$c][$g] = LotteryProcess::countRemainingApplicationsByClassGender($this->term, $c, $g);
                }
            }
        }catch(Exception $e) {
            $this->output[] = 'Error counting outstanding lottery entires, quitting. Exception: ' . $e->getMessage();
            return;
        }
        
        $this->output[] = "{$this->applicationsRemaining[CLASS_SENIOR][MALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_SENIOR][FEMALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_JUNIOR][MALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_JUNIOR][FEMALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_SOPHOMORE][MALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_SOPHOMORE][FEMALE]} senior male lottery entries remaining";
        
        /******************
         * Send magic winner invites
         */
        if($this->sendMagicWinners){
            $this->output[] = "Sending magic winner invites...";
            while(($magicWinner = $this->getMagicWinner()) != null){
                $student = StudentFactory::getStudentByBannerId($magicWinner['banner_id'], $this->term);
                $this->sendInvite($student);
                break;
            }
        }
        
        /******************
         * Send Invites
         */
        foreach($this->classes as $c){
            foreach($this->genders as $g){
                
                $this->output[] = "Sending {$this->inviteCounts[$c][$g]} invites for class: {$c}, gender: {$g}";
                $this->output[] = "There are {$this->applicationsRemaining[$c][$g]} remaining applicants of that class and gender.";
                
                // While we need to send an invite and there is an applicant remaining
                // And we haven't exceeded our batch size
                while($this->inviteCounts[$c][$g] > $this->numInvitesSent[$c][$g] && $this->applicationsRemaining[$c][$g] >= 1 && $this->numInvitesSent['TOTAL'] <= MAX_INVITES_PER_BATCH){
                    // Send an invite to the proper class & gender
                    $winningRow = $this->chooseWinner($c, $g);
                    
                    $student = StudentFactory::getStudentByBannerId($winningRow['banner_id'], $this->term);

                    $this->sendInvite($student);
                    
                    // Update counts
                    $this->numInvitesSent[$c][$g]++;
                    $this->applicationsRemaining[$c][$g]--;
                }
            }
        }
        
        $this->output[] = "Done. Sent {$this->numInvitesSent['TOTAL']} invites total.";
    }
    
    private function sendInvite(Student $student)
    {
        $this->output[] = "Inviting {$student->getUsername()} ({$student->getBannerId()})";
        
        // Update the winning student's invite
        try{
            $entry = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);
            $entry->invited_on = $this->now;
        
            $result = $entry->save();
        }catch(Exception $e) {
            $this->output[] = 'Error while trying to select a winning student. Exception: ' . $e->getMessage();
            return;
        }
        
        // Update the total count
        $this->numInvitesSent['TOTAL']++;
        
        // Send the notification email
        HMS_Email::send_lottery_invite($student->getUsername(), $student->getName(), $this->academicYear);
        
        // Log that the invite was sent
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_LOTTERY_INVITED, UserStatus::getUsername());
    }
    
    private function sendWinningReminderEmails()
    {
        // Get a list of lottery winners who have not chosen a room yet, send them reminder emails
        $query = "select hms_new_application.username FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                        LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term={$this->term}) as foo ON hms_new_application.username = foo.asu_username
                        WHERE foo.asu_username IS NULL
                        AND hms_lottery_application.invited_on IS NOT NULL";
        
        $result = PHPWS_DB::getAll($query);
        
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        
        //$year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));
        
        foreach($result as $row) {
            $student = StudentFactory::getStudentByUsername($row['username'], $this->term);
            HMS_Email::send_lottery_invite_reminder($row['username'], $student->getName(), $this->academicYear);
            HMS_Activity_Log::log_activity($row['username'], ACTIVITY_LOTTERY_REMINDED, UserStatus::getUsername());
        }
    }

    private function sendRoommateReminderEmails()
    {
        // Get a list of outstanding roommate requests, send them reminder emails
        $query = "select hms_lottery_reservation.* FROM hms_lottery_reservation
                        LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term={$this->term}) as foo ON hms_lottery_reservation.asu_username = foo.asu_username
                        WHERE foo.asu_username IS NULL
                        AND hms_lottery_reservation.expires_on > " . $this->now;
        
        $result = PHPWS_DB::getAll($query);
        
        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }
        
        foreach($result as $row) {
            $student = StudentFactory::getStudentByUsername($row['asu_username'], $this->term);
            $requestor = StudentFactory::getStudentByUsername($row['requestor'], $this->term);
        
            $bed = new HMS_Bed($row['bed_id']);
            $hall_room = $bed->where_am_i();
            HMS_Email::send_lottery_roommate_reminder($row['asu_username'], $student->getName(), $row['expires_on'], $requestor->getName(), $hall_room, $this->academicYear);
            HMS_Activity_Log::log_activity($row['asu_username'], ACTIVITY_LOTTERY_ROOMMATE_REMINDED, UserStatus::getUsername());
        }
    }
    
    private function getMagicWinner()
    {
        $now = mktime();
        
        $query = "SELECT * FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                            LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term = {$this->term}) as foo ON hms_new_application.username = foo.asu_username
                            WHERE foo.asu_username IS NULL AND (hms_lottery_application.invited_on IS NULL)
                            AND hms_new_application.term = {$this->term}
                            AND hms_lottery_application.magic_winner = 1";
        
        $result = PHPWS_DB::getRow($query);
        
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }
    
    private function chooseWinner($class, $gender)
    {
        $query = "SELECT * FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term={$this->term}) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL
                    AND hms_lottery_application.invited_on IS NULL
                    AND hms_new_application.term = {$this->term}
                    AND hms_lottery_application.magic_winner = 0
                    AND hms_lottery_application.special_interest IS NULL
                    AND hms_new_application.gender = $gender ";
        
        $term_year = Term::getTermYear($this->term);
        if($class == CLASS_SOPHOMORE) {
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20 ';
            $query .=   'OR application_term = ' . ($term_year - 1) . '30 ';
            $query .=   'OR application_term = ' . ($term_year - 1) . '40 ';
            $query .=   'OR application_term = ' . $term_year . '10';
            $query .= ') ';
        }else if($class == CLASS_JUNIOR) {
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
        
        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }
        
        if(sizeof($result) < 1){
            return null;
        }
        
        // Randomly pick a student from result
        $winningRow = $result[mt_rand(0, sizeof($result)-1)];
        
        return $winningRow;
    }
    
    public function getOutput(){
        return $this->output;
    }

    /*************************
     * Static Helper Methods *
    *************************/
    public static function getHardCap()
    {
        $hardCap = PHPWS_Settings::get('hms', 'lottery_hard_cap');
        if(!isset($hardCap) || empty($hardCap)){
            throw new InvalidArgumentException('Hard cap not set!');
        }
        
        return $hardCap;
    }

    public static function countLotteryAssigned($term)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('term', $term);
        $db->addWhere('reason', ASSIGN_LOTTERY);
        
        $count = $db->count();
        
        if(PHPWS_Error::isError($count)){
            throw new DatabaseException($count->toString());
        }
        
        return $count;
    }
    
    public static function countLotteryAssignedByClassGender($term, $class, $gender)
    {
        
    }
    
    public static function hardCapReached($term)
    {
        $hardCap  = LotteryProcess::getHardCap();
        $assigned = LotteryProcess::countLotteryAssigned($term);
    
        if($assigned >= $hardCap){
            return true;
        }
    
        return false;
    }

    public static function countInvitesByClassGender($term, $class, $gender = null)
    {
        //TODO
    }
    
    public static function countOutstandingRoommateInvites($term)
    {
        $query = "select count(*) FROM hms_lottery_reservation
                                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term={$this->term}) as foo ON hms_lottery_reservation.asu_username = foo.asu_username
                                WHERE foo.asu_username IS NULL
                                AND hms_lottery_reservation.expires_on > " . time();
        
        $result = PHPWS_DB::getOne($query);
        
        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result);
        }
        
        return $result;
    }
    
    public static function countRemainingApplications($term)
    {
        $now = mktime();
        
        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                            LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
                            WHERE foo.asu_username IS NULL AND hms_lottery_application.invited_on IS NULL
                            AND hms_new_application.term = $term
                            AND special_interest IS NULL";
        
        $remainingApplications = PHPWS_DB::getOne($query);
        
        if(PHPWS_Error::logIfError($remainingApplications)) {
            throw new DatabaseException($remainingApplications->toString());
        }
        
        return $remainingApplications;
    }
    
    public static function countRemainingApplicationsByClassGender($term, $class, $gender = null)
    {
        $now = mktime();
        
        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL AND hms_lottery_application.invited_on IS NULL
                    AND hms_new_application.term = $term
                    AND special_interest IS NULL ";
        
        if(isset($gender)){
            $query .= "AND hms_new_application.gender = $gender ";
        }
        
        $term_year = Term::getTermYear($term);
        if($class == CLASS_SOPHOMORE) {
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20 ';
            $query .=   'OR application_term = ' . ($term_year - 1) . '30 ';
            $query .=   'OR application_term = ' . ($term_year - 1) . '40 ';
            $query .=   'OR application_term = ' . $term_year . '10';
            $query .= ') ';
        }else if($class == CLASS_JUNIOR) {
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
        
        $remainingApplications = PHPWS_DB::getOne($query);
        
        if(PHPWS_Error::logIfError($remainingApplications)) {
            throw new DatabaseException($remainingApplications->toString());
        }
        
        return $remainingApplications;
    }
}

?>