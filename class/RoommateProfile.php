<?php

/**
 * The RoommateProfile class
 * Implements the RoommateProfile object and methods to load/save
 * roommate profiles from the database.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package hms
 */
class RoommateProfile {

    public $id;

    public $username;

    public $bannerid;

    public $date_submitted;

    public $term;

    // Alternate contact info
    public $alternate_email = NULL;
    
    public $fb_link = NULL;

    public $instagram_sn = NULL;

    public $twitter_sn = NULL;
    
    public $tumblr_sn = NULL;
    
    public $kik_sn = NULL;
    
    public $about_me = NULL;

    // Hobby choices
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
    
    public $hobbies_array = array("arts_and_crafts", "books_and_reading", "cars", "church_activities", 
		    "collecting", "computers_and_technology", "dancing", "fashion", "fine_arts", "gardening", 
		    "games", "humor", "investing_personal_finance", "movies", "music", "outdoor_activities", 
		    "pets_and_animals", "photography", "politics", "sports", "travel", "tv_shows", "volunteering", "writing", "rotc");

    // music choices
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
        
    public $music_array = array("alternative", "ambient", "beach", "bluegrass", "blues", "christian", "classical", 
    		"classic_rock", "country", "electronic", "folk", "heavy_metal", "hip_hop", "house", "industrial", "jazz", 
    		"popular_music", "progressive", "punk", "r_and_b", "rap", "reggae", "rock", "world_music");

    // Study times
    public $study_early_morning = 0;

    public $study_morning_afternoon = 0;

    public $study_afternoon_evening = 0;

    public $study_evening = 0;

    public $study_late_night = 0;
    
    public $study_array = array("study_early_morning", "study_morning_afternoon", 
    		"study_afternoon_evening", "study_evening", "study_late_night");

    // drop downs
    public $political_views = 0;

    public $major = 0;

    public $experience = 0;

    public $sleep_time = 0;

    public $wakeup_time = 0;

    public $overnight_guests = 0;

    public $loudness = 0;

    public $cleanliness = 0;

    public $free_time = 0;
    
    public $drop_down_array = array("political_views", "major", "experience", 
   			"sleep_time", "wakeup_time", "overnight_guests", "loudness", "cleanliness", "free_time");

    // Spoken languages
    // Top 20 most spoken languages:
    // http://en.wikipedia.org/wiki/Ethnologue_list_of_most_spoken_languages
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
    
    public $lang_array = array("arabic" , "bengali" , "chinese" , "english" , "french" , "german" , "hindi" , 
        	"italian" , "japanese" , "javanese" , "korean" , "malay" , "marathi" , "portuguese" , "punjabi" , 
        	"russian" , "tamil" , "telugu" , "vietnamese");

    /**
     * Constructor
     * Optional parameter is a id number corresponding to database column 'id'
     */
    public function __construct($id = NULL)
    {
        if (!isset($id)) {
            return;
        }

        $this->setID($id);

        // Initialize
        $result = $this->init();
    }

    public function init()
    {
        if (!isset($this->id)) {
            return false;
        }

        $db = new PHPWS_DB('hms_student_profiles');
        $result = $db->loadObject($this);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_student_profiles');

        if ($this->get_date_submitted() == NULL) {
            $this->set_date_submitted();
        }

        $result = $db->saveObject($this);

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_PROFILE_CREATED, UserStatus::getUsername(), '');

        return $result;
    }

    /**
     * Sets up the row tags for the pager
     *
     * @return Array Array of template tags.
     */
    public function getPagerTags()
    {
        $student = StudentFactory::getStudentByBannerID($this->bannerid, $this->term);

        $tags['STUDENT_ID'] = $student->getUsername() . "@appstate.edu";
        $tags['FIRST_NAME'] = $student->getFirstName();
        $tags['LAST_NAME'] = $student->getLastName();

        $viewProfileCmd = CommandFactory::getCommand('ShowRoommateProfile');
        $viewProfileCmd->setBannerid($student->getBannerid());
        $viewProfileCmd->setTerm($this->term);

        $tags['ACTIONS'] = $viewProfileCmd->getLink('[View Profile]');

        return $tags;
    }

    /*
     * Accessor / Mutator Methods
     */

    /**
     * Sets the profile's id.
     *
     * @param integer $id
     */
     
    public function setID($id)
    {
        $this->id = $id;
    }

    /**
     * Returns this profile's id.
     *
     * @return integer profile id
     */
    public function getID()
    {
        return $this->id;
    }

    public function setUsername($name)
    {
        $this->username = $name;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setBannerid($id)
    {
        $this->bannerid = $id;
    }

    public function getBannerid()
    {
        return $this->bannerid;
    }

    public function set_date_submitted($date = NULL)
    {
        if (isset($date)) {
            $this->date_submitted = $date;
        } else {
            $this->date_submitted = time();
        }
    }

    public function get_date_submitted()
    {
        return $this->date_submitted;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    //universal get method for dropdowns/text fields
    public function get_text($item)
    {
        return $this->$item;
    }

    //universal set method for dropdowns/text fields
    public function set_text($item, $value)
    {
        $this->$item = $value;
    }

    //universal get for checkboxes
    public function get_checked($item)
    {
        return $this->$item == 1 ? true : false;
    }

    //universal set for checkboxes
    public function set_checked($item, $value = 1)
    {
        $this->$item = $value;
    }
}
?>
