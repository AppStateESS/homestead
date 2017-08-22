<?php

namespace Homestead;

define('MAX_INVITES_PER_BATCH', 500);
define('INVITE_TTL_HRS', 48);


class HMS_Lottery {

    /**
     * Looks for an entry with the 'magic_winner' flag set and returns it, otherwise it returns null
     */
    public static function check_magic_winner($term)
    {
        $now = time();

        $query = "SELECT * FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL AND (hms_lottery_application.invite_expires_on < $now OR hms_lottery_application.invite_expires_on IS NULL)
                    AND hms_new_application.term = $term
                    AND hms_lottery_application.magic_winner = 1";

        $result = PHPWS_DB::getRow($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return null;
        }

        if (!isset($result) || empty($result)) {
            return null;
        } else {
            return $result;
        }
    }

    /**
     * Returns the number of lottery entries currently outstanding (i.e.
     * non-winners)
     */
    public static function count_remaining_entries($term)
    {
        $now = time();

        $sql = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL AND (hms_lottery_application.invite_expires_on < $now OR hms_lottery_application.invite_expires_on IS NULL)
                AND hms_new_application.term = $term
                AND special_interest IS NULL";

        $num_remaining_entries = PHPWS_DB::getOne($sql);

        if (PEAR::isError($num_remaining_entries)) {
            PHPWS_Error::log($num_remaining_entries);
            return false;
        }

        return $num_remaining_entries;
    }

    public static function count_outstanding_invites($term, $gender = null)
    {
        $now = time();
        $query = "select count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_application.invite_expires_on > $now
                AND hms_new_application.term = $term";
        if (isset($gender)) {
            $query .= ' AND hms_new_application.gender = ' . $gender;
        }

        $result = PHPWS_DB::getOne($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return $result;
        }
    }

    /*
     * Returns the number of outstanding *roommate* invites
    */
    public static function count_outstanding_roommate_invites($term)
    {
        $now = time();
        $query = "select count(*) FROM hms_lottery_reservation
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_lottery_reservation.asu_username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_reservation.expires_on > $now
                AND hms_lottery_reservation.term = $term";

        $result = PHPWS_DB::getOne($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return $result;
        }
    }

    /*
     * Returns the number of invites sent (confirmed or outstanding) for the given class
    */
    public static function count_invites_by_class($term, $class)
    {
        $now = time();
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_new_application.username = foo.asu_username
                WHERE ((foo.asu_username IS NULL AND hms_lottery_application.invite_expires_on > $now) OR (foo.asu_username IS NOT NULL AND hms_lottery_application.invite_expires_on IS NOT NULL))
                AND hms_new_application.term = $term ";

        if ($class == CLASS_SOPHOMORE) {
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        } else if ($class == CLASS_JUNIOR) {
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        } else {
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }

        $result = PHPWS_DB::getOne($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return $result;
        }
    }

    public static function count_remaining_entries_by_class($term, $class)
    {
        $now = time();
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL AND (hms_lottery_application.invite_expires_on < $now OR hms_lottery_application.invite_expires_on IS NULL)
                    AND hms_new_application.term = $term
                    AND special_interest IS NULL ";

        if ($class == CLASS_SOPHOMORE) {
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        } else if ($class == CLASS_JUNIOR) {
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        } else {
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }

        $result = PHPWS_DB::getOne($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return $result;
        }
    }

    public static function count_outstanding_invites_by_class($term, $class)
    {
        $now = time();
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term) as foo ON hms_new_application.username = foo.asu_username
                    WHERE foo.asu_username IS NULL
                    AND hms_lottery_application.invite_expires_on > $now
                    AND hms_new_application.term = $term ";

        if ($class == CLASS_SOPHOMORE) {
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        } else if ($class == CLASS_JUNIOR) {
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        } else {
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }

