<?php

/**
 * The HMS_Acivity_Log class
 * Handles logging of various activities and produces the log pager.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Activity_Log{

    var $user_id;
    var $timestamp;
    var $activity;
    var $actor;
    var $notes;

    var $activity_text = array( ACTIVITY_LOGIN                      => "Logged in",
                                ACTIVITY_AGREED_TO_TERMS            => "Agreed to terms & agreement",
                                ACTIVITY_SUBMITTED_APPLICATION      => "Submitted housing application",
                                ACTIVITY_SUBMITTED_RLC_APPLICATION  => "Submitted an RLC application",
                                ACTIVITY_ACCEPTED_TO_RLC            => "Accepted to an RLC",
                                ACTIVITY_REQUESTED_ROOMMATE         => "Requested a roommate",
                                ACTIVITY_REQUESTED_AS_ROOMMATE      => "Requested as a roommate by",
                                ACTIVITY_ACCEPTED_ROOMMATE          => "Accepted a roommate request",
                                ACTIVITY_ACCEPTED_AS_ROOMMATE       => "Accepted as roommate",
                                ACTIVITY_PROFILE_CREATED            => "Created a profile",
                                ACTIVITY_ASSIGNED                   => "Assigned to room",
                                ACTIVITY_AUTO_ASSIGNED              => "Auto-assigned to room",
                                ACTIVITY_REMOVED                    => "Removed from room",
                                ACTIVITY_ASSIGNMENT_REPORTED        => "Assignment reported to Banner",
                                ACTIVITY_REMOVAL_REPORTED           => "Removal reported to Banner",
                                ACTIVITY_LETTER_PRINTED             => "Assignment letter printed",
                                ACTIVITY_BANNER_ERROR               => "Banner error");
    
    /**
     * Constructor
     * 
     */
    function HMS_Activity_Log($user_id, $timestamp, $activity, $actor, $notes)
    {
        $this->set_user_id($user_id);
        $this->set_timestamp($timestamp);
        $this->set_activity($activity);
        $this->set_actor($actor);
        $this->set_notes($notes);
        
    }

    /**
     * Saves the current activity log object to the db.
     * Returns TRUE upon succes or a PEAR error object otherwise.
     */
    function save()
    {
       $db = &new PHPWS_DB('hms_activity_log');
       $db->addValue('user_id',     $this->get_user_id());
       $db->addValue('timestamp',   $this->get_timestamp());
       $db->addValue('activity',    $this->get_activity());
       $db->addValue('actor',       $this->get_actor());
       $db->addValue('notes',       $this->get_notes());

       $result = $db->insert();

       if(PEAR::isError($result)){
           PHPWS_Error::log($result,'hms','save_activity_log',"Could not save activity log");
           return $result;
       }else{
           return TRUE;
       }

    }

    /*******************
    * Static Functions *
    *******************/

    /**
     * Takes a username whos log the record should go in, the activity, the actor, and the notes
     * and creates a new Activity_Log object and saves it to the db.
     */
    function log_activity($userid, $activity, $actor, $notes)
    {
        $activity_log = new HMS_Activity_Log($userid, mktime(), $activity, $actor, $notes);
        $result = $activity_log->save();

        if(PEAR::isError($result)){
            return $result;
        }else{
            return TRUE;
        }

    }

    /**
     * Generates the activity log table
     */
    function activity_log_pager()
    {
        // TODO
    }

    /******************
    * Mutator Methods *
    ******************/

    function get_user_id(){
        return $this->user_id;
    }

    function set_user_id($id){
        $this->user_id = $id;
    }

    function get_timestamp(){
        return $this->user_id;
    }

    function set_timestamp($time){
        $this->timestamp = $time;
    }

    function get_activity(){
        return $this->activity;
    }

    function set_activity($activity){
        $this->activity = $activity;
    }

    function get_actor(){
        return $this->actor;
    }

    function set_actor($actor){
        $this->actor = $actor;
    }

    function get_notes(){
        return $this->notes;
    }

    function set_notes($notes){
        $this->notes = $notes;
    }
    
}
?>
