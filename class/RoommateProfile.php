<?php

/**
 * The HMS_Student_Profile class
 * Implements the Student_profile object and methods to load/save
 * student profiles from the database.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

/**
 * Includes the defines file used for the values of the fields
 * throughout this class.
 */

class RoommateProfile{

    public $id;

    public $username;
    public $date_submitted;
    public $term;

    # Alternate contact info
    public $alternate_email = NULL;
    public $aim_sn = NULL;
    public $yahoo_sn = NULL;
    public $msn_sn = NULL;

    # Hobby choices
    public $arts_and_crafts = 0;
    public $books_and_reading = 0;
    public $cars = 0;
    public $church_activities = 0;
    public $collecting = 0;
    public $computers_and_technology = 0;
    public $dancing = 0;
    public $fashion = 0;
    public $fine_arts = 0;
    public $gardening = 0;
    public $games = 0;
    public $humor = 0;
    public $investing_personal_finance = 0;
    public $movies = 0;
    public $music = 0;
    public $outdoor_activities = 0;
    public $pets_and_animals = 0;
    public $photography = 0;
    public $politics = 0;
    public $sports = 0;
    public $travel = 0;
    public $tv_shows = 0;
    public $volunteering = 0;
    public $writing = 0;
    public $rotc = 0;

    # music choices
    public $alternative = 0;
    public $ambient = 0;
    public $beach = 0;
    public $bluegrass = 0;
    public $blues = 0;
    public $christian = 0;
    public $classical = 0;
    public $classic_rock = 0;
    public $country = 0;
    public $electronic = 0;
    public $folk = 0;
    public $heavy_metal = 0;
    public $hip_hop = 0;
    public $house = 0;
    public $industrial = 0;
    public $jazz = 0;
    public $popular_music = 0;
    public $progressive = 0;
    public $punk = 0;
    public $r_and_b = 0;
    public $rap = 0;
    public $reggae = 0;
    public $rock = 0;
    public $world_music = 0;

    # Study times
    public $study_early_morning = 0;
    public $study_morning_afternoon = 0;
    public $study_afternoon_evening = 0;
    public $study_evening = 0;
    public $study_late_night = 0;

    # drop downs
    public $political_view = 0;
    public $major = 0;
    public $experience = 0;
    public $sleep_time = 0;
    public $wakeup_time = 0;
    public $overnight_guests = 0;
    public $loudness = 0;
    public $cleanliness = 0;
    public $free_time = 0;

    # Spoken languages
    # Top 20 most spoken languages: 
    #     http://en.wikipedia.org/wiki/Ethnologue_list_of_most_spoken_languages
    public $arabic = 0;
    public $bengali = 0;
    public $chinese = 0;
    public $english = 0;
    public $french = 0;
    public $german = 0;
    public $hindi = 0;
    public $italian = 0;
    public $japanese = 0;
    public $javanese = 0;
    public $korean = 0;
    public $malay = 0;
    public $marathi = 0;
    public $portuguese = 0;
    public $punjabi = 0;
    public $russian = 0;
    public $spanish = 0;
    public $tamil = 0;
    public $telugu = 0;
    public $vietnamese = 0;

    /**
     * Constructor
     * Optional parameter is a id number corresponding to database column 'id'
     */
    public function __construct($id = NULL)
    {
        if(!isset($id)){
            return;
        }

        $this->setID($id);

        # Initialize
        $result = $this->init();
    }

    public function init()
    {
        if(!isset($this->id)){
            return FALSE;
        }

        $db = new PHPWS_DB('hms_student_profiles');
        $result = $db->loadObject($this);
         
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_student_profiles');

        if($this->get_date_submitted() == NULL){
            $this->set_date_submitted();
        }
        
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_PROFILE_CREATED, UserStatus::getUsername(), '');

