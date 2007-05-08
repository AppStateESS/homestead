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

class HMS_Student_Profile{

    var $id;

    var $user_id;
    var $date_submitted;

    # Alternate contact info
    var $alternate_email = NULL;
    var $aim_sn = NULL;
    var $yahoo_sn = NULL;
    var $msn_sn = NULL;

    # Music choices 
    var $arts_and_crafts = 0;
    var $books_and_reading = 0;
    var $cars = 0;
    var $church_activities = 0;
    var $collecting = 0;
    var $computers_and_technology = 0;
    var $dancing = 0;
    var $fashion = 0;
    var $fine_arts = 0;
    var $gardening = 0;
    var $games = 0;
    var $humor = 0;
    var $investing_personal_finance = 0;
    var $movies = 0;
    var $music = 0;
    var $outdoor_activities = 0;
    var $pets_and_animals = 0;
    var $photography = 0;
    var $politics = 0;
    var $sports = 0;
    var $travel = 0;
    var $tv_shows = 0;
    var $volunteering = 0;
    var $writing = 0;

    # Hobby choices
    var $alternative = 0;
    var $ambient = 0;
    var $beach = 0;
    var $bluegrass = 0;
    var $blues = 0;
    var $classical = 0;
    var $classic_rock = 0;
    var $country = 0;
    var $electronic = 0;
    var $folk = 0;
    var $heavy_metal = 0;
    var $hip_hop = 0;
    var $house = 0;
    var $industrial = 0;
    var $jazz = 0;
    var $popular_music = 0;
    var $progressive = 0;
    var $punk = 0;
    var $r_and_b = 0;
    var $rap = 0;
    var $reggae = 0;
    var $rock = 0;
    var $world_music = 0;
    
    # Study times
    var $study_early_morning = 0;
    var $study_morning_afternoon = 0;
    var $study_afternoon_evening = 0;
    var $study_evening = 0;
    var $study_late_night = 0;

