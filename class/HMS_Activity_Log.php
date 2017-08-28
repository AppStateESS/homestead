<?php

namespace Homestead;

use \Homestead\exception\DatabaseException;
use \Homestead\exception\StudentNotFoundException;

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
    public function __construct($id = 0, $user_id = null, $timestamp = null,
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
            $db = new \PHPWS_DB($table);
            $db->addWhere('id', $this->id);
            $result = $db->loadObject($this);
            if(!$result || \PHPWS_Error::logIfError($result)) {
                $tis->id = 0;
            }
        }
    }

    /**
     * Saves the current activity log object to the db.
     * Returns TRUE upon succes or a \PEAR error object otherwise.
     */
    public function save()
    {
        if($this->id != 0) {
            return FALSE;
        }

        $db = new \PHPWS_DB('hms_activity_log');
        $db->addValue('user_id',     $this->get_user_id());
        $db->addValue('timestamp',   $this->get_timestamp());
        $db->addValue('activity',    $this->get_activity());
        $db->addValue('actor',       $this->get_actor());
        $db->addValue('notes',       $this->get_notes());

        $result = $db->insert();

        if(\PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }else{
            return TRUE;
        }
    }

    /**
     * Turns an integer activity into text
     */
    public function get_text_activity($num = null)
    {
        $activities = HMS_Activity_Log::getActivityMapping();
        if (!is_null($num)) {
            return $activities[$num];
        }

        return $activities[$this->get_activity()];
    }

    /**
     * Generates the activity log table
     */
    public function getPagerTags()
    {
        $tpl = array();

        try {
            $student = StudentFactory::getStudentByUsername($this->get_user_id(), Term::getSelectedTerm());
        }catch(StudentNotFoundException $e){
            \NQ::simple('hms', NotificationView::WARNING, "Could not find data for student: {$this->get_user_id()}");
            $student = null;
        }

        if(is_null($student)){
            $tpl['ACTEE'] = 'UNKNOWN';
        }else{
            $tpl['ACTEE'] = $student->getProfileLink();
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
        if(UserStatus::isMasquerading()) {
            $notes .= " Admin: " . UserStatus::getUsername(FALSE); // get the *real* username
        }

        $activity_log = new HMS_Activity_Log(NULL, $userid, time(), $activity, $actor, $notes);
        $activity_log->save();
    }

    /**
     * Gets the mapping of activity number to activity name.
     */
    public static function getActivityMapping()
    {
        return array(
            ACTIVITY_LOGIN                          => "Logged in",
            ACTIVITY_AGREED_TO_TERMS                => "Agreed to terms & agreement",
            ACTIVITY_SUBMITTED_APPLICATION          => "Submitted housing application",
            ACTIVITY_SUBMITTED_RLC_APPLICATION      => "Submitted RLC application",
            ACTIVITY_ACCEPTED_TO_RLC                => "Accepted to an RLC",
            ACTIVITY_TOO_OLD_REDIRECTED             => "Over 25, redirected",
            ACTIVITY_REQUESTED_AS_ROOMMATE          => "Roommate request",
            ACTIVITY_REJECTED_AS_ROOMMATE           => "Roommate request rejected",
            ACTIVITY_ACCEPTED_AS_ROOMMATE           => "Roommate request accepted",
            ACTIVITY_STUDENT_BROKE_ROOMMATE         => "Broke roommate pairing",
            ACTIVITY_STUDENT_CANCELLED_ROOMMATE_REQUEST => "Cancelled roommate request",
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
            ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED   => "Assignment deleted due to withdrawal",
            ACTIVITY_WITHDRAWN_ROOMMATE_DELETED     => "Roommate request deleted due to withdrawal",
            ACTIVITY_WITHDRAWN_RLC_APP_DENIED       => "RLC application denied due to withdrawal",
            ACTIVITY_WITHDRAWN_RLC_ASSIGN_DELETED   => "RLC assignment deleted due to withdrawal",
            ACTIVITY_APPLICATION_REPORTED           => "Application reported to Banner",
            ACTIVITY_DENIED_RLC_APPLICATION         => "Denied RLC Application",
            ACTIVITY_UNDENIED_RLC_APPLICATION       => "Un-denied RLC Application",
            ACTIVITY_ASSIGN_TO_RLC                  => "Assigned student to RLC",
            ACTIVITY_RLC_UNASSIGN                   => "Removed from RLC",
            ACTIVITY_USERNAME_UPDATED               => "Updated Username",
            ACTIVITY_APPLICATION_UPDATED            => "Updated Application",
            ACTIVITY_RLC_APPLICATION_UPDATED        => "Updated RLC Application",
            ACTIVITY_RLC_APPLICATION_DELETED		=> "RLC Application Deleted",
            ACTIVITY_ASSIGNMENTS_UPDATED            => "Updated Assignments",
            ACTIVITY_BANNER_QUEUE_UPDATED           => "Updated Banner Queue",
            ACTIVITY_ROOMMATES_UPDATED              => "Updated Roommates",
            ACTIVITY_ROOMMATE_REQUESTS_UPDATED      => "Updated Roommate Requests",
            ACTIVITY_CHANGE_ACTIVE_TERM             => "Changed Active Term",
            ACTIVITY_ADD_NOTE                       => "Note",
            ACTIVITY_LOTTERY_SIGNUP_INVITE          => "Invited to enter lottery", //depricated
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
            ACTIVITY_LOTTERY_OPTOUT                 => "Opted-out of waiting list",
            ACTIVITY_FLOOR_NOTIFIED_ANONYMOUSLY     => "Anonymous email notification sent to floor",
            ACTIVITY_FLOOR_NOTIFIED                 => "Email notification sent to floor",
            ACTIVITY_ROOM_CHANGE_SUBMITTED          => "Submitted Room Change Request",
            ACTIVITY_ROOM_CHANGE_APPROVED_RD        => "RD Approved Room Change",
            ACTIVITY_ROOM_CHANGE_APPROVED_HOUSING   => "Housing Approved Room Change",
            ACTIVITY_ROOM_CHANGE_COMPLETED          => "Room Change Completed",
            ACTIVITY_ROOM_CHANGE_DENIED             => "Room Change Denied",
            ACTIVITY_ROOM_CHANGE_AGREED             => "Agreed to Room Change Request",
            ACTIVITY_ROOM_CHANGE_DECLINE            => "Declined Room Change Request",
            ACTIVITY_LOTTERY_ROOMMATE_DENIED        => "Denied lottery roommate invite",
            ACTIVITY_CANCEL_HOUSING_APPLICATION     => "Housing Application Cancelled",
            ACTIVITY_ACCEPT_RLC_INVITE              => "Accepted RLC Invitation",
            ACTIVITY_DECLINE_RLC_INVITE             => "Declined RLC Invitation",
            ACTIVITY_RLC_INVITE_SENT                => "RLC Invitation Sent",
            ACTIVITY_EMERGENCY_CONTACT_UPDATED      => "Emergency Contact & Missing Person information updated",
            ACTIVITY_CHECK_IN                       => 'Checked-in',
            ACTIVITY_CHECK_OUT                      => 'Checked-out',
            ACTIVITY_REAPP_WAITINGLIST_APPLY        => 'Applied for Re-application Waiting List',
            ACTIVITY_REINSTATE_APPLICATION          => 'Reinstated Application',
            ACTIVITY_ROOM_CHANGE_REASSIGNED         => 'Reassigned due to Room Change',
            ACTIVITY_CONTRACT_CREATED               => 'Created a Contract',
            ACTIVITY_CONTRACT_SENT_EMAIL            => 'Contract Sent via Email',
            ACTIVITY_CONTRACT_STUDENT_SIGN_EMBEDDED => 'Student Signed Contract via Embedded Signing',
            ACTIVITY_CONTRACT_REMOVED_VOIDED        => 'Removed Voided Contract',
            ACTIVITY_MEAL_PLAN_SENT                 => 'Meal Plan Reported to Banner'
        );
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
