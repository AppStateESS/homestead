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

    # check boxes

    # drop downs
    var $political_view;
    var $major;
    var $experience;
    var $sleep_time;
    var $wakeup_time;
    var $overnight_guests;
    var $loudness;
    var $cleanliness;
    var $study_time;
    var $free_time;

    function HMS_Student_Profile($user_id = NULL)
    {
        if(isset($user_id)){
            $this->setUserID($user_id);
        }else{
            return;
        }

        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_Studnet_Profile()','Caught error from init');
            return $result;
        }
    }

    function init()
    {

    }

    /**
     * Accessor / Mutator Methods
     */
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
    
    function set_study_time($time){
        $this->study_time = $time;
    }

    function get_study_time(){
        return $this->study_time;
    }

    function set_free_time($time){
        $this->free_time = $time;
    }
    
    function get_free_time(){
        return $this->free_time;
    }
};
?>
