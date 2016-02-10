<?php
PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');
PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

if (!defined('MAX_INVITES_PER_BATCH')) {
    define('MAX_INVITES_PER_BATCH', 500);
    define('INVITE_TTL_HRS', 48);
}


class LotteryProcess {
    private $sendMagicWinners;
    private $sendReminders;
    private $inviteCounts;
    private $applicationsRemaining;
    private $term; // ex. 201240
    private $year; // ex. 2012
    private $academicYear; // ex: 'Fall 2012 - Spring 2013'
    private $now; // current unix timestamp
    private $expireTime;
    private $hardCap;
    private $jrSoftCap;
    private $srSoftCap;
    private $output; // An array for holding the text output, one line per array element.

    // Invites sent by the process so far this run, total and by class
    private $numInvitesSent;

    public function __construct($sendMagicWinners, $sendReminders, Array $inviteCounts)
    {

        // Gender and classes
        $this->genders = array(
                MALE,
                FEMALE
        );
        $this->classes = array(
                CLASS_SENIOR,
                CLASS_JUNIOR,
                CLASS_SOPHOMORE
        );

        // Send magic winners?
        $this->sendMagicWinners = $sendMagicWinners;

        // Send reminders?
        $this->sendReminders = $sendReminders;

        // Invite counts to be sent
        $this->inviteCounts = $inviteCounts;

        // One-time date/time calculations, setup for later on
        $this->term = PHPWS_Settings::get('hms', 'lottery_term');
        $this->year = Term::getTermYear($this->term);
        $this->academicYear = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        $this->now = time();
        $this->expireTime = $this->now + (INVITE_TTL_HRS * 3600);

        // Hard Cap
        $this->hardCap = LotteryProcess::getHardCap();

        // Soft caps
        $this->jrSoftCap = LotteryProcess::getJrSoftCap();
        $this->srSoftCap = LotteryProcess::getSrSoftCap();

        // Invites Sent by this process so far this run
        $this->numInvitesSent['TOTAL'] = 0;
        foreach ($this->classes as $c) {
            foreach ($this->genders as $g) {
                $this->numInvitesSent[$c][$g] = 0;
            }
        }

        $this->output = array();
    }

