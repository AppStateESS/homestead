<?php
namespace Homestead;

use \Homestead\Exception\DatabaseException;

class RoommateProfileFactory {
    /**
     * check_for_profile
     * Returns the id number of a profile, if it
     * exists for the given user name.
     * Returns false if no profile is found.
     *
     * @param $username string Student's Username
     * @param $term
     * @throws DatabaseException
     * @return integer boolean integer id of profile object, or false if no profile exists.
     */

    //public static function checkForProfile($username, $term)
    public static function checkForProfile($bannerId, $term)
    {
        $db = new \PHPWS_DB('hms_student_profiles');

        $db->addWhere('banner_id', $bannerId);
        $db->addWhere('term', $term);
        $result = $db->select('row');

        if (\PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if ($result != NULL && sizeof($result > 0)) {
            return $result['id'];
        } else {
            return false;
        }
    }

    /**
     * Returns a RoommateProfile object if one is found, false otherwise.
     *
     * @param String $username
     * @param integer $term
     * @throws DatabaseException
     * @return RoommateProfile NULL
     */

    //public static function getProfile($username, $term)
    public static function getProfile($bannerId, $term)
    {
        $profile = new RoommateProfile();

        $db = new \PHPWS_DB('hms_student_profiles');

        $db->addWhere('banner_id', $bannerId);
        $db->addWhere('term', $term);
        $result = $db->loadObject($profile);

        if (\PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        if (!is_null($profile->id)) {
            return $profile;
        } else {
            return NULL;
        }
    }

    /**
     * Get all profiles of the same gender, for the same semster, who are not assigned already,
     * and do not have roommate requests (pending or confiremd)
     */
    public static function getPotentialProfiles(Student $student, string $term)
    {
        $pdo = PdoFactory::getPdoInstance();

        $query = 'SELECT * FROM hms_student_profiles
                    WHERE
                        hms_student_profiles.term = :term AND
                        hms_student_profiles.banner_id != :bannerId AND
                        hms_student_profiles.gender = :gender AND
                        banner_id NOT IN (SELECT banner_id FROM hms_assignment WHERE term = :term) AND
                        username NOT IN (SELECT requestor FROM hms_roommate WHERE term = :term UNION SELECT requestee FROM hms_roommate WHERE term = :term)';
        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>$term, 'bannerId'=>$student->getBannerId(), 'gender'=>$student->getGender()));

        // TODO: Fetch as array??
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Homestead\RoommateProfileRestored');

        return $stmt->fetchAll();
    }

    /**
     * Function to determine which hobbies check boxes need to be checked
     * Takes a Student_Profile object and returns an array of the checkbox names
     * which should be checked.
     * (Used as input to the setMatch public function).
     *
     * @param RoommateProfile
     * @return Array
     */
    public static function get_hobbies_matches($profile)
    {
        $hobbies_matches = array();
        $m = new RoommateProfile;

        $hobbiesCount = count($m->hobbies_array);

        for ($x = 0; $x < $hobbiesCount; $x++)
        {
            if($profile->get_checked($m->hobbies_array[$x]))
            {
                $hobbies_matches[] = $m->hobbies_array[$x];
            }
        }
        return $hobbies_matches;
    }

    /**
     * Function to determine which music check boxes need to be checked
     * Takes a Student_Profile object and returns an array of the checkbox names
     * which should be checked.
     * (Used as input to the setMatch public function).
     *
     * @param RoommateProfile
     * @return Array
     */
    public static function get_music_matches($profile)
    {
        $music_matches = array();
        $m = new RoommateProfile;

        $musicCount = count($m->music_array);

        for ($x = 0; $x < $musicCount; $x++)
        {
            if($profile->get_checked($m->music_array[$x]))
            {
                $music_matches[] = $m->music_array[$x];
            }
        }
        return $music_matches;
    }

    /**
     * Returns study time matches
     *
     * @param RoommateProfile $profile
     * @return Array
     */
    public static function get_study_matches($profile)
    {
        $study_matches = array();
        $m = new RoommateProfile;

        $studyCount = count($m->study_array);

        for ($x = 0; $x < $studyCount; $x++)
        {
            if($profile->get_checked($m->study_array[$x]))
            {
                $study_matches[] = $m->study_array[$x];
            }
        }
        return $study_matches;
    }

    /**
     * Returns language matches
     *
     * @param RoommateProfile $profile
     * @return Array
     */
    public static function get_language_matches($profile)
    {
        $lang_match = array();
        $m = new RoommateProfile;

        $langCount = count($m->lang_array);

        for ($x = 0; $x < count($m->lang_array); $x ++)
        {
            if($profile->get_checked($m->lang_array[$x]))
            {
                $lang_match[] = $m->lang_array[$x];
            }
        }
        return $lang_match;
    }
}
