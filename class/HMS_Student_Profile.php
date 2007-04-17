<?php

/**
 * The HMS_Student_Profile class
 * Implements the Student_profile object and methods to load/save
 * student profiles from the database.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Student_Profile{

    var $id;

    var $user_id;
    var $date_submitted;

    # Music choices 
    var $arts_and_crafts = null;
    var $books_and_reading = null;
    var $cars = null;
    var $church_activities = null;
    var $collecting = null;
    var $computers_and_technology = null;
    var $dancing = null;
    var $fashion = null;
    var $fine_arts = null;
    var $gardening = null;
    var $games = null;
    var $humor = null;
    var $investing_personal_finance = null;
    var $movies = null;
    var $music = null;
    var $outdoor_activities = null;
    var $pets_and_animals = null;
    var $photography = null;
    var $politics = null;
    var $sports = null;
    var $travel = null;
    var $tv_shows = null;
    var $volunteering = null;

    # Hobby choices
    var $alternative = null;
    var $ambient = null;
    var $beach = null;
    var $bluegrass = null;
    var $blues = null;
    var $classical = null;
    var $classic_rock = null;
    var $country = null;
    var $electronic = null;
    var $folk = null;
    var $heavy_metal = null;
    var $hip_hop = null;
    var $house = null;
    var $industrial = null;
    var $jazz = null;
    var $popular_music = null;
    var $progressive = null;
    var $punk = null;
    var $r_and_b = null;
    var $rap = null;
    var $reggae = null;
    var $rock = null;
    var $world_music = null;
    
    # Study times
    var $study_early_morning = null;
    var $study_morning_afternoon = null;
    var $study_afternoon_evening = null;
    var $study_evening = null;
    var $study_late_night = null;

    # drop downs
    var $political_view;
    var $major;
    var $experience;
    var $sleep_time;
    var $wakeup_time;
    var $overnight_guests;
    var $loudness;
    var $cleanliness;
    var $free_time;

    
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
    function check_for_profile($user_id)
    {
        $db = &new PHPWS_DB('hms_student_profiles');
       
        $db->addWhere('user_id',$user_id,'=');
        $result = $db->select('row');
         
        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return $result;
        }

        if(sizeof($result > 0){
            return $result['id'];
        }else{
            return FALSE;
        }
    }

    function profile_pager()
    {
        
    }

    function get_pager_tags()
    {
        
    }

    /**
     * Static methods
     */

    /**
     * Uses HMS_Forms to display the profile form.
     */
    function showProfileForm(){
        PHPWS_Core::initModClass('hms','HMS_Forms.php');
        return HMS_Form::show_profile_form();
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

    function get_loudess(){
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
    
    function set_computer_and_technology($value = 1){
        $this->computer_and_technology = $value;
    }

    function get_computer_and_technology(){
        if($this->computer_and_technology == 1){
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