    public function sendInvites()
    {
        HMS_Activity_Log::log_activity('hms', ACTIVITY_LOTTERY_EXECUTED, 'hms');
        $this->output[] = "Lottery system invoked on " . date("d M, Y @ g:i:s", $this->now) . " ($this->now)";

        /**
         * **
         * Check the hard cap.
         * Don't do anything if it's been reached.
         */
        if (LotteryProcess::hardCapReached($this->term)) {
            $this->output[] = 'Hard cap reached. Done!';
            return;
        }

        /**
         * *****************
         * Reminder Emails *
         * *****************
         */

        $output = array();

        if ($this->sendReminders) {
            $this->output[] = "Sending invite reminder emails...";
            $this->sendWinningReminderEmails();

            $output[] = "Sending roommate invite reminder emails...";
            $this->sendRoommateReminderEmails();
        }

        // check the jr/sr soft caps
        if (LotteryProcess::jrSoftCapReached($this->term)) {
            $this->inviteCounts[CLASS_JUNIOR][MALE] = 0;
            $this->inviteCounts[CLASS_JUNIOR][FEMALE] = 0;
        }

        if (LotteryProcess::srSoftCapReached($this->term)) {
            $this->inviteCounts[CLASS_SENIOR][MALE] = 0;
            $this->inviteCounts[CLASS_SENIOR][FEMALE] = 0;
        }

        /**
         * ****
         * Count the number of remaining entries
         * *******
         */
        try {
            // Count remaining applications by class and gender
            $this->applicationsRemaining = array();
            foreach ($this->classes as $c) {
                foreach ($this->genders as $g) {
                    $this->applicationsRemaining[$c][$g] = LotteryProcess::countRemainingApplicationsByClassGender($this->term, $c, $g);
                }
            }
        } catch (Exception $e) {
            $this->output[] = 'Error counting outstanding lottery entires, quitting. Exception: ' . $e->getMessage();
            return;
        }

        $this->output[] = "{$this->applicationsRemaining[CLASS_SENIOR][MALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_SENIOR][FEMALE]} senior female lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_JUNIOR][MALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_JUNIOR][FEMALE]} senior female lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_SOPHOMORE][MALE]} senior male lottery entries remaining";
        $this->output[] = "{$this->applicationsRemaining[CLASS_SOPHOMORE][FEMALE]} senior female lottery entries remaining";

        /**
         * ****************
         * Send magic winner invites
         */
        if ($this->sendMagicWinners) {
            $this->output[] = "Sending magic winner invites...";
            while (($magicWinner = $this->getMagicWinner()) != null) {
                $student = StudentFactory::getStudentByBannerId($magicWinner['banner_id'], $this->term);
                $this->sendInvite($student);
            }
        }

        /**
         * ****************
         * Send Invites
         */
        foreach ($this->classes as $c) {
            foreach ($this->genders as $g) {

                $this->output[] = "Sending {$this->inviteCounts[$c][$g]} invites for class: {$c}, gender: {$g}";
                $this->output[] = "There are {$this->applicationsRemaining[$c][$g]} remaining applicants of that class and gender.";

                // While we need to send an invite and there is an applicant remaining
                // And we haven't exceeded our batch size
                while ($this->inviteCounts[$c][$g] > $this->numInvitesSent[$c][$g] && $this->applicationsRemaining[$c][$g] >= 1 && $this->numInvitesSent['TOTAL'] <= MAX_INVITES_PER_BATCH) {
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
        try {
            $entry = HousingApplicationFactory::getAppByStudent($student, $this->term, 'lottery');
            $entry->invited_on = $this->now;

            $entry->save();
        } catch (Exception $e) {
            $this->output[] = 'Error while trying to select a winning student. Exception: ' . $e->getMessage();
            return;
        }

        // Update the total count
        $this->numInvitesSent['TOTAL']++;

        // Send the notification email
        HMS_Email::send_lottery_invite($student->getUsername(), $student->getName(), $this->academicYear);

        // Log that the invite was sent
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_LOTTERY_INVITED, UserStatus::getUsername(), "Expires on " . date('m/d/Y h:i:s a', $this->expireTime));
    }

    private function sendWinningReminderEmails()
    {
        $ttl = INVITE_TTL_HRS * 3600;

        // Get a list of lottery winners who have not chosen a room yet, send them reminder emails
        $query = "select username from hms_new_application
                    JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (select * from hms_assignment where term = {$this->term}) AS foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL
                    AND hms_new_application.term = {$this->term}
                    AND application_type = 'lottery'
                    AND invited_on IS NOT NULL
                    AND (hms_lottery_application.invited_on + $ttl) > {$this->now}";

        $result = PHPWS_DB::getAll($query);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        foreach ($result as $row) {
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
                        AND hms_lottery_reservation.term = {$this->term}
                        AND hms_lottery_reservation.expires_on > " . $this->now;

        $result = PHPWS_DB::getAll($query);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        foreach ($result as $row) {
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
        $query = "SELECT * FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                            LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term = {$this->term}) as foo ON hms_new_application.username = foo.asu_username
                            WHERE foo.asu_username IS NULL AND (hms_lottery_application.invited_on IS NULL)
                            AND hms_new_application.term = {$this->term}
                            AND hms_lottery_application.magic_winner = 1";

        $result = PHPWS_DB::getRow($query);

        if (PHPWS_Error::logIfError($result)) {
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
                    AND hms_new_application.gender = $gender
                    AND hms_new_application.username NOT IN (SELECT username FROM hms_learning_community_applications JOIN hms_learning_community_assignment ON hms_learning_community_applications.id = hms_learning_community_assignment.application_id WHERE term = {$this->term} and state IN ('confirmed', 'selfselect-assigned')) ";

        $term_year = Term::getTermYear($this->term);
        if ($class == CLASS_SOPHOMORE) {
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '30 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '40 ';
            $query .= 'OR application_term = ' . $term_year . '10';
            $query .= ') ';
        } else if ($class == CLASS_JUNIOR) {
            // Choose a rising jr
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20 ';
            $query .= 'OR application_term = ' . ($term_year - 2) . '30 ';
            $query .= 'OR application_term = ' . ($term_year - 2) . '40 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '10';
            $query .= ') ';
        } else {
            // Choose a rising senior or beyond
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10 ';
        }

        $result = PHPWS_DB::getAll($query);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if (sizeof($result) < 1) {
            return null;
        }

        // Randomly pick a student from result
        $winningRow = $result[mt_rand(0, sizeof($result) - 1)];

        return $winningRow;
    }

    public function getOutput()
    {
        return $this->output;
    }

    /**
     * ***********************
     * Static Helper Methods *
     * ***********************
     */
    public static function getHardCap()
    {
        $hardCap = PHPWS_Settings::get('hms', 'lottery_hard_cap');
        if (!isset($hardCap) || empty($hardCap)) {
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

        if (PHPWS_Error::isError($count)) {
            throw new DatabaseException($count->toString());
        }

        return $count;
    }

    public static function countLotteryAssignedByClassGender($term, $class, $gender = null)
    {
        $query = "SELECT count(*) FROM hms_assignment LEFT OUTER JOIN hms_new_application ON (hms_assignment.banner_id = hms_new_application.banner_id  AND hms_assignment.term = hms_new_application.term )WHERE hms_assignment.term = $term and reason = 'lottery' ";

        if (isset($gender)) {
            $query .= "AND hms_new_application.gender = $gender ";
        }

        $term_year = Term::getTermYear($term);
        if ($class == CLASS_SOPHOMORE) {
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (hms_assignment.application_term = ' . ($term_year - 1) . '20 ';
            $query .= 'OR hms_assignment.application_term = ' . ($term_year - 1) . '30 ';
            $query .= 'OR hms_assignment.application_term = ' . ($term_year - 1) . '40 ';
            $query .= 'OR hms_assignment.application_term = ' . $term_year . '10';
            $query .= ') ';
        } else if ($class == CLASS_JUNIOR) {
            // Choose a rising jr
            $query .= 'AND (hms_assignment.application_term = ' . ($term_year - 2) . '20 ';
            $query .= 'OR hms_assignment.application_term = ' . ($term_year - 2) . '30 ';
            $query .= 'OR hms_assignment.application_term = ' . ($term_year - 2) . '40 ';
            $query .= 'OR hms_assignment.application_term = ' . ($term_year - 1) . '10';
            $query .= ') ';
        } else {
            // Choose a rising senior or beyond
            $query .= 'AND hms_assignment.application_term <= ' . ($term_year - 2) . '10 ';
        }

        $assignments = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($assignments)) {
            throw new DatabaseException($assignments->toString());
        }

        return $assignments;
    }

    public static function hardCapReached($term)
    {
        $hardCap = LotteryProcess::getHardCap();
        $assigned = LotteryProcess::countLotteryAssigned($term);

        if ($assigned >= $hardCap) {
            return true;
        }

        return false;
    }

    public static function jrSoftCapReached($term)
    {
        $softCap = LotteryProcess::getJrSoftCap();
        $assigned = LotteryProcess::countLotteryAssignedByClassGender($term, CLASS_JUNIOR);

        if ($assigned >= $softCap) {
            return true;
        }

        return false;
    }

    public static function srSoftCapReached($term)
    {
        $softCap = LotteryProcess::getSrSoftCap();
        $assigned = LotteryProcess::countLotteryAssignedByClassGender($term, CLASS_SENIOR);

        if ($assigned >= $softCap) {
            return true;
        }

        return false;
    }

    public static function getJrSoftCap()
    {
        $softCap = PHPWS_Settings::get('hms', 'lottery_jr_goal');
        if (!isset($softCap) || empty($softCap)) {
            throw new InvalidArgumentException('Junior soft cap not set!');
        }

        return $softCap;
    }

    public static function getSrSoftCap()
    {
        $softCap = PHPWS_Settings::get('hms', 'lottery_sr_goal');
        if (!isset($softCap) || empty($softCap)) {
            throw new InvalidArgumentException('Junior soft cap not set!');
        }

        return $softCap;
    }

    public static function countInvitesByClassGender($term, $class, $gender = null)
    {
        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    WHERE hms_lottery_application.invited_on IS NOT NULL
                    AND hms_new_application.term = $term ";

        if (isset($gender)) {
            $query .= "AND hms_new_application.gender = $gender ";
        }

        $term_year = Term::getTermYear($term);
        if ($class == CLASS_SOPHOMORE) {
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '30 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '40 ';
            $query .= 'OR application_term = ' . $term_year . '10';
            $query .= ') ';
        } else if ($class == CLASS_JUNIOR) {
            // Choose a rising jr
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20 ';
            $query .= 'OR application_term = ' . ($term_year - 2) . '30 ';
            $query .= 'OR application_term = ' . ($term_year - 2) . '40 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '10';
            $query .= ') ';
        } else {
            // Choose a rising senior or beyond
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10 ';
        }

        $remainingApplications = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($remainingApplications)) {
            throw new DatabaseException($remainingApplications->toString());
        }

        return $remainingApplications;
    }

    public static function countOutstandingInvites($term, $class, $gender = null)
    {
        $now = time();
        $ttl = INVITE_TTL_HRS * 3600;

        $query = "SELECT count(*) FROM hms_new_application
        JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
        LEFT OUTER JOIN hms_assignment ON (hms_new_application.banner_id = hms_assignment.banner_id AND hms_new_application.term = hms_assignment.term)
        WHERE hms_assignment.banner_id IS NULL
        AND hms_lottery_application.invited_on IS NOT NULL
        AND hms_new_application.term = $term
        AND (hms_lottery_application.invited_on + $ttl) > $now";

        if (isset($gender)) {
            $query .= "AND hms_new_application.gender = $gender ";
        }

        $term_year = Term::getTermYear($term);
        if ($class == CLASS_SOPHOMORE) {
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (hms_new_application.application_term = ' . ($term_year - 1) . '20 ';
            $query .= 'OR hms_new_application.application_term = ' . ($term_year - 1) . '30 ';
            $query .= 'OR hms_new_application.application_term = ' . ($term_year - 1) . '40 ';
            $query .= 'OR hms_new_application.application_term = ' . $term_year . '10';
            $query .= ') ';
        } else if ($class == CLASS_JUNIOR) {
            // Choose a rising jr
            $query .= 'AND (hms_new_application.application_term = ' . ($term_year - 2) . '20 ';
            $query .= 'OR hms_new_application.application_term = ' . ($term_year - 2) . '30 ';
            $query .= 'OR hms_new_application.application_term = ' . ($term_year - 2) . '40 ';
            $query .= 'OR hms_new_application.application_term = ' . ($term_year - 1) . '10';
            $query .= ') ';
        } else {
            // Choose a rising senior or beyond
            $query .= 'AND hms_new_application.application_term <= ' . ($term_year - 2) . '10 ';
        }

        // test($query,1);

        $remainingApplications = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($remainingApplications)) {
            throw new DatabaseException($remainingApplications->toString());
        }

        return $remainingApplications;
    }

    /**
     * Returns the number of outstanding roommate invites.
     *
     * @param int $term
     * @throws DatabaseException
     * @return int Number of outstanding roommate invites.
     */
    public static function countOutstandingRoommateInvites($term)
    {
        $query = "select count(*) FROM hms_lottery_reservation
                                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term={$term}) as foo ON hms_lottery_reservation.asu_username = foo.asu_username
                                WHERE foo.asu_username IS NULL
                                AND hms_lottery_reservation.expires_on > " . time();

        $result = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result);
        }

        return $result;
    }

    /**
     * ********************
     * Application Counts *
     * ********************
     */
    public static function countGrossApplicationsByClassGender($term, $class = null, $gender = null)
    {
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    WHERE term = $term ";

        if (isset($gender)) {
            $query .= "AND hms_new_application.gender = $gender ";
        }

        if (isset($class) && $class == CLASS_SOPHOMORE) {
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        } else if (isset($class) && $class == CLASS_JUNIOR) {
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        } else if (isset($class)) {
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }

        $result = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public static function countNetAppsByClassGender($term, $class = null, $gender = null)
    {
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    WHERE term = $term AND special_interest IS NULL AND hms_new_application.username NOT IN (SELECT username FROM hms_learning_community_applications
                    JOIN hms_learning_community_assignment ON hms_learning_community_applications.id = hms_learning_community_assignment.application_id
                    WHERE term = $term and (state = 'confirmed' OR state = 'selfselect-assigned'))";

        if (isset($gender)) {
            $query .= "AND hms_new_application.gender = $gender ";
        }

        if (isset($class) && $class == CLASS_SOPHOMORE) {
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        } else if (isset($class) && $class == CLASS_JUNIOR) {
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        } else if (isset($class)) {
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }

        $result = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public static function countRemainingApplications($term)
    {
        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
        LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
        WHERE foo.asu_username IS NULL AND hms_lottery_application.invited_on IS NULL
        AND hms_new_application.term = $term
        AND special_interest IS NULL
        AND hms_new_application.username NOT IN (SELECT username FROM hms_learning_community_applications JOIN hms_learning_community_assignment ON hms_learning_community_applications.id = hms_learning_community_assignment.application_id WHERE term = $term and state IN ('confirmed', 'selfselect-assigned'))";

        $remainingApplications = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($remainingApplications)) {
            throw new DatabaseException($remainingApplications->toString());
        }

        return $remainingApplications;
    }

    public static function countRemainingApplicationsByClassGender($term, $class, $gender = null)
    {
        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL AND hms_lottery_application.invited_on IS NULL
                    AND hms_new_application.term = $term
                    AND special_interest IS NULL
                    AND hms_new_application.username NOT IN (SELECT username FROM hms_learning_community_applications JOIN hms_learning_community_assignment ON hms_learning_community_applications.id = hms_learning_community_assignment.application_id WHERE term = $term and state IN ('confirmed', 'selfselect-assigned')) ";

        if (isset($gender)) {
            $query .= "AND hms_new_application.gender = $gender ";
        }

        $term_year = Term::getTermYear($term);
        if ($class == CLASS_SOPHOMORE) {
            // Choose a rising sophmore (summer 1 thru fall of the previous year, plus spring of the same year)
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '30 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '40 ';
            $query .= 'OR application_term = ' . $term_year . '10';
            $query .= ') ';
        } else if ($class == CLASS_JUNIOR) {
            // Choose a rising jr
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20 ';
            $query .= 'OR application_term = ' . ($term_year - 2) . '30 ';
            $query .= 'OR application_term = ' . ($term_year - 2) . '40 ';
            $query .= 'OR application_term = ' . ($term_year - 1) . '10';
            $query .= ') ';
        } else {
            // Choose a rising senior or beyond
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10 ';
        }

        $remainingApplications = PHPWS_DB::getOne($query);

        if (PHPWS_Error::logIfError($remainingApplications)) {
            throw new DatabaseException($remainingApplications->toString());
        }

        return $remainingApplications;
    }
}
