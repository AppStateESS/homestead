<?php

/**
 * The HMS_Acivity_Log class
 * Handles logging of various activities and produces the log pager.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Activity_Log{

    var $id;
    var $user_id;
    var $timestamp;
    var $activity;
    var $actor;
    var $notes;

    var $activity_text;
    
    /**
     * Constructor
     * 
     */
    function HMS_Activity_Log($id = 0, $user_id = null, $timestamp = null, 
        $activity = null, $actor = null, $notes = null)
    {
        $this->activity_text = HMS_Activity_Log::get_activity_mapping();

        if(is_null($id) || $id == 0) {
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
    function save()
    {
        if(!is_null($this->id) || $id != 0) {
            return FALSE;
        }

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
    function log_activity($userid, $activity, $actor, $notes = '')
    {
        $activity_log = new HMS_Activity_Log(NULL, $userid, mktime(), $activity, $actor, $notes);
        $result = $activity_log->save();

        if(PEAR::isError($result)){
            return $result;
        }else{
            return TRUE;
        }

    }

    /**
     * Gets the mapping of activity number to activity name.
     */
    function get_activity_mapping()
    {
        return array(   ACTIVITY_LOGIN                      => "Logged in",
                        ACTIVITY_AGREED_TO_TERMS            => "Agreed to terms & agreement",
                        ACTIVITY_SUBMITTED_APPLICATION      => "Submitted housing application",
                        ACTIVITY_SUBMITTED_RLC_APPLICATION  => "Submitted an RLC application",
                        ACTIVITY_ACCEPTED_TO_RLC            => "Accepted to an RLC",
                        ACTIVITY_TOO_OLD_REDIRECTED         => "Over 25, redirected",
                        ACTIVITY_REQUESTED_AS_ROOMMATE      => "Requested as a roommate by",
                        ACTIVITY_REJECTED_AS_ROOMMATE       => "Rejected a roommate request",
                        ACTIVITY_ACCEPTED_AS_ROOMMATE       => "Accepted as roommate",
                        ACTIVITY_PROFILE_CREATED            => "Created a profile",
                        ACTIVITY_ASSIGNED                   => "Assigned to room",
                        ACTIVITY_AUTO_ASSIGNED              => "Auto-assigned to room",
                        ACTIVITY_REMOVED                    => "Removed from room",
                        ACTIVITY_ASSIGNMENT_REPORTED        => "Assignment reported to Banner",
                        ACTIVITY_REMOVAL_REPORTED           => "Removal reported to Banner",
                        ACTIVITY_LETTER_PRINTED             => "Assignment letter printed",
                        ACTIVITY_BANNER_ERROR               => "Banner error");
    }

    /**
     * Turns an integer activity into text
     */
    function get_text_activity($num = -1)
    {
        $activities = HMS_Activity_Log::get_activity_mapping();
        if($num > -1)
            return $activities[$num];

        return $activities[$this->get_activity()];
    }

    /**
     * Generates the activity log table
     */
    function getPagerTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        $tpl = array();
        $tpl['ACTEE']     = HMS_Student::get_link($this->get_user_id());
        $tpl['TIMESTAMP'] = $this->get_timestamp();
        $tpl['ACTIVITY']  = $this->get_text_activity();
        $tpl['ACTOR']     = $this->get_actor();
        $tpl['NOTES']     = $this->get_notes();

        return $tpl;
    }

    /**
     *

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
        return $this->timestamp;
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
    
    /******************
     * User Interface *
     ******************/

    /**
     * Shows the DBPager for the Activity Log, along with options for limiting what
     * is shown.  If no limits are provided, the log will not be very useful.
     */
    function showPager($actor, $actee, $notes, $begin, $end, $activities)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = &new DBPager('hms_activity_log','HMS_Activity_Log');
        $pager->db->addOrder('timestamp', 'DESC');

        if(isset($actor))
            $pager->db->addWhere('actor', "%$actor%", 'ILIKE');

        if(isset($actee))
            $pager->db->addWhere('user_id', "%$actee%", 'ILIKE');

        if(isset($notes))
            $pager->db->addWhere('notes', "%$notes%", 'ILIKE');

        // TODO: Begin

        // TODO: End

        if(isset($activities) && !empty($activities))
            $pager->db->addWhere('activity', $activities, 'IN');

        $pager->setModule('hms');
        $pager->setTemplate('admin/log_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage('No log entries found under the limits provided.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerTags');

        return $pager->get();
    }

    function main()
    {
        if(isset($_REQUEST['actee']))
            $actee = $_REQUEST['actee'];

        if(isset($_REQUEST['actor']))
            $actor = $_REQUEST['actor'];

        if(isset($_REQUEST['notes']))
            $notes = $_REQUEST['notes'];

        if(isset($_REQUEST['begin']))
            $begin = null; // TODO: This
        else
            $begin = 0;

        if(isset($_REQUEST['end']))
            $end = null; // TODO: This
        else
            $end = PHP_INT_MAX;

        $activity_map = HMS_Activity_Log::get_activity_mapping();

        $activities = array();

        foreach($activity_map as $i => $t) {
            if(isset($_REQUEST["a$i"]))
                $activities[] = $i;
        }

        return HMS_Activity_Log::showPager($actor, $actee, $notes, $begin, $end, $activities);
    }
}
?>
