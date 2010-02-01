<?php

/**
 * The HMS_Acivity_Log class
 * Handles logging of various activities and produces the log pager.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Activity_Log{

    public $id;
    public $user_id;
    public $timestamp;
    public $activity;
    public $actor;
    public $notes;

    public $activity_text;

    /**
     * Constructor
     *
     */
    public function HMS_Activity_Log($id = 0, $user_id = null, $timestamp = null,
    $activity = null, $actor = null, $notes = null)
    {
        $this->activity_text = HMS_Activity_Log::getActivityMapping();

        if(is_null($id) || $id == 0) {
            $this->id = 0;
            $this->set_user_id($user_id);
            $this->set_timestamp($timestamp);
            $this->set_activity($activity);
            $this->set_actor($actor);
            $this->set_notes($notes);
        } else {
            $this->id = $id;
            $db = new PHPWS_DB($table);
            $db->addWhere('id', $this->id);
            $result = $db->loadObject($this);
            if(!$result || PHPWS_Error::logIfError($result)) {
                $tis->id = 0;
            }
        }
    }

    /**
     * Saves the current activity log object to the db.
     * Returns TRUE upon succes or a PEAR error object otherwise.
     */
    public function save()
    {
        if($this->id != 0) {
            return FALSE;
        }

        $db = new PHPWS_DB('hms_activity_log');
        $db->addValue('user_id',     $this->get_user_id());
        $db->addValue('timestamp',   $this->get_timestamp());
        $db->addValue('activity',    $this->get_activity());
        $db->addValue('actor',       $this->get_actor());
        $db->addValue('notes',       $this->get_notes());

        $result = $db->insert();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModclass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }else{
            return TRUE;
        }
    }

    /**
     * Turns an integer activity into text
     */
    public function get_text_activity($num = -1)
    {
        $activities = HMS_Activity_Log::getActivityMapping();
        if($num > -1)
        return $activities[$num];

        return $activities[$this->get_activity()];
    }

    /**
     * Generates the activity log table
     */
    public function getPagerTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        $tpl = array();
         
        try {
            $student = StudentFactory::getStudentByUsername($this->get_user_id(), Term::getSelectedTerm());
        }catch(StudentNotFoundException $e){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, "Could not find data for student: {$this->get_user_id()}");
            $student = null;
        }

        if(is_null($student)){
            $tpl['ACTEE'] = 'UNKNOWN';
        }else{
            $tpl['ACTEE'] = $student->getFullNameProfileLink();
        }

        if(strcmp($this->get_user_id(),$this->get_actor()) == 0)
        $tpl['ACTOR'] = NULL;
        else
        $tpl['ACTOR'] = $this->get_actor();

        $time = $this->get_timestamp();
        $tpl['DATE'] = date('j M Y', $time);
        $tpl['TIME'] = date('g:i a', $time);


        $tpl['ACTIVITY']  = $this->get_text_activity();

        $notes = $this->get_notes();
        if(!is_null($notes)) {
            $tpl['NOTES'] = $notes;
        }

        return $tpl;
    }

    /*******************
     * Static Functions *
     *******************/

    /**
     * Takes a username whos log the record should go in, the activity, the actor, and the notes
     * and creates a new Activity_Log object and saves it to the db.
     */
    public static function log_activity($userid, $activity, $actor, $notes = NULL)
    {
        test($activity);
        
        if(UserStatus::isMasquerading()) {
            $notes .= " Admin: " . UserStatus::getUsername(FALSE); // get the *real* username
        }

        $activity_log = new HMS_Activity_Log(NULL, $userid, mktime(), $activity, $actor, $notes);
        $result = $activity_log->save();
    }

    /**
     * Gets the mapping of activity number to activity name.
     */
    public static function getActivityMapping()
    {
        return array(   ACTIVITY_LOGIN                          => "Logged in",
        ACTIVITY_AGREED_TO_TERMS                => "Agreed to terms & agreement",
        ACTIVITY_SUBMITTED_APPLICATION          => "Submitted housing application",
        ACTIVITY_SUBMITTED_RLC_APPLICATION      => "Submitted an RLC application",
        ACTIVITY_ACCEPTED_TO_RLC                => "Accepted to an RLC",
        ACTIVITY_TOO_OLD_REDIRECTED             => "Over 25, redirected",
        ACTIVITY_REQUESTED_AS_ROOMMATE          => "Requested as a roommate by",
        ACTIVITY_REJECTED_AS_ROOMMATE           => "Rejected a roommate request",
        ACTIVITY_ACCEPTED_AS_ROOMMATE           => "Accepted as roommate",
        ACTIVITY_PROFILE_CREATED                => "Created a profile",
        ACTIVITY_ASSIGNED                       => "Assigned to room",
        ACTIVITY_AUTO_ASSIGNED                  => "Auto-assigned to room",
        ACTIVITY_REMOVED                        => "Removed from room",
        ACTIVITY_ASSIGNMENT_REPORTED            => "Assignment reported to Banner",
        ACTIVITY_REMOVAL_REPORTED               => "Removal reported to Banner",
        ACTIVITY_LETTER_PRINTED                 => "Assignment letter printed",
        ACTIVITY_BANNER_ERROR                   => "Banner error",
        ACTIVITY_LOGIN_AS_STUDENT               => "Admin logged in as student",
        ACTIVITY_ADMIN_ASSIGNED_ROOMMATE        => "Admin assigned roommate",
        ACTIVITY_ADMIN_REMOVED_ROOMMATE         => "Admin removed roommate",
        ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ       => "Automatically canceled roommate request",
        ACTIVITY_WITHDRAWN_APP                  => "Application withdrawn",
        ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED   => "Assignment deleted due to withdrawl",
        ACTIVITY_WITHDRAWN_ROOMMATE_DELETED     => "Roommate request deleted due to withdrawl",
        ACTIVITY_WITHDRAWN_RLC_APP_DENIED       => "RLC application denied due to withdrawl",
        ACTIVITY_WITHDRAWN_RLC_ASSIGN_DELETED   => "RLC assignment deleted due to withdrawl",
        ACTIVITY_APPLICATION_REPORTED           => "Application reported to Banner",
        ACTIVITY_DENIED_RLC_APPLICATION         => "Denied RLC Application",
        ACTIVITY_UNDENIED_RLC_APPLICATION       => "Un-denied RLC Application",
        ACTIVITY_ASSIGN_TO_RLC                  => "Assigned student to RLC",
        ACTIVITY_RLC_APP_SUBMITTED              => "Submitted RLC Application",
        ACTIVITY_USERNAME_UPDATED               => "Updated Username",
        ACTIVITY_APPLICATION_UPDATED            => "Updated Application",
        ACTIVITY_RLC_APPLICATION_UPDATED        => "Updated RLC Application",
        ACTIVITY_ASSIGNMENTS_UPDATED            => "Updated Assignments",
        ACTIVITY_BANNER_QUEUE_UPDATED           => "Updated Banner Queue",
        ACTIVITY_ROOMMATES_UPDATED              => "Updated Roommates",
        ACTIVITY_ROOMMATE_REQUESTS_UPDATED      => "Updated Roommate Requests",
        ACTIVITY_ADD_NOTE                       => "Note",
        ACTIVITY_LOTTERY_SIGNUP_INVITE          => "Invited to enter lottery",
        ACTIVITY_LOTTERY_ENTRY                  => "Lottery entry submitted",
        ACTIVITY_LOTTERY_INVITED                => "Lottery invitation sent",
        ACTIVITY_LOTTERY_REMINDED               => "Lottery invitation reminder sent",
        ACTIVITY_LOTTERY_ROOM_CHOSEN            => "Lottery room chosen",
        ACTIVITY_LOTTERY_REQUESTED_AS_ROOMMATE  => "Requested as a roommate for lottery room",
        ACTIVITY_LOTTERY_ROOMMATE_REMINDED      => "Lottery roommate invivation reminder sent",
        ACTIVITY_LOTTERY_CONFIRMED_ROOMMATE     => "Confirmed lottery roommate request",
        ACTIVITY_LOTTERY_EXECUTED               => "Lottery process executed",
        ACTIVITY_CREATE_TERM                    => "Created a new Term",
        ACTIVITY_NOTIFICATION_SENT              => "Notification sent",
        ACTIVITY_ANON_NOTIFICATION_SENT         => "Anonymous notification sent",
        ACTIVITY_HALL_NOTIFIED                  => "Email notification sent to hall",
        ACTIVITY_HALL_NOTIFIED_ANONYMOUSLY      => "Anonymous email notification sent to hall",
        ACTIVITY_LOTTERY_OPTOUT                 => "Opted-out of waiting list");
    }

    /**
     * Returns an array of all the activity ids. Based on the activity mapping above.
     */
    public static function get_activity_list()
    {
        $activities = HMS_Activity_Log::getActivityMapping();
        $list = array();

        foreach ($activities as $id=>$desc){
            $list[] = $id;
        }

        return $list;
    }

    /******************
     * Mutator Methods *
     ******************/

    public function get_user_id(){
        return $this->user_id;
    }

    public function set_user_id($id){
        $this->user_id = $id;
    }

    public function get_timestamp(){
        return $this->timestamp;
    }

    public function set_timestamp($time){
        $this->timestamp = $time;
    }

    public function get_activity(){
        return $this->activity;
    }

    public function set_activity($activity){
        $this->activity = $activity;
    }

    public function get_actor(){
        return $this->actor;
    }

    public function set_actor($actor){
        $this->actor = $actor;
    }

    public function get_notes(){
        return $this->notes;
    }

    public function set_notes($notes){
        $this->notes = $notes;
    }
}
?>