        $result = PHPWS_DB::getOne($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return $result;
        }
    }

    public static function count_applications_by_class($term, $class)
    {
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    WHERE term = $term
                    AND special_interest IS NULL ";

        if ($class == CLASS_SOPHOMORE) {
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        } else if ($class == CLASS_JUNIOR) {
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        } else {
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }

        $result = PHPWS_DB::getOne($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return $result;
        }
    }

    public static function count_assignments_by_class($term, $class)
    {
        $term_year = Term::getTermYear($term);

        $query = "SELECT count(*) from hms_assignment
                    JOIN hms_new_application ON hms_assignment.asu_username = hms_new_application.username
                    JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                    WHERE hms_assignment.term = $term
                    AND hms_assignment.lottery = 1
                    AND hms_new_application.term = $term ";

        if ($class == CLASS_SOPHOMORE) {
            $query .= 'AND (application_term = ' . ($term_year - 1) . '20';
            $query .= ' OR application_term = ' . ($term_year - 1) . '30';
            $query .= ' OR application_term = ' . ($term_year - 1) . '40';
            $query .= ' OR application_term = ' . ($term_year) . '10';
            $query .= ')';
        } else if ($class == CLASS_JUNIOR) {
            $query .= 'AND (application_term = ' . ($term_year - 2) . '20';
            $query .= ' OR application_term = ' . ($term_year - 2) . '30';
            $query .= ' OR application_term = ' . ($term_year - 2) . '40';
            $query .= ' OR application_term = ' . ($term_year - 1) . '10';
            $query .= ')';
        } else {
            $query .= 'AND application_term <= ' . ($term_year - 2) . '10';
        }

        $result = PHPWS_DB::getOne($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return false;
        } else {
            return $result;
        }
    }

    public static function send_winning_reminder_emails($term)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModclass('hms', 'StudentFactory.php');

        // Get a list of lottery winners who have not chosen a room yet, send them reminder emails
        $query = "select hms_new_application.username, hms_lottery_application.invite_expires_on FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_application.invite_expires_on > " . time();

        $result = PHPWS_DB::getAll($query);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            test($result, 1);
        }

        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));

        foreach ($result as $row) {
            $student = StudentFactory::getStudentByUsername($row['username'], $term);
            HMS_Email::send_lottery_invite_reminder($row['username'], $student->getName(), $row['invite_expires_on'], $year);
            HMS_Activity_Log::log_activity($row['username'], ACTIVITY_LOTTERY_REMINDED, 'hms');
        }
    }

    public static function send_roommate_reminder_emails($term)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModclass('hms', 'StudentFactory.php');

        // Get a list of outstanding roommate requests, send them reminder emails
        $query = "select hms_lottery_reservation.* FROM hms_lottery_reservation
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE term=$term AND lottery = 1) as foo ON hms_lottery_reservation.asu_username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_lottery_reservation.expires_on > " . time();

        $result = PHPWS_DB::getAll($query);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            test($result, 1);
        }

        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));

        foreach ($result as $row) {
            $student = StudentFactory::getStudentByUsername($row['asu_username'], $term);
            $requestor = StudentFactory::getStudentByUsername($row['requestor'], $term);

            $bed = new HMS_Bed($row['bed_id']);
            $hall_room = $bed->where_am_i();
            HMS_Email::send_lottery_roommate_reminder($row['asu_username'], $student->getName(), $row['expires_on'], $requestor->getName(), $hall_room, $year);
            HMS_Activity_Log::log_activity($row['asu_username'], ACTIVITY_LOTTERY_ROOMMATE_REMINDED, 'hms');
        }
    }

    public static function lottery_complete($status, $log, $unschedule = false)
    {
        echo "Lottery complete, status: $status<br />\n";

        $email = "";

        // Output the logging info, transform for email
        foreach ($log as $line) {
            echo $line . "<br />\n";
            $email .= $line . "\n";
        }

        // TODO: unschedule from pulse here, if true

        HMS_Email::send_lottery_status_report($status, $email);
        exit();
    }

    /**
     * Retuns an array of lottery roommate invites
     */
    public static function get_lottery_roommate_invites($username, $term)
    {
        $db = new PHPWS_DB('hms_lottery_reservation');

        $db->addWhere('asu_username', $username);
        $db->addWhere('term', $term);
        $db->addWhere('expires_on', time(), '>'); // make sure the request hasn't expired

        $result = $db->select();

        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }

        return $result;
    }

    public static function get_lottery_roommate_invite_by_id($id)
    {
        if($id === null || $id === ''){
            throw new \InvalidArgumentException('Missing roommate invite id parameter');
        }

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT hms_lottery_reservation.*
                    FROM hms_lottery_reservation
                    WHERE
                        hms_lottery_reservation.expires_on > :expiresOn
                        AND hms_lottery_reservation.id = :id";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'id' => $id,
                'expiresOn' => time()
        ));

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }

        return $result;
    }

    public static function confirm_roommate_request($username, $requestId)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $term = PHPWS_Settings::get('hms', 'lottery_term');

        // Get the roommate invite
        $invite = HMS_Lottery::get_lottery_roommate_invite_by_id($requestId);

        // If the invite wasn't found, show an error
        if ($invite === false) {
            return E_LOTTERY_ROOMMATE_INVITE_NOT_FOUND;
        }

        // Check that the reserved bed is still empty
        $bed = new HMS_Bed($invite['bed_id']);
        if (!$bed->has_vacancy()) {
            return E_ASSIGN_BED_NOT_EMPTY;
        }

        // Make sure the student isn't assigned anywhere else
        if (HMS_Assignment::checkForAssignment($username, $term)) {
            return E_ASSIGN_ALREADY_ASSIGNED;
        }

        $student = StudentFactory::getStudentByUsername($username, $term);
        $requestor = StudentFactory::getStudentByUsername($invite['requestor'], $term);

        // Actually make the assignment
        HMS_Assignment::assignStudent($student, $term, null, $invite['bed_id'], 'Confirmed roommate invite', true, ASSIGN_LOTTERY);

        // return successfully
        HMS_Email::send_roommate_confirmation($student, $requestor);
        return E_SUCCESS;
    }

    public static function denyRoommateRequest($requestId)
    {
        // Delete the invite
        $db = new PHPWS_DB('hms_lottery_reservation');
        $db->addWhere('id', $requestId);
        $result = $db->delete();

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    /*
     * Returns true if the student is assigned in the current term
    * or if the student has an eligibility waiver.
    */
    public static function determineEligibility($username)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Eligibility_Waiver.php');

        // First, check for an assignment in the current term
        if (HMS_Assignment::checkForAssignment($username, Term::getCurrentTerm())) {
            return true;
            // If that didn't work, check for a waiver in the lottery term
        } elseif (HMS_Eligibility_Waiver::checkForWaiver($username, PHPWS_Settings::get('hms', 'lottery_term'))) {
            return true;
            // If that didn't work either, then the student is not elibible, so return false
        } else {
            return false;
        }
    }

    // Translates an application term into a class (fr, soph, etc) based on the term given
    public static function application_term_to_class($curr_term, $application_term)
    {
        // Break up the term and year
        $yr = floor($application_term / 100);
        $sem = $application_term - ($yr * 100);

        $curr_year = floor($curr_term / 100);
        $curr_sem = $curr_term - ($curr_year * 100);

        if ($curr_sem == 10) {
            $curr_year -= 1;
            $curr_sem = 40;
        }

        if (is_null($application_term) || !isset($application_term)) {
            // If there's no application term, just return null
            return null;
        } else if ($application_term >= $curr_term) {
            // The application term is greater than the current term, then they're certainly a freshmen
            return CLASS_FRESHMEN;
        } else if (($yr == $curr_year + 1 && $sem = 10) || ($yr == $curr_year && $sem >= 20 && $sem <= 40)) {
            // freshmen
            return CLASS_FRESHMEN;
        } else if (($yr == $curr_year && $sem == 10) || ($yr + 1 == $curr_year && $sem >= 20 && $sem <= 40)) {
            // soph
            return CLASS_SOPHOMORE;
        } else if (($yr + 1 == $curr_year && $sem == 10) || ($yr + 2 == $curr_year && $sem >= 20 && $sem <= 40)) {
            // jr
            return CLASS_JUNIOR;
        } else {
            // senior
            return CLASS_SENIOR;
        }
    }

    public static function getSpecialInterestGroupsMap()
    {
        $special_interests = array();

        $special_interests['none'] = 'None';
        $special_interests['honors'] = 'The Honors College';
        $special_interests['watauga_global'] = 'Watauga Global Community';
        $special_interests['teaching'] = 'Teaching Fellows';
        $special_interests['sorority_adp'] = 'Alpha Delta Pi Sorority';
        $special_interests['sorority_ap'] = 'Aplha Phi Sorority';
        $special_interests['sorority_co'] = 'Chi Omega Sorority';
        $special_interests['sorority_dz'] = 'Delta Zeta Sorority';
        $special_interests['sorority_kd'] = 'Kappa Delta Sorority';
        $special_interests['sorority_pm'] = 'Phi Mu Sorority';
        $special_interests['sorority_sk'] = 'Sigma Kappa Sorority';
        $special_interests['sorority_aop'] = 'Alpha Omicron Pi Sorority';
        $special_interests['sorority_zta'] = 'Zeta Tau Alpha';

        return $special_interests;
    }

    public static function getSororities()
    {
        $sororities = array();

        $sororities['sorority_adp'] = 'Alpha Delta Pi Sorority';
        $sororities['sorority_ap'] = 'Aplha Phi Sorority';
        $sororities['sorority_co'] = 'Chi Omega Sorority';
        $sororities['sorority_dz'] = 'Delta Zeta Sorority';
        $sororities['sorority_kd'] = 'Kappa Delta Sorority';
        $sororities['sorority_pm'] = 'Phi Mu Sorority';
        $sororities['sorority_sk'] = 'Sigma Kappa Sorority';
        $sororities['sorority_aop'] = 'Alpha Omicron Pi Sorority';
        $sororities['sorority_zta'] = 'Zeta Tau Alpha';

        return $sororities;
    }

    /**
     *
     * @deprecated
     *
     * @throws DatabaseException
     * @return unknown
     */
    public static function getSizeOfOnCampusWaitList()
    {
        $term = PHPWS_Settings::get('hms', 'lottery_term');

        // Get the list of user names still on the waiting list, sorted by ID (first come, first served)
        $sql = "SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_new_application.term = $term
                AND special_interest IS NULL
                AND waiting_list_hide = 0";

        $count = PHPWS_DB::getOne($sql);

        if (PHPWS_Error::logIfError($count)) {
            throw new DatabaseException($count->toString());
        }

        return $count;
    }
}