        return $result;
    }

    /****************
     * Static methods
     ****************/

    /**
     * check_for_profile
     * Returns the id number of a profile, if it
     * exists for the given user name.
     * Returns FALSE if no profile is found.
     */
    public static function checkForProfile($username, $term)
    {
        $db = new PHPWS_DB('hms_student_profiles');
         
        $db->addWhere('username',$username,'ILIKE');
        $db->addWhere('term', $term);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
         
        if($result != NULL && sizeof($result > 0)){
            return $result['id'];
        }else{
            return FALSE;
        }
    }

    public static function getProfile($username, $term)
    {
        $profile = new RoommateProfile();

        $db = new PHPWS_DB('hms_student_profiles');
         
        $db->addWhere('username',$username,'ILIKE');
        $db->addWhere('term', $term);
        $result = $db->loadObject($profile);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if(!is_null($profile->id)){
            return $profile;
        }else{
            return NULL;
        }
    }

    /*
     * Sets up the pager object for searching questionnairs.
     */
    public static function profile_search_pager()
    {
        # get the current student's gender
        PHPWS_Core::initModClass('hms','HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $student->getApplicationTerm());

        $gender = $student->getGender();

        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['USERNAME']   = _('Email');
        $pageTags['FIRST_NAME'] = _('First Name');
        $pageTags['LAST_NAME']  = _('Last Name');
        $pageTags['ACTIONS']    = _('Action');

        $pager = new DBPager('hms_student_profiles','RoommateProfile');

        // Check to see if user is assigned to an RLC
        $rlc_assignment = HMS_RLC_Assignment::checkForAssignment($student->getUsername(), $student->getApplicationTerm());
        if($rlc_assignment != FALSE){
            // User is assigned to an RLC, only show results from other students in the same RLC
            $pager->db->addJoin('LEFT OUTER', 'hms_student_profiles', 'hms_learning_community_applications', 'username', 'user_id');
            $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_applications', 'hms_learning_community_assignment', 'hms_assignment_id', 'id');
            $pager->db->addWhere('hms_learning_community_assignment.rlc_id', $rlc_assignment['rlc_id']);
            //$pager->db->setTestMode();
        }

        # If an ASU username was entered, just use that. Otherwise, use the rest of the fields.
        if(isset($_REQUEST['asu_username']) && $_REQUEST['asu_username'] != ''){
            $pager->addWhere('hms_student_profiles.username',$_REQUEST['asu_username'],'ILIKE');
            $_SESSION['profile_search_asu_username'] = $_REQUEST['asu_username'];
        }else{

            if(isset($_REQUEST['hobbies_checkbox']['arts_and_crafts'])){
                $pager->addWhere('hms_student_profiles.arts_and_crafts',1,'=');
                $_SESSION['hobbies_checkbox']['arts_and_crafts'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['books_and_reading'])){
                $pager->addWhere('hms_student_profiles.books_and_reading',1,'=');
                $_SESSION['hobbies_checkbox']['books_and_reading'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['cars'])){
                $pager->addWhere('hms_student_profiles.cars',1,'=');
                $_SESSION['hobbies_checkbox']['cars'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['church_activities'])){
                $pager->addWhere('hms_student_profiles.church_activities',1,'=');
                $_SESSION['hobbies_checkbox']['chrch_activities'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['collecting'])){
                $pager->addWhere('hms_student_profiles.collecting',1,'=');
                $_SESSION['hobbies_checkbox']['collecting'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['computers_and_technology'])){
                $pager->addWhere('hms_student_profiles.computers_and_technology',1,'=');
                $_SESSION['hobbies_checkbox']['computers_and_technology'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['dancing'])){
                $pager->addWhere('hms_student_profiles.dancing',1,'=');
                $_SESSION['hobbies_checkbox']['dancing'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['fashion'])){
                $pager->addWhere('hms_student_profiles.fashion',1,'=');
                $_SESSION['hobbies_checkbox']['fashion'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['fine_arts'])){
                $pager->addWhere('hms_student_profiles.fine_arts',1,'=');
                $_SESSION['hobbies_checkbox']['fine_arts'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['gardening'])){
                $pager->addWhere('hms_student_profiles.gardening',1,'=');
                $_SESSION['hobbies_checkbox']['gardening'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['games'])){
                $pager->addWhere('hms_student_profiles.games',1,'=');
                $_SESSION['hobbies_checkbox']['games'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['humor'])){
                $pager->addWhere('hms_student_profiles.humor',1,'=');
                $_SESSION['hobbies_checkbox']['humor'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['investing_personal_finance'])){
                $pager->addWhere('hms_student_profiles.investing_personal_finance',1,'=');
                $_SESSION['hobbies_checkbox']['intesting_personal_finance'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['movies'])){
                $pager->addWhere('hms_student_profiles.movies',1,'=');
                $_SESSION['hobbies_checkbox']['movies'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['music'])){
                $pager->addWhere('hms_student_profiles.music',1,'=');
                $_SESSION['hobbies_checkbox']['music'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['outdoor_activities'])){
                $pager->addWhere('hms_student_profiles.outdoor_activities',1,'=');
                $_SESSION['hobbies_checkbox']['outdoor_activities'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['pets_and_animals'])){
                $pager->addWhere('hms_student_profiles.pets_and_animals',1,'=');
                $_SESSION['hobbies_checkbox']['pets_and_animals'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['photography'])){
                $pager->addWhere('hms_student_profiles.photography',1,'=');
                $_SESSION['hobbies_checkbox']['photography'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['politics'])){
                $pager->addWhere('hms_student_profiles.politics',1,'=');
                $_SESSION['hobbies_checkbox']['politics'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['sports'])){
                $pager->addWhere('hms_student_profiles.sports',1,'=');
                $_SESSION['hobbies_checkbox']['sports'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['travel'])){
                $pager->addWhere('hms_student_profiles.travel',1,'=');
                $_SESSION['hobbies_checkbox']['travel'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['tv_shows'])){
                $pager->addWhere('hms_student_profiles.tv_shows',1,'=');
                $_SESSION['hobbies_checkbox']['tv_shows'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['volunteering'])){
                $pager->addWhere('hms_student_profiles.volunteering',1,'=');
                $_SESSION['hobbies_checkbox']['volunteering'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['writing'])){
                $pager->addWhere('hms_student_profiles.writing',1,'=');
                $_SESSION['hobbies_checkbox']['writing'] = 1;
            }

            if(isset($_REQUEST['hobbies_checkbox']['rotc'])){
                $pager->addWhere('hms_student_profiles.rotc',1,'=');
                $_SESSION['hobbies_checkbox']['rotc'] = 1;
            }

            # Music check boxes
            if(isset($_REQUEST['music_checkbox']['alternative'])){
                $pager->addWhere('hms_student_profiles.alternative',1,'=');
                $_SESSION['hobbies_checkbox']['alternative'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['ambient'])){
                $pager->addWhere('hms_student_profiles.ambient',1,'=');
                $_SESSION['hobbies_checkbox']['ambient'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['beach'])){
                $pager->addWhere('hms_student_profiles.beach',1,'=');
                $_SESSION['hobbies_checkbox']['beach'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['bluegrass'])){
                $pager->addWhere('hms_student_profiles.bluegrass',1,'=');
                $_SESSION['hobbies_checkbox']['bluegrass'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['blues'])){
                $pager->addWhere('hms_student_profiles.blues',1,'=');
                $_SESSION['hobbies_checkbox']['blues'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['christian'])){
                $pager->addWhere('hms_student_profiles.christian',1,'=');
                $_SESSION['hobbies_checkbox']['christian'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['classical'])){
                $pager->addWhere('hms_student_profiles.classical',1,'=');
                $_SESSION['hobbies_checkbox']['classical'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['classic_rock'])){
                $pager->addWhere('hms_student_profiles.classic_rock',1,'=');
                $_SESSION['hobbies_checkbox']['classic_rock'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['country'])){
                $pager->addWhere('hms_student_profiles.country',1,'=');
                $_SESSION['hobbies_checkbox']['country'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['electronic'])){
                $pager->addWhere('hms_student_profiles.electronic',1,'=');
                $_SESSION['hobbies_checkbox']['electronic'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['folk'])){
                $pager->addWhere('hms_student_profiles.folk',1,'=');
                $_SESSION['hobbies_checkbox']['folk'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['heavy_metal'])){
                $pager->addWhere('hms_student_profiles.heavy_metal',1,'=');
                $_SESSION['hobbies_checkbox']['heavy_metal'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['hip_hop'])){
                $pager->addWhere('hms_student_profiles.hip_hop',1,'=');
                $_SESSION['hobbies_checkbox']['hip_hop'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['house'])){
                $pager->addWhere('hms_student_profiles.house',1,'=');
                $_SESSION['hobbies_checkbox']['house'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['industrial'])){
                $_SESSION['hobbies_checkbox']['industrial'] = 1;
                $pager->addWhere('hms_student_profiles.industrial',1,'=');
            }

            if(isset($_REQUEST['music_checkbox']['jazz'])){
                $_SESSION['hobbies_checkbox']['jazz'] = 1;
                $pager->addWhere('hms_student_profiles.jazz',1,'=');
            }

            if(isset($_REQUEST['music_checkbox']['popular_music'])){
                $_SESSION['hobbies_checkbox']['popular_music'] = 1;
                $pager->addWhere('hms_student_profiles.popular_music',1,'=');
            }

            if(isset($_REQUEST['music_checkbox']['progressive'])){
                $_SESSION['hobbies_checkbox']['progressive'] = 1;
                $pager->addWhere('hms_student_profiles.progressive',1,'=');
            }

            if(isset($_REQUEST['music_checkbox']['punk'])){
                $_SESSION['hobbies_checkbox']['punk'] = 1;
                $pager->addWhere('hms_student_profiles.punk',1,'=');
            }

            if(isset($_REQUEST['music_checkbox']['r_and_b'])){
                $_SESSION['hobbies_checkbox']['r_and_b'] = 1;
                $pager->addWhere('hms_student_profiles.r_and_b',1,'=');
            }

            if(isset($_REQUEST['music_checkbox']['rap'])){
                $_SESSION['hobbies_checkbox']['rap'] = 1;
                $pager->addWhere('hms_student_profiles.rap',1,'=');
            }

            if(isset($_REQUEST['music_checkbox']['reggae'])){
                $pager->addWhere('hms_student_profiles.reggae',1,'=');
                $_SESSION['hobbies_checkbox']['reggae'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['alternative'])){
                $pager->addWhere('hms_student_profiles.rock',1,'=');
                $_SESSION['hobbies_checkbox']['alternative'] = 1;
            }

            if(isset($_REQUEST['music_checkbox']['world_music'])){
                $pager->addWhere('hms_student_profiles.world_music',1,'=');
                $_SESSION['hobbies_checkbox']['world_music'] = 1;
            }

            # Study times
            if(isset($_REQUEST['study_times']['study_early_morning'])){
                $pager->addWhere('hms_student_profiles.study_early_morning',1,'=');
                $_SESSION['study_times']['study_early_morning'] = 1;
            }

            if(isset($_REQUEST['study_times']['study_morning_afternoon'])){
                $pager->addWhere('hms_student_profiles.study_morning_afternoon',1,'=');
                $_SESSION['study_times']['study_morning_afternoon'] = 1;
            }

            if(isset($_REQUEST['study_times']['study_afternoon_evening'])){
                $pager->addWhere('hms_student_profiles.study_afternoon_evening',1,'=');
                $_SESSION['study_times']['study_afternoon_evening'] = 1;
            }

            if(isset($_REQUEST['study_times']['study_evening'])){
                $pager->addWhere('hms_student_profiles.study_evening',1,'=');
                $_SESSION['study_times']['study_evening'] = 1;
            }

            if(isset($_REQUEST['study_times']['study_late_night'])){
                $pager->addWhere('hms_student_profiles.study_late_night',1,'=');
                $_SESSION['study_times']['study_late_night'] = 1;
            }

            # Drop downs
            if(isset($_REQUEST['political_views_dropbox']) && $_REQUEST['political_views_dropbox'] != 0){
                $pager->addWhere('hms_student_profiles.political_view',$_REQUEST['political_views_dropbox'],'=');
                $_SESSION['political_views_dropbox'] = $_REQUEST['political_views_dropbox'];
            }

            if(isset($_REQUEST['intended_major']) && $_REQUEST['intended_major'] != 0){
                $pager->addWhere('hms_student_profiles.major',$_REQUEST['intended_major'],'=');
                $_SESSION['intended_major'] = $_REQUEST['intended_major'];
            }

            if(isset($_REQUEST['important_experience']) && $_REQUEST['important_experience'] != 0){
                $pager->addWhere('hms_student_profiles.experience',$_REQUEST['important_experience'],'=');
                $_SESSION['important_experience'] = $_REQUEST['important_experience'];
            }

            if(isset($_REQUEST['sleep_time']) && $_REQUEST['sleep_time'] != 0){
                $pager->addWhere('hms_student_profiles.sleep_time',$_REQUEST['sleep_time'],'=');
                $_SESSION['sleep_time'] = $_REQUEST['sleep_time'];
            }

            if(isset($_REQUEST['wakeup_time']) && $_REQUEST['wakeup_time'] != 0){
                $pager->addWhere('hms_student_profiles.wakeup_time',$_REQUEST['wakeup_time'],'=');
                $_SESSION['wakeup_time'] = $_REQUEST['wakeup_time'];
            }

            if(isset($_REQUEST['overnight_guests']) && $_REQUEST['overnight_guests'] != 0){
                $pager->addWhere('hms_student_profiles.overnight_guests',$_REQUEST['overnight_guests'],'=');
                $_SESSION['overnight_guests'] = $_REQUEST['overnight_guests'];
            }

            if(isset($_REQUEST['loudness']) && $_REQUEST['loudness'] != 0){
                $pager->addWhere('hms_student_profiles.loudness',$_REQUEST['loudness'],'=');
                $_SESSION['loudness'] = $_REQUEST['loudness'];
            }

            if(isset($_REQUEST['cleanliness']) && $_REQUEST['cleanliness'] != 0){
                $pager->addWhere('hms_student_profiles.cleanliness',$_REQUEST['cleanliness'],'=');
                $_SESSION['cleanliness'] = $_REQUEST['cleanliness'];
            }

            if(isset($_REQUEST['free_time']) && $_REQUEST['free_time'] != 0){
                $pager->addWhere('hms_student_profiles.free_time',$_REQUEST['free_time'],'=');
                $_SESSION['free_time'] = $_REQUEST['free_time'];
            }

            # Spoken Languages
            if(isset($_REQUEST['language_checkbox']['arabic'])){
                $pager->addWhere('hms_student_profiles.arabic', 1, '=');
                $_SESSION['language_checkbox']['arabic'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['bengali'])){
                $pager->addWhere('hms_student_profiles.bengali', 1, '=');
                $_SESSION['language_checkbox']['bengali'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['english'])){
                $pager->addWhere('hms_student_profiles.english', 1, '=');
                $_SESSION['language_checkbox']['english'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['french'])){
                $pager->addWhere('hms_student_profiles.french', 1, '=');
                $_SESSION['language_checkbox']['french'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['german'])){
                $pager->addWhere('hms_student_profiles.german', 1, '=');
                $_SESSION['language_checkbox']['german'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['hindi'])){
                $pager->addWhere('hms_student_profiles.hindi', 1, '=');
                $_SESSION['language_checkbox']['hindi'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['italian'])){
                $pager->addWhere('hms_student_profiles.italian', 1, '=');
                $_SESSION['language_checkbox']['italian'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['japanese'])){
                $pager->addWhere('hms_student_profiles.japanese', 1, '=');
                $_SESSION['language_checkbox']['japanese'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['javanese'])){
                $pager->addWhere('hms_student_profiles.javanese', 1, '=');
                $_SESSION['language_checkbox']['javanese'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['korean'])){
                $pager->addWhere('hms_student_profiles.korean', 1, '=');
                $_SESSION['language_checkbox']['korean'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['malay'])){
                $pager->addWhere('hms_student_profiles.malay', 1, '=');
                $_SESSION['language_checkbox']['malay'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['marathi'])){
                $pager->addWhere('hms_student_profiles.marathi', 1, '=');
                $_SESSION['language_checkbox']['marathi'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['portuguese'])){
                $pager->addWhere('hms_student_profiles.portuguese', 1, '=');
                $_SESSION['language_checkbox']['portuguese'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['punjabi'])){
                $pager->addWhere('hms_student_profiles.punjabi', 1, '=');
                $_SESSION['language_checkbox']['punjabi'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['russian'])){
                $pager->addWhere('hms_student_profiles.russian', 1, '=');
                $_SESSION['language_checkbox']['russian'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['spanish'])){
                $pager->addWhere('hms_student_profiles.spanish', 1, '=');
                $_SESSION['language_checkbox']['spanish'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['tamil'])){
                $pager->addWhere('hms_student_profiles.tamil', 1, '=');
                $_SESSION['language_checkbox']['tamil'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['telugu'])){
                $pager->addWhere('hms_student_profiles.telugu', 1, '=');
                $_SESSION['language_checkbox']['telugu'] = 1;
            }

            if(isset($_REQUEST['language_checkbox']['vietnamese'])){
                $pager->addWhere('hms_student_profiles.vietnamese', 1, '=');
                $_SESSION['language_checkbox']['vietnamese'] = 1;
            }
        }

        # Join with hms_application table on username to make sure genders match.
        $pager->db->addJoin('LEFT OUTER', 'hms_student_profiles', 'hms_new_application', 'username', 'username');
        //$pager->addWhere('hms_student_profiles.user_id','hms_application.asu_username','ILIKE');
        $pager->addWhere('hms_new_application.gender',$gender,'=');

        # Don't list the current user as a match
        $pager->addWhere('hms_student_profiles.username',UserStatus::getUsername(),'NOT LIKE');

        $pager->db->addOrder('username','ASC');

        $pager->setModule('hms');
        $pager->setTemplate('student/profile_search_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No matches found. Try broadening your search by selecting fewer criteria.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);

        return $pager->get();
    }

    /*
     *Sets up the row tags for the pager
     */
    public function getPagerTags()
    {

        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $tags['STUDENT_ID'] = $student->getUsername() . "@appstate.edu";
        $tags['FIRST_NAME'] = $student->getFirstName();
        $tags['LAST_NAME'] = $student->getLastName();

        $viewProfileCmd = CommandFactory::getCommand('ShowRoommateProfile');
        $viewProfileCmd->setUsername($student->getUsername());
        $viewProfileCmd->setTerm($this->term);

        $tags['ACTIONS'] = $viewProfileCmd->getLink('[View Profile]');

        return $tags;
    }

    /**
     * Function to determine which hobbies check boxes need to be checked
     * Takes a Student_Profile object and returns an array of the checkbox names
     * which should be checked. (Used as input to the setMatch public function).
     */
    public static function get_hobbies_matches($profile)
    {
        $hobbies_matches = array();

        if($profile->get_arts_and_crafts()){
            $hobbies_matches[] = 'arts_and_crafts';
        }

        if($profile->get_books_and_reading()){
            $hobbies_matches[] = 'books_and_reading';
        }

        if($profile->get_cars()){
            $hobbies_matches[] = 'cars';
        }

        if($profile->get_church_activities()){
            $hobbies_matches[] = 'church_activities';
        }

        if($profile->get_collecting()){
            $hobbies_matches[] = 'collecting';
        }

        if($profile->get_computers_and_technology()){
            $hobbies_matches[] = 'computers_and_technology';
        }

        if($profile->get_dancing()){
            $hobbies_matches[] = 'dancing';
        }

        if($profile->get_fashion()){
            $hobbies_matches[] = 'fashion';
        }

        if($profile->get_fine_arts()){
            $hobbies_matches[] = 'fine_arts';
        }

        if($profile->get_gardening()){
            $hobbies_matches[] = 'gardening';
        }

        if($profile->get_games()){
            $hobbies_matches[] = 'games';
        }

        if($profile->get_humor()){
            $hobbies_matches[] = 'humor';
        }

        if($profile->get_investing_personal_finance()){
            $hobbies_matches[] = 'investing_personal_finance';
        }

        if($profile->get_movies()){
            $hobbies_matches[] = 'movies';
        }

        if($profile->get_music()){
            $hobbies_matches[] = 'music';
        }

        if($profile->get_outdoor_activities()){
            $hobbies_matches[] = 'outdoor_activities';
        }

        if($profile->get_pets_and_animals()){
            $hobbies_matches[] = 'pets_and_animals';
        }

        if($profile->get_photography()){
            $hobbies_matches[] = 'photography';
        }

        if($profile->get_politics()){
            $hobbies_matches[] = 'politics';
        }

        if($profile->get_sports()){
            $hobbies_matches[] = 'sports';
        }

        if($profile->get_travel()){
            $hobbies_matches[] = 'travel';
        }

        if($profile->get_tv_shows()){
            $hobbies_matches[] = 'tv_shows';
        }

        if($profile->get_volunteering()){
            $hobbies_matches[] = 'volunteering';
        }

        if($profile->get_writing()){
            $hobbies_matches[] = 'writing';
        }
        
        if($profile->get_rotc()){
            $hobbies_matches[] = 'rotc';
        }

        return $hobbies_matches;
    }

    /**
     * Function to determine which music check boxes need to be checked
     * Takes a Student_Profile object and returns an array of the checkbox names
     * which should be checked. (Used as input to the setMatch public function).
     */
    public static function get_music_matches($profile)
    {
        $music_matches = array();

        if($profile->get_alternative()){
            $music_matches[] = 'alternative';
        }

        if($profile->get_ambient()){
            $music_matches[] = 'ambient';
        }

        if($profile->get_beach()){
            $music_matches[] = 'beach';
        }

        if($profile->get_bluegrass()){
            $music_matches[] = 'bluegrass';
        }

        if($profile->get_blues()){
            $music_matches[] = 'blues';
        }

        if($profile->get_christian()){
            $music_matches[] = 'christian';
        }

        if($profile->get_classical()){
            $music_matches[] = 'classical';
        }

        if($profile->get_classic_rock()){
            $music_matches[] = 'classic_rock';
        }

        if($profile->get_country()){
            $music_matches[] = 'country';
        }

        if($profile->get_electronic()){
            $music_matches[] = 'electronic';
        }

        if($profile->get_folk()){
            $music_matches[] = 'folk';
        }

        if($profile->get_heavy_metal()){
            $music_matches[] = 'heavy_metal';
        }

        if($profile->get_hip_hop()){
            $music_matches[] = 'hip_hop';
        }

        if($profile->get_house()){
            $music_matches[] = 'house';
        }

        if($profile->get_industrial()){
            $music_matches[] = 'industrial';
        }

        if($profile->get_jazz()){
            $music_matches[] = 'jazz';
        }

        if($profile->get_popular_music()){
            $music_matches[] = 'popular_music';
        }

        if($profile->get_progressive()){
            $music_matches[] = 'progressive';
        }

        if($profile->get_punk()){
            $music_matches[] = 'punk';
        }

        if($profile->get_r_and_b()){
            $music_matches[] = 'r_and_b';
        }

        if($profile->get_rap()){
            $music_matches[] = 'rap';
        }

        if($profile->get_reggae()){
            $music_matches[] = 'reggae';
        }

        if($profile->get_rock()){
            $music_matches[] = 'rock';
        }

        if($profile->get_world_music()){
            $music_matches[] = 'world_music';
        }

        return $music_matches;
    }

    public static function get_study_matches($profile)
    {
        $study_matches = array();

        if($profile->get_study_early_morning()){
            $study_matches[] = 'study_early_morning';
        }

        if($profile->get_study_morning_afternoon()){
            $study_matches[] = 'study_morning_afternoon';
        }

        if($profile->get_study_afternoon_evening()){
            $study_matches[] = 'study_afternoon_evening';
        }

        if($profile->get_study_evening()){
            $study_matches[] = 'study_evening';
        }

        if($profile->get_study_late_night()){
            $study_matches[] = 'study_late_night';
        }

        return $study_matches;
    }

    public static function get_language_matches($profile)
    {
        $lang_match = array();
        
        if($profile->get_arabic()){
            $lang_match[] = 'arabic';
        }
        if($profile->get_bengali()){
            $lang_match[] = 'bengali';
        }
        if($profile->get_chinese()){
            $lang_match[] = 'chinese';
        }
        if($profile->get_english()){
            $lang_match[] = 'english';
        }
        if($profile->get_french()){
            $lang_match[] = 'french';
        }
        if($profile->get_german()){
            $lang_match[] = 'german';
        }
        if($profile->get_hindi()){
            $lang_match[] = 'hindi';
        }
        if($profile->get_italian()){
            $lang_match[] = 'italian';
        }
        if($profile->get_japanese()){
            $lang_match[] = 'japanese';
        }
        if($profile->get_javanese()){
            $lang_match[] = 'javanese';
        }
        if($profile->get_korean()){
            $lang_match[] = 'korean';
        }
        if($profile->get_malay()){
            $lang_match[] = 'malay';
        }
        if($profile->get_marathi()){
            $lang_match[] = 'marathi';
        }
        if($profile->get_portuguese()){
            $lang_match[] = 'portuguese';
        }
        if($profile->get_punjabi()){
            $lang_match[] = 'punjabi';
        }
        if($profile->get_russian()){
            $lang_match[] = 'russian';
        }
        if($profile->get_spanish()){
            $lang_match[] = 'spanish';
        }
        if($profile->get_tamil()){
            $lang_match[] = 'tamil';
        }
        if($profile->get_telugu()){
            $lang_match[] = 'telugu';
        }
        if($profile->get_vietnamese()){
            $lang_match[] = 'vietnamese';
        }

        return $lang_match;
    }


    /**
     * Accessor / Mutator Methods
     */

    public function setID($id){
        $this->id = $id;
    }

    public function getID(){
        return $this->id;
    }

    public function setUsername($name){
        $this->username = $name;
    }

    public function getUsername(){
        return $this->username;
    }

    public function set_date_submitted($date = NULL){
        if(isset($date)){
            $this->date_submitted = $date;
        }else{
            $this->date_submitted = mktime();
        }
    }

    public function get_date_submitted(){
        return $this->date_submitted;
    }

    public function setTerm($term){
        $this->term = $term;
    }

    public function getTerm(){
        return $this->term;
    }

    public function set_alternate_email($mail){
        $this->alternate_email = $mail;
    }

    public function get_alternate_email(){
        return $this->alternate_email;
    }

    public function set_aim_sn($sn){
        $this->aim_sn = $sn;
    }

    public function get_aim_sn(){
        return $this->aim_sn;
    }

    public function set_yahoo_sn($sn){
        $this->yahoo_sn = $sn;
    }

    public function get_yahoo_sn(){
        return $this->yahoo_sn;
    }

    public function set_msn_sn($sn){
        $this->msn_sn = $sn;
    }

    public function get_msn_sn(){
        return $this->msn_sn;
    }

    public function set_political_view($view){
        $this->political_view = $view;
    }

    public function get_political_view(){
        return $this->political_view;
    }

    public function set_major($major){
        $this->major = $major;
    }

    public function get_major(){
        return $this->major;
    }

    public function set_experience($exp){
        $this->experience = $exp;
    }

    public function get_experience(){
        return $this->experience;
    }

    public function set_sleep_time($time){
        $this->sleep_time = $time;
    }

    public function get_sleep_time(){
        return $this->sleep_time;
    }

    public function set_wakeup_time($time){
        $this->wakeup_time = $time;
    }

    public function get_wakeup_time(){
        return $this->wakeup_time;
    }

    public function set_overnight_guests($guests){
        $this->overnight_guests = $guests;
    }

    public function get_overnight_guests(){
        return $this->overnight_guests;
    }

    public function set_loudness($loudness){
        $this->loudness = $loudness;
    }

    public function get_loudness(){
        return $this->loudness;
    }

    public function set_cleanliness($clean){
        $this->cleanliness = $clean;
    }

    public function get_cleanliness(){
        return $this->cleanliness;
    }

    public function set_free_time($time){
        $this->free_time = $time;
    }

    public function get_free_time(){
        return $this->free_time;
    }

    /**
     * Hobbies check boxes
     */

    public function set_arts_and_crafts($value = 1){
        $this->arts_and_crafts = $value;
    }

    public function get_arts_and_crafts(){
        if($this->arts_and_crafts == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_books_and_reading($value = 1){
        $this->books_and_reading = $value;
    }

    public function get_books_and_reading(){
        if($this->books_and_reading == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_cars($value = 1){
        $this->cars = $value;
    }

    public function get_cars(){
        if($this->cars == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_church_activities($value = 1){
        $this->church_activities = $value;
    }

    public function get_church_activities(){
        if($this->church_activities == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_collecting($value = 1){
        $this->collecting = $value;
    }

    public function get_collecting(){
        if($this->collecting == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_computers_and_technology($value = 1){
        $this->computers_and_technology = $value;
    }

    public function get_computers_and_technology(){
        if($this->computers_and_technology == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_dancing($value = 1){
        $this->dancing = $value;
    }

    public function get_dancing(){
        if($this->dancing == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_fashion($value = 1){
        $this->fashion = $value;
    }

    public function get_fashion(){
        if($this->fashion == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_fine_arts($value = 1){
        $this->fine_arts = $value;
    }

    public function get_fine_arts(){
        if($this->fine_arts == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_gardening($value = 1){
        $this->gardening = $value;
    }

    public function get_gardening(){
        if($this->gardening == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_games($value = 1){
        $this->games = $value;
    }

    public function get_games(){
        if($this->games == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_humor($value = 1){
        $this->humor = $value;
    }

    public function get_humor(){
        if($this->humor == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_investing_personal_finance($value = 1){
        $this->investing_personal_finance = $value;
    }

    public function get_investing_personal_finance(){
        if($this->investing_personal_finance == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_movies($value = 1){
        $this->movies = $value;
    }

    public function get_movies(){
        if($this->movies == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_music($value = 1){
        $this->music = $value;
    }

    public function get_music(){
        if($this->music == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_outdoor_activities($value = 1){
        $this->outdoor_activities = $value;
    }

    public function get_outdoor_activities(){
        if($this->outdoor_activities == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_pets_and_animals($value = 1){
        $this->pets_and_animals = $value;
    }

    public function get_pets_and_animals(){
        if($this->pets_and_animals == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_photography($value = 1){
        $this->photography = $value;
    }

    public function get_photography(){
        if($this->photography == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_politics($value = 1){
        $this->politics = $value;
    }

    public function get_politics(){
        if($this->politics == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_sports($value = 1){
        $this->sports = $value;
    }

    public function get_sports(){
        if($this->sports == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_travel($value = 1){
        $this->travel = $value;
    }

    public function get_travel(){
        if($this->travel == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_tv_shows($value = 1){
        $this->tv_shows = $value;
    }

    public function get_tv_shows(){
        if($this->tv_shows == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_volunteering($value = 1){
        $this->volunteering = $value;
    }

    public function get_volunteering(){
        if($this->volunteering == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_writing($value = 1){
        $this->writing = $value;
    }

    public function get_writing(){
        if($this->writing == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    public function set_rotc($value = 1){
        $this->rotc = $value;
    }
    
    public function get_rotc(){
        if($this->rotc == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Music check boxes
     */

    public function set_alternative($value = 1){
        $this->alternative = $value;
    }

    public function get_alternative(){
        if($this->alternative == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_ambient($value = 1){
        $this->ambient = $value;
    }

    public function get_ambient(){
        if($this->ambient == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_beach($value = 1){
        $this->beach = $value;
    }

    public function get_beach(){
        if($this->beach == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_bluegrass($value = 1){
        $this->bluegrass = $value;
    }

    public function get_bluegrass(){
        if($this->bluegrass == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_blues($value = 1){
        $this->blues = $value;
    }

    public function get_blues(){
        if($this->blues == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_christian($value = 1){
        $this->christian = $value;
    }

    public function get_christian(){
        if($this->christian == 1){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function set_classical($value = 1){
        $this->classical = $value;
    }

    public function get_classical(){
        if($this->classical == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_classic_rock($value = 1){
        $this->classic_rock = $value;
    }

    public function get_classic_rock(){
        if($this->classic_rock == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_country($value = 1){
        $this->country = $value;
    }

    public function get_country(){
        if($this->country == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_electronic($value = 1){
        $this->electronic = $value;
    }

    public function get_electronic(){
        if($this->electronic == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_folk($value = 1){
        $this->folk = $value;
    }

    public function get_folk(){
        if($this->folk == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_heavy_metal($value = 1){
        $this->heavy_metal = $value;
    }

    public function get_heavy_metal(){
        if($this->heavy_metal == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_hip_hop($value = 1){
        $this->hip_hop = $value;
    }

    public function get_hip_hop(){
        if($this->hip_hop == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_house($value = 1){
        $this->house = $value;
    }

    public function get_house(){
        if($this->house == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_industrial($value = 1){
        $this->industrial = $value;
    }

    public function get_industrial(){
        if($this->industrial == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_jazz($value = 1){
        $this->jazz = $value;
    }

    public function get_jazz(){
        if($this->jazz == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_popular_music($value = 1){
        $this->popular_music = $value;
    }

    public function get_popular_music(){
        if($this->popular_music == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_progressive($value = 1){
        $this->progressive = $value;
    }

    public function get_progressive(){
        if($this->progressive == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_punk($value = 1){
        $this->punk = $value;
    }

    public function get_punk(){
        if($this->punk == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_r_and_b($value = 1){
        $this->r_and_b = $value;
    }

    public function get_r_and_b(){
        if($this->r_and_b == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_rap($value = 1){
        $this->rap = $value;
    }

    public function get_rap(){
        if($this->rap == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_reggae($value = 1){
        $this->reggae = $value;
    }

    public function get_reggae(){
        if($this->reggae == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_rock($value = 1){
        $this->rock = $value;
    }

    public function get_rock(){
        if($this->rock == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_world_music($value = 1){
        $this->world_music = $value;
    }

    public function get_world_music(){
        if($this->world_music == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Study times check boxes
     */
    public function set_study_early_morning($value = 1){
        $this->study_early_morning = $value;
    }

    public function get_study_early_morning(){
        if($this->study_early_morning == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_study_morning_afternoon($value = 1){
        $this->study_morning_afternoon = $value;
    }

    public function get_study_morning_afternoon(){
        if($this->study_morning_afternoon == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_study_afternoon_evening($value = 1){
        $this->study_afternoon_evening = $value;
    }

    public function get_study_afternoon_evening(){
        if($this->study_afternoon_evening == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_study_evening($value = 1){
        $this->study_evening = $value;
    }

    public function get_study_evening(){
        if($this->study_evening == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function set_study_late_night($value = 1){
        $this->study_late_night = $value;
    }

    public function get_study_late_night(){
        if($this->study_late_night == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    # Spoken Languages
    public function get_arabic(){
        return $this->arabic == 1 ? TRUE : FALSE;
    }
    public function get_bengali(){
        return $this->bengali == 1 ? TRUE : FALSE;
    }
    public function get_chinese(){
        return $this->chinese == 1 ? TRUE : FALSE;
    }
    public function get_english(){
        return $this->english == 1 ? TRUE : FALSE;
    }
    public function get_french(){
        return $this->french == 1 ? TRUE : FALSE;
    }
    public function get_german(){
        return $this->german == 1 ? TRUE : FALSE;
    }
    public function get_hindi(){
        return $this->hindi == 1 ? TRUE : FALSE;
    }
    public function get_italian(){
        return $this->italian == 1 ? TRUE : FALSE;
    }
    public function get_japanese(){
        return $this->japanese == 1 ? TRUE : FALSE;
    }
    public function get_javanese(){
        return $this->javanese == 1 ? TRUE : FALSE;
    }
    public function get_korean(){
        return $this->korean == 1 ? TRUE : FALSE;
    }
    public function get_malay(){
        return $this->malay == 1 ? TRUE : FALSE;
    }
    public function get_marathi(){
        return $this->marathi == 1 ? TRUE : FALSE;
    }
    public function get_portuguese(){
        return $this->portuguese == 1 ? TRUE : FALSE;
    }
    public function get_punjabi(){
        return $this->punjabi == 1 ? TRUE : FALSE;
    }
    public function get_russian(){
        return $this->russian == 1 ? TRUE : FALSE;
    }
    public function get_spanish(){
        return $this->spanish == 1 ? TRUE : FALSE;
    }
    public function get_tamil(){
        return $this->tamil == 1 ? TRUE : FALSE;
    }
    public function get_telugu(){
        return $this->telugu == 1 ? TRUE : FALSE;
    }
    public function get_vietnamese(){
        return $this->vietnamese == 1 ? TRUE : FALSE;
    }
    
    public function set_arabic($value=1){
        $this->arabic = $value;
    }
    public function set_bengali($value=1){
        $this->bengali = $value;
    }
    public function set_chinese($value=1){
        $this->chinese = $value;
    }
    public function set_english($value=1){
        $this->english = $value;
    }
    public function set_french($value=1){
        $this->french = $value;
    }
    public function set_german($value=1){
        $this->german = $value;
    }
    public function set_hindi($value=1){
        $this->hindi = $value;
    }
    public function set_italian($value=1){
        $this->italian = $value;
    }
    public function set_japanese($value=1){
        $this->japanese = $value;
    }
    public function set_javanese($value=1){
        $this->javanese = $value;
    }
    public function set_korean($value=1){
        $this->korean = $value;
    }
    public function set_malay($value=1){
        $this->malay = $value;
    }
    public function set_marathi($value=1){
        $this->marathi = $value;
    }
    public function set_portuguese($value=1){
        $this->portuguese = $value;
    }
    public function set_punjabi($value=1){
         $this->punjabi = $value;
    }
    public function set_russian($value=1){
         $this->russian = $value;
    }
    public function set_spanish($value=1){
         $this->spanish = $value;
    }
    public function set_tamil($value=1){
         $this->tamil = $value;
    }
    public function set_telugu($value=1){
         $this->telugu = $value;
    }
    public function set_vietnamese($value=1){
         $this->vietnamese = $value;
    }
};
?>