    # drop downs
    var $political_view = 0;
    var $major = 0;
    var $experience = 0;
    var $sleep_time = 0;
    var $wakeup_time = 0;
    var $overnight_guests = 0;
    var $loudness = 0;
    var $cleanliness = 0;
    var $free_time = 0;

    
    /**
     * Constructor
     * Optional parameter is a id number corresponding to database column 'id'
     */
    function HMS_Student_Profile($id = NULL)
    {
        if(!isset($id)){
            return;
        }
        
        $this->setID($id);

        # Initialize
        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_Studnet_Profile()','Caught error from init');
            return $result;
        }
    }

    function init()
    {
       if(!isset($this->id)){
           return FALSE;
       }

       $db = &new PHPWS_DB('hms_student_profiles');
       $result = $db->loadObject($this);
       
       if(PEAR::isError($result)){
           PHPWS_Error::log($result,'hms','init','Caught error from check_for_profile');
           return $result;
       }

        return $result;
    }

    function save()
    {
        $db = &new PHPWS_DB('hms_student_profiles');

        if($this->get_date_submitted() == NULL){
            $this->set_date_submitted();
        }

        $result = $db->saveObject($this);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
        }
        
        return $result;
    }

    /**
     * check_for_profile
     * Returns the id number of a profile, if it
     * exists for the given user name.
     * Returns FALSE if no profile is found.
     */
    function check_for_profile($user_id = NULL)
    {

        if(!isset($user_id)){
            $user_id = $_SESSION['asu_username'];
        }
       
        $db = &new PHPWS_DB('hms_student_profiles');
       
        $db->addWhere('user_id',$user_id,'=');
        $result = $db->select('row');

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return $result;
        }

        if($result != NULL && sizeof($result > 0)){
            return $result['id'];
        }else{
            return FALSE;
        }
    }

    /****************
     * Static methods
     ****************/

    /**
     * Uses HMS_Forms to display the profile form.
     */
    function show_profile_form()
    {
        PHPWS_Core::initModClass('hms','HMS_Deadlines.php');
        if(HMS_Deadlines::check_deadline_past('edit_profile_end_timestamp')){
            # too late
            $template['MESSAGE'] = "Sorry, it's too late to submit a profile.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }else if(!HMS_Deadlines::check_deadline_past('edit_profile_begin_timestamp')){
            # too early
            $template['MESSAGE'] = "Sorry, it's too early to submit a profile.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }else{
            PHPWS_Core::initModClass('hms','HMS_Forms.php');
            return HMS_Form::show_profile_form();
        }
        
    }

    /**
     * Shows the profile for the given username
     */
    function show_profile($username)
    {
        require(PHPWS_SOURCE_DIR . 'mod/hms/inc/profile_options.php');
        
        $id = HMS_Student_Profile::check_for_profile($username);
        
        if(PEAR::isError($id)){
            # db error
            PHPWS_Error::log($id);
            $template['MESSAGE'] = "Sorry, there was an error working with the database. Please contact Housing and Residence Life if you need assistance.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }elseif($id !== FALSE){
            # profile found
            $profile = new HMS_Student_Profile($id);
        }else{
            # No profile found
            $template['MESSAGE'] = "No profile found for $username.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }

        $template = array();
        $profile_form = &new PHPWS_Form('profile_form');
        $profile_form->useRowRepeat();

        $none_given = '<span style="color:#CCC;">none given</span>';
       
        /***** Contact Info *****/
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $template['TITLE'] = HMS_SOAP::get_name($username) . '\'s Profile';
        $template['EMAIL_ADDRESS'] = "<a href=\"mailto:$username@appstate.edu\">$username@appstate.edu</a>";
        
        $template['ALTERNATE_EMAIL_LABEL'] = 'Alternate email: ';
        $alt_email = $profile->get_alternate_email();
        if(isset($alt_email)){
            $template['ALTERNATE_EMAIL'] = "<a href=\"mailto:$alt_email\">$alt_email</a>";
        }else{
            $template['ALTERNATE_EMAIL'] = $none_given;
        }

        $template['AIM_SN_LABEL'] = 'AIM screen name: ';
        $aim_sn = $profile->get_aim_sn();
        if(isset($aim_sn)){
            $template['AIM_SN'] = $aim_sn;
        }else{
            $template['AIM_SN'] = $none_given;
        }

        $template['YAHOO_SN_LABEL'] = 'Yahoo! screen name: ';
        $yahoo_sn = $profile->get_yahoo_sn();
        if(isset($yahoo_sn)){
            $template['YAHOO_SN'] = $yahoo_sn;
        }else{
            $template['YAHOO_SN'] = $none_given;
        }

        $template['MSN_SN_LABEL'] = 'MSN screen name: ';
        $msn_sn = $profile->get_msn_sn();
        if(isset($msn_sn)){
            $template['MSN_SN'] = $msn_sn;
        }else{
            $template['MSN_SN'] = $none_given;
        }

        /***** About Me *****/
        $profile_form->addCheck('hobbies_checkbox',$hobbies);
        $profile_form->setLabel('hobbies_checkbox',$hobbies_labels);
        $profile_form->setDisabled('hobbies_checkbox');
        $template['HOBBIES_CHECKBOX_QUESTION'] = 'My Hobbies and Interests: ';

        # set matches on hobby check boxes
        $hobbies_matches = HMS_Student_Profile::get_hobbies_matches($profile);
        $profile_form->setMatch('hobbies_checkbox',$hobbies_matches);
        
        $profile_form->addCheck('music_checkbox',$music);
        $profile_form->setLabel('music_checkbox',$music_labels);
        $profile_form->setDisabled('music_checkbox');
        $template['MUSIC_CHECKBOX_QUESTION'] = 'My Music Preferences: ';

        # set matches on music check boxes
        $music_matches = HMS_Student_Profile::get_music_matches($profile);
        $profile_form->setMatch('music_checkbox',$music_matches);

        $template['POLITICAL_VIEWS_DROPBOX_LABEL'] = 'Political views: ';
        $template['POLITICAL_VIEWS_DROPBOX'] = $political_views[$profile->get_political_view()];

        /***** College Life *****/
        $template['INTENDED_MAJOR_LABEL'] = 'Intended major: ';
        $template['INTENDED_MAJOR'] = $majors[$profile->get_major()];

        $template['IMPORTANT_EXPERIENCE_LABEL'] = 'I fee the most important part of my college experience is: ';
        $template['IMPORTANT_EXPERIENCE'] = $experiences[$profile->get_experience()];
        
        /***** Daily Life *****/
        $template['SLEEP_TIME_LABEL']       = 'I generally go to sleep: '; 
        $template['SLEEP_TIME']             = $sleep_times[$profile->get_sleep_time()];
        $template['WAKEUP_TIME_LABEL']      = 'I generally wake up: '; 
        $template['WAKEUP_TIME']            = $wakeup_times[$profile->get_wakeup_time()];
        $template['OVERNIGHT_GUESTS_LABEL'] = 'I plan on hosting overnight guests: '; 
        $template['OVERNIGHT_GUESTS']       = $overnight_guests[$profile->get_overnight_guests()];
        $template['LOUDNESS_LABEL']         = 'In my daily activities: '; 
        $template['LOUDNESS']               = $loudness[$profile->get_loudness()];
        $template['CLEANLINESS_LABEL']      = 'I would describe myself as: '; 
        $template['CLEANLINESS']            = $cleanliness[$profile->get_cleanliness()];
        $template['FREE_TIME_LABEL']        = 'If I have free time I would rather: '; 
        $template['FREE_TIME']              = $free_time[$profile->get_free_time()];

        $profile_form->addCheck('study_times',$study_times);
        $profile_form->setLabel('study_times',$study_times_labels);
        $profile_form->setDisabled('study_times');
        $template['STUDY_TIMES_QUESTION'] = 'I prefer to study: ';
        # set matches on study times check boxes here, set disabled
        $study_matches = HMS_Student_Profile::get_study_matches($profile);
        $profile_form->setMatch('study_times',$study_matches);

        $profile_form->mergeTemplate($template);
        $template = $profile_form->getTemplate();

        return PHPWS_Template::process($template,'hms','student/profile_form.tpl');
    }

    /**
     * Saves a submitted profile
     */
    function submit_profile()
    {

        # Check to see if a student already has a profile on file.
        # If so, pass the profile's id to the Student_Profile constructor
        # so it will load the current profile, and then update it.
        # Otherwise, create a new profile.
        $id = HMS_Student_Profile::check_for_profile($_SESSION['asu_username']);
        
        if(PEAR::isError($id)){
            PHPWS_Error::log($id);
            $template['MESSAGE'] = "Sorry, there was an error working with the database. Please contact Housing and Residence Life if you need assistance.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }elseif($id !== FALSE){
            $profile = new HMS_Student_Profile($id);
        }else{
            $profile = new HMS_Student_Profile();
            $profile->set_user_id($_SESSION['asu_username']);
            $profile->set_date_submitted();
        }

        #test($_REQUEST);


        # Alternate contact info
        if(isset($_REQUEST['alternate_email']) && $_REQUEST['alternate_email'] != ''){
            $profile->set_alternate_email($_REQUEST['alternate_email']);
        }

        if(isset($_REQUEST['aim_sn']) && $_REQUEST['aim_sn'] != ''){
            $profile->set_aim_sn($_REQUEST['aim_sn']);
        }
        
        if(isset($_REQUEST['yahoo_sn']) && $_REQUEST['yahoo_sn'] != ''){
            $profile->set_yahoo_sn($_REQUEST['yahoo_sn']);
        }
        
        if(isset($_REQUEST['msn_sn']) && $_REQUEST['msn_sn'] != ''){
            $profile->set_msn_sn($_REQUEST['msn_sn']);
        }
        
        # Hobbies check boxes
        if(isset($_REQUEST['hobbies_checkbox']['arts_and_crafts'])){
            $profile->set_arts_and_crafts();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['books_and_reading'])){
            $profile->set_books_and_reading();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['cars'])){
            $profile->set_cars();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['church_activities'])){
            $profile->set_church_activities();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['collecting'])){
            $profile->set_collecting();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['computers_and_technology'])){
            $profile->set_computers_and_technology();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['dancing'])){
            $profile->set_dancing();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['fashion'])){
            $profile->set_fashion();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['fine_arts'])){
            $profile->set_fine_arts();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['gardening'])){
            $profile->set_gardening();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['games'])){
            $profile->set_games();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['humor'])){
            $profile->set_humor();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['investing_personal_finance'])){
            $profile->set_investing_personal_finance();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['movies'])){
            $profile->set_movies();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['music'])){
            $profile->set_music();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['outdoor_activities'])){
            $profile->set_outdoor_activities();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['pets_and_animals'])){
            $profile->set_pets_and_animals();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['photography'])){
            $profile->set_photography();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['politics'])){
            $profile->set_politics();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['sports'])){
            $profile->set_sports();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['travel'])){
            $profile->set_travel();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['tv_shows'])){
            $profile->set_tv_shows();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['volunteering'])){
            $profile->set_volunteering();
        }
        
        if(isset($_REQUEST['hobbies_checkbox']['writing'])){
            $profile->set_writing();
        }
        
        # Music check boxes
        if(isset($_REQUEST['music_checkbox']['alternative'])){
            $profile->set_alternative();
        }
        
        if(isset($_REQUEST['music_checkbox']['ambient'])){
            $profile->set_ambient();
        }
        
        if(isset($_REQUEST['music_checkbox']['beach'])){
            $profile->set_beach();
        }
        
        if(isset($_REQUEST['music_checkbox']['bluegrass'])){
            $profile->set_bluegrass();
        }
        
        if(isset($_REQUEST['music_checkbox']['blues'])){
            $profile->set_blues();
        }
        
        if(isset($_REQUEST['music_checkbox']['classical'])){
            $profile->set_classical();
        }
        
        if(isset($_REQUEST['music_checkbox']['classic_rock'])){
            $profile->set_classic_rock();
        }
        
        if(isset($_REQUEST['music_checkbox']['country'])){
            $profile->set_country();
        }
        
        if(isset($_REQUEST['music_checkbox']['electronic'])){
            $profile->set_electronic();
        }
        
        if(isset($_REQUEST['music_checkbox']['folk'])){
            $profile->set_folk();
        }
        
        if(isset($_REQUEST['music_checkbox']['heavy_metal'])){
            $profile->set_heavy_metal();
        }
        
        if(isset($_REQUEST['music_checkbox']['hip_hop'])){
            $profile->set_hip_hop();
        }
        
        if(isset($_REQUEST['music_checkbox']['house'])){
            $profile->set_house();
        }
        
        if(isset($_REQUEST['music_checkbox']['industrial'])){
            $profile->set_industrial();
        }
        
        if(isset($_REQUEST['music_checkbox']['jazz'])){
            $profile->set_jazz();
        }
        
        if(isset($_REQUEST['music_checkbox']['popular_music'])){
            $profile->set_popular_music();
        }
        
        if(isset($_REQUEST['music_checkbox']['progressive'])){
            $profile->set_progressive();
        }
        
        if(isset($_REQUEST['music_checkbox']['punk'])){
            $profile->set_punk();
        }
        
        if(isset($_REQUEST['music_checkbox']['r_and_b'])){
            $profile->set_r_and_b();
        }
        
        if(isset($_REQUEST['music_checkbox']['rap'])){
            $profile->set_rap();
        }
        
        if(isset($_REQUEST['music_checkbox']['reggae'])){
            $profile->set_reggae();
        }
        
        if(isset($_REQUEST['music_checkbox']['alternative'])){
            $profile->set_rock();
        }
        
        if(isset($_REQUEST['music_checkbox']['world_music'])){
            $profile->set_world_music();
        }

        # Study times
        if(isset($_REQUEST['study_times']['study_early_morning'])){
            $profile->set_study_early_morning();
        }

        if(isset($_REQUEST['study_times']['study_morning_afternoon'])){
            $profile->set_study_morning_afternoon();
        }

        if(isset($_REQUEST['study_times']['study_afternoon_evening'])){
            $profile->set_study_afternoon_evening();
        }

        if(isset($_REQUEST['study_times']['study_evening'])){
            $profile->set_study_evening();
        }

        if(isset($_REQUEST['study_times']['study_late_night'])){
            $profile->set_study_late_night();
        }

        # Drop downs
        if(isset($_REQUEST['political_views_dropbox']) && $_REQUEST['political_views_dropbox'] != 0){
            $profile->set_political_view($_REQUEST['political_views_dropbox']);
        }
        
        if(isset($_REQUEST['intended_major']) && $_REQUEST['intended_major'] != 0){
            $profile->set_major($_REQUEST['intended_major']);
        }
        
        if(isset($_REQUEST['important_experience']) && $_REQUEST['important_experience'] != 0){
            $profile->set_experience($_REQUEST['important_experience']);
        }

        if(isset($_REQUEST['sleep_time']) && $_REQUEST['sleep_time'] != 0){
            $profile->set_sleep_time($_REQUEST['sleep_time']);
        }

        if(isset($_REQUEST['wakeup_time']) && $_REQUEST['wakeup_time'] != 0){
            $profile->set_wakeup_time($_REQUEST['wakeup_time']);
        }
        
        if(isset($_REQUEST['overnight_guests']) && $_REQUEST['overnight_guests'] != 0){
            $profile->set_overnight_guests($_REQUEST['overnight_guests']);
        }
        
        if(isset($_REQUEST['loudness']) && $_REQUEST['loudness'] != 0){
            $profile->set_loudness($_REQUEST['loudness']);
        }
        
        if(isset($_REQUEST['cleanliness']) && $_REQUEST['cleanliness'] != 0){
            $profile->set_cleanliness($_REQUEST['cleanliness']);
        }
        
        if(isset($_REQUEST['free_time']) && $_REQUEST['free_time'] != 0){
            $profile->set_free_time($_REQUEST['free_time']);
        }

        //test($profile->save());
        $result = $profile->save();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            $template['MESSAGE'] = "Sorry, there was an error working with the database. Please contact Housing and Residence Life if you need assistance.";
            return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
        }

        $template['SUCCESS'] = "Your profile was successfully created/updated.";
        $template['SUCCESS'] .= "<br /><br />";
        $template['SUCCESS'] .= PHPWS_Text::secureLink(_('Back to Main Menu'), 'hms', array('type'=>'student','op'=>'main'));
        return PHPWS_Template::process($template, 'hms', 'student/student_success_failure_message.tpl');
    }


    /**
     * Uses the forms class to display the profile search page.
     */
    function display_profile_search()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::profile_search_form();
    }

    /**
     * Does the actual searching of profiles.
     */
    function profile_search()
    {
        $tags = array();

        $tags['RESULTS'] = HMS_Student_Profile::profile_search_pager();

        return PHPWS_Template::process($tags, 'hms', 'student/profile_search_results.tpl');
    }
    
    /**
     * Sets up the pager object for searching questionnairs.
     */
    function profile_search_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['USERNAME']   = _('Email');
        $pageTags['FIRST_NAME'] = _('First Name');
        $pageTags['LAST_NAME']  = _('Last Name');
        $PageTags['ACTIONS']    = _('Action');

        $pager = &new DBPager('hms_student_profiles','HMS_Student_Profile');

        $pager->addWhere('hms_student_profiles.user_id',$_REQUEST['asu_username'],'ILIKE');
        $pager->db->addOrder('user_id','ASC');
        # TODO: CHECK GENDER HERE!!!!

        $pager->setModule('hms');
        $pager->setTemplate('student/profile_search_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No matches found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);

        return $pager->get();
    }

    /* 
     *Sets up the row tags for the pager
     */
    function getPagerTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        
        $tags['STUDENT_ID'] = $this->get_user_id() . "@appstate.edu";
        $tags['FIRST_NAME'] = HMS_SOAP::get_first_name($this->get_user_id());
        $tags['LAST_NAME'] = HMS_SOAP::get_last_name($this->get_user_id());
        $tags['ACTIONS'] = PHPWS_Text::secureLink('[View Profile]', 'hms',array('type'=>'student','op'=>'show_profile','user'=>$this->get_user_id()));

        return $tags;
    }
    
    /**
     * Function to determine which hobbies check boxes need to be checked
     * Takes a Student_Profile object and returns an array of the checkbox names
     * which should be checked. (Used as input to the setMatch function).
     */
    function get_hobbies_matches($profile)
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

        return $hobbies_matches;
    }

    /**
     * Function to determine which music check boxes need to be checked
     * Takes a Student_Profile object and returns an array of the checkbox names
     * which should be checked. (Used as input to the setMatch function).
     */
    function get_music_matches($profile)
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

    function get_study_matches($profile)
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


    /**
     * Accessor / Mutator Methods
     */

    function setID($id){
        $this->id = $id;
    }
    
    function getID(){
        return $this->id;
    }
    
    function set_user_id($user_id){
        $this->user_id = $user_id;
    }

    function get_user_id(){
        return $this->user_id;
    }

    function set_date_submitted($date = NULL){
        if(isset($date)){
            $this->date_submitted = $date;
        }else{
            $this->date_submitted = mktime();
        }
    }
    
    function get_date_submitted(){
        return $this->date_submitted;
    }

    function set_alternate_email($mail){
        $this->alternate_email = $mail;
    }

    function get_alternate_email(){
        return $this->alternate_email;
    }

    function set_aim_sn($sn){
        $this->aim_sn = $sn;
    }

    function get_aim_sn(){
        return $this->aim_sn;
    }

    function set_yahoo_sn($sn){
        $this->yahoo_sn = $sn;
    }

    function get_yahoo_sn(){
        return $this->yahoo_sn;
    }
    
    function set_msn_sn($sn){
        $this->msn_sn = $sn;
    }

    function get_msn_sn(){
        return $this->msn_sn;
    }
    
    function set_political_view($view){
        $this->political_view = $view;
    }

    function get_political_view(){
        return $this->political_view;
    }
 
    function set_major($major){
        $this->major = $major;
    }

    function get_major(){
        return $this->major;
    }

    function set_experience($exp){
        $this->experience = $exp;
    }

    function get_experience(){
        return $this->experience;
    }

    function set_sleep_time($time){
        $this->sleep_time = $time;
    }

    function get_sleep_time(){
        return $this->sleep_time;
    }

    function set_wakeup_time($time){
        $this->wakeup_time = $time;
    }

    function get_wakeup_time(){
        return $this->wakeup_time;
    }

    function set_overnight_guests($guests){
        $this->overnight_guests = $guests;
    }

    function get_overnight_guests(){
        return $this->overnight_guests;
    }

    function set_loudness($loudness){
        $this->loudness = $loudness;
    }

    function get_loudness(){
        return $this->loudness;
    }
    
    function set_cleanliness($clean){
        $this->cleanliness = $clean;
    }

    function get_cleanliness(){
        return $this->cleanliness;
    }
    
    function set_free_time($time){
        $this->free_time = $time;
    }
    
    function get_free_time(){
        return $this->free_time;
    }

    /**
     * Hobbies check boxes
     */

    function set_arts_and_crafts($value = 1){
        $this->arts_and_crafts = $value;
    }

    function get_arts_and_crafts(){
        if($this->arts_and_crafts == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_books_and_reading($value = 1){
        $this->books_and_reading = $value;
    }

    function get_books_and_reading(){
        if($this->books_and_reading == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_cars($value = 1){
        $this->cars = $value;
    }

    function get_cars(){
        if($this->cars == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function set_church_activities($value = 1){
        $this->church_activities = $value;
    }

    function get_church_activities(){
        if($this->church_activities == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_collecting($value = 1){
        $this->collecting = $value;
    }

    function get_collecting(){
        if($this->collecting == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_computers_and_technology($value = 1){
        $this->computers_and_technology = $value;
    }

    function get_computers_and_technology(){
        if($this->computers_and_technology == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_dancing($value = 1){
        $this->dancing = $value;
    }

    function get_dancing(){
        if($this->dancing == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_fashion($value = 1){
        $this->fashion = $value;
    }

    function get_fashion(){
        if($this->fashion == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_fine_arts($value = 1){
        $this->fine_arts = $value;
    }

    function get_fine_arts(){
        if($this->fine_arts == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_gardening($value = 1){
        $this->gardening = $value;
    }

    function get_gardening(){
        if($this->gardening == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_games($value = 1){
        $this->games = $value;
    }

    function get_games(){
        if($this->games == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_humor($value = 1){
        $this->humor = $value;
    }

    function get_humor(){
        if($this->humor == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_investing_personal_finance($value = 1){
        $this->investing_personal_finance = $value;
    }

    function get_investing_personal_finance(){
        if($this->investing_personal_finance == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_movies($value = 1){
        $this->movies = $value;
    }

    function get_movies(){
        if($this->movies == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_music($value = 1){
        $this->music = $value;
    }

    function get_music(){
        if($this->music == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_outdoor_activities($value = 1){
        $this->outdoor_activities = $value;
    }

    function get_outdoor_activities(){
        if($this->outdoor_activities == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_pets_and_animals($value = 1){
        $this->pets_and_animals = $value;
    }

    function get_pets_and_animals(){
        if($this->pets_and_animals == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_photography($value = 1){
        $this->photography = $value;
    }

    function get_photography(){
        if($this->photography == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_politics($value = 1){
        $this->politics = $value;
    }

    function get_politics(){
        if($this->politics == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_sports($value = 1){
        $this->sports = $value;
    }

    function get_sports(){
        if($this->sports == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_travel($value = 1){
        $this->travel = $value;
    }

    function get_travel(){
        if($this->travel == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_tv_shows($value = 1){
        $this->tv_shows = $value;
    }

    function get_tv_shows(){
        if($this->tv_shows == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function set_volunteering($value = 1){
        $this->volunteering = $value;
    }

    function get_volunteering(){
        if($this->volunteering == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_writing($value = 1){
        $this->writing = $value;
    }

    function get_writing(){
        if($this->writing == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Music check boxes
     */

    function set_alternative($value = 1){
        $this->alternative = $value;
    }

    function get_alternative(){
        if($this->alternative == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_ambient($value = 1){
        $this->ambient = $value;
    }

    function get_ambient(){
        if($this->ambient == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_beach($value = 1){
        $this->beach = $value;
    }

    function get_beach(){
        if($this->beach == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_bluegrass($value = 1){
        $this->bluegrass = $value;
    }

    function get_bluegrass(){
        if($this->bluegrass == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_blues($value = 1){
        $this->blues = $value;
    }

    function get_blues(){
        if($this->blues == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_classical($value = 1){
        $this->classical = $value;
    }

    function get_classical(){
        if($this->classical == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_classic_rock($value = 1){
        $this->classic_rock = $value;
    }

    function get_classic_rock(){
        if($this->classic_rock == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_country($value = 1){
        $this->country = $value;
    }

    function get_country(){
        if($this->country == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_electronic($value = 1){
        $this->electronic = $value;
    }

    function get_electronic(){
        if($this->electronic == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_folk($value = 1){
        $this->folk = $value;
    }

    function get_folk(){
        if($this->folk == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_heavy_metal($value = 1){
        $this->heavy_metal = $value;
    }

    function get_heavy_metal(){
        if($this->heavy_metal == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_hip_hop($value = 1){
        $this->hip_hop = $value;
    }

    function get_hip_hop(){
        if($this->hip_hop == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_house($value = 1){
        $this->house = $value;
    }

    function get_house(){
        if($this->house == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_industrial($value = 1){
        $this->industrial = $value;
    }

    function get_industrial(){
        if($this->industrial == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_jazz($value = 1){
        $this->jazz = $value;
    }

    function get_jazz(){
        if($this->jazz == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_popular_music($value = 1){
        $this->popular_music = $value;
    }

    function get_popular_music(){
        if($this->popular_music == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_progressive($value = 1){
        $this->progressive = $value;
    }

    function get_progressive(){
        if($this->progressive == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_punk($value = 1){
        $this->punk = $value;
    }

    function get_punk(){
        if($this->punk == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_r_and_b($value = 1){
        $this->r_and_b = $value;
    }

    function get_r_and_b(){
        if($this->r_and_b == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_rap($value = 1){
        $this->rap = $value;
    }

    function get_rap(){
        if($this->rap == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_reggae($value = 1){
        $this->reggae = $value;
    }

    function get_reggae(){
        if($this->reggae == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_rock($value = 1){
        $this->rock = $value;
    }

    function get_rock(){
        if($this->rock == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    function set_world_music($value = 1){
        $this->world_music = $value;
    }

    function get_world_music(){
        if($this->world_music == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Study times check boxes
     */
    function set_study_early_morning($value = 1){
        $this->study_early_morning = $value;
    }

    function get_study_early_morning(){
        if($this->study_early_morning == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function set_study_morning_afternoon($value = 1){
        $this->study_morning_afternoon = $value;
    }

    function get_study_morning_afternoon(){
        if($this->study_morning_afternoon == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function set_study_afternoon_evening($value = 1){
        $this->study_afternoon_evening = $value;
    }

    function get_study_afternoon_evening(){
        if($this->study_afternoon_evening == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function set_study_evening($value = 1){
        $this->study_evening = $value;
    }

    function get_study_evening(){
        if($this->study_evening == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    function set_study_late_night($value = 1){
        $this->study_late_night = $value;
    }

    function get_study_late_night(){
        if($this->study_late_night == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
};
?>
