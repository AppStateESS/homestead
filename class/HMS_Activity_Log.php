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
    function save()
    {
        if($this->id != 0) {
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
    function log_activity($userid, $activity, $actor, $notes = NULL)
    {
        if( isset($_SESSION['login_as_student']) ) {
            $notes .= " Admin: " . Current_User::getUsername();
        }
        
        $activity_log = new HMS_Activity_Log(NULL, $userid, mktime(), $activity, $actor, $notes);
        $result = $activity_log->save();

        if($result == FALSE || PEAR::isError($result)){
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
                        ACTIVITY_LOTTERY_EXECUTED               => "Lottery process executed");
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
        
        $tpl['ACTEE'] = HMS_Student::get_link($this->get_user_id());

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
     *
     * The static variable lets you switch between a static view of the pager
     * (unsortable with unchangeable limits) that has a link to the main 
     * activity log, or the regular dynamic log.
     */
    function showPager($actor, $actee, $exact, $notes, $begin, $end, $activities, $limit=10, $static=false)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = &new DBPager('hms_activity_log','HMS_Activity_Log');
        
        $pct = ($exact == TRUE) ? '%' : '';

        if(!is_null($actor) && !is_null($actee) && $actor == $actee){
            // Both actor and actee were specified, and they match so use an 'OR'
            // to effectively show all entries for the username specified
            $pager->db->addWhere('actor', "$pct$actor$pct", 'ILIKE', 'OR', 'actor_actee_group');
            $pager->db->addWhere('user_id', "$pct$actee$pct", 'ILIKE', 'OR', 'actor_actee_group');
            $pager->db->setGroupConj('actor_actee_group', 'AND');
        }else if(!is_null($actor) && !is_null($actee)){
            // Both actor and actee were specified, but they don't match so use an 'AND'
            // to get just the specific situation we're looking for
            $pager->db->addWhere('actor', "$pct$actor$pct", 'ILIKE', 'AND', 'actor_actee_group');
            $pager->db->addWhere('user_id', "$pct$actee$pct", 'ILIKE', 'AND', 'actor_actee_group');
            $pager->db->setGroupConj('actor_actee_group', 'AND');
        }else if(!is_null($actor)){
            $pager->db->addWhere('actor', "$pct$actor$pct", 'ILIKE');
        }else if(!is_null($actee)){
            $pager->db->addWhere('user_id', "$pct$actee$pct", 'ILIKE');
        }

        if(!is_null($notes))
            $pager->db->addWhere('notes', "%$notes%", 'ILIKE');

        if($begin != $end && $begin < $end) {
            if(!is_null($begin))
                $pager->db->addWhere('timestamp', $begin, '>');

            if(!is_null($end))
                $pager->db->addWhere('timestamp', $end, '<');
        }
            
        if(!is_null($activities) && !empty($activities))
            $pager->db->addWhere('activity', $activities, 'IN');

        $pager->setModule('hms');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage('No log entries found under the limits provided.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getPagerTags');
        $pager->setOrder('timestamp', 'desc', TRUE);
        $pager->setDefaultLimit($limit);

        if($static){
            $pager->setTemplate('admin/static_activity_log_pager.tpl');
        } else {
            $pager->setTemplate('admin/activity_log_pager.tpl');
        }
        return $pager->get();
    }

    /**
     * Shows filtering options for the log view.  The first argument is usually
     * $_SESSION. The second argument is laid out in the same way, and
     * specifies default values.  If a default value is specified in the second
     * argument, that option will not appear in the filter; this way, if you're
     * in the Student Info thing, you can show the activity log for only that
     * user.
     */
    function showFilters($selection = NULL, $defaults = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');

        $form = &new PHPWS_Form();
        $form->setMethod('get');
        $form->addHidden('module', 'hms');

        // Don't lose our place in any embedded menus
        if(isset($_REQUEST['type']))
            $form->addHidden('type', $_REQUEST['type']);
        else
            $form->addHidden('type', 'activity_log');
        if(isset($_REQUEST['op']))
            $form->addHidden('op', $_REQUEST['op']);
        else
            $form->addHidden('op', 'view');
 
        // Keep the activity log from losing tabs when filters are applied
        if(isset($_REQUEST['tab'])){
            $form->addHidden('tab', $_REQUEST['tab']);
        }       

        $form->addText('actor');
        $form->setLabel('actor', 'Action Performed By:');
        if(isset($selection['actor']))
            $form->setValue('actor', $selection['actor']);

        $form->addText('actee');
        $form->setLabel('actee', 'Action Affected:');
        if(isset($selection['actee']))
            $form->setValue('actee', $selection['actee']);

        // "exact" flag
        $form->addCheck('exact','yes');
        $form->setMatch('exact','yes');
        $form->setLabel('exact','Exact? ');

        $begindate = null;
        $enddate = null;
/*
        if(PHPWS_Form::testDate('begin'))
            $begindate = PHPWS_Form::getPostedDate('begin');
        $form->dateSelect('begin', $begindate, '%b', 10, 10);

        if(PHPWS_Form::testDate('end'))
            $enddate = PHPWS_Form::getPostedDate('end');
        $form->dateSelect('end', $enddate, '%b', 10, 10);*/
        
        $form->addText('notes');
        $form->setLabel('notes', 'Note:');
        if(isset($selection['notes']))
            $form->setValue('notes', $selection['notes']);

        $activities = HMS_Activity_Log::get_activity_mapping();
        foreach($activities as $id => $text) {
            $name = "a$id";
            $form->addCheckbox($name);
            $form->setLabel($name, $text);
            $form->setMatch($name, isset($selection[$name]));
        }

        $form->addSubmit('Refresh');
        
        $tpl = $form->getTemplate();
        $tpl['BEGIN_LABEL'] = 'After:';
        $tpl['END_LABEL'] = 'Before:';
        return PHPWS_Template::process($tpl, 'hms', 'admin/activity_log_filters.tpl');
    }

    function main()
    {
        $actee = NULL;
        if(isset($_REQUEST['actee']) && !empty($_REQUEST['actee']))
            $actee = $_REQUEST['actee'];

        $actor = NULL;
        if(isset($_REQUEST['actor']) && !empty($_REQUEST['actor']))
            $actor = $_REQUEST['actor'];

        $notes = NULL;
        if(isset($_REQUEST['notes']) && !empty($_REQUEST['notes'])) 
            $notes = $_REQUEST['notes'];
        
        $exact = isset($_REQUEST['exact']) ? TRUE : FALSE;

        if(PHPWS_Form::testDate('begin'))
            $begin = PHPWS_Form::getPostedDate('begin');
        else
            $begin = null;

        if(PHPWS_Form::testDate('end'))
            $end = PHPWS_Form::getPostedDate('end');
        else
            $end = null;

        // Sanity Checking
        if($end <= $begin) {
            unset($_REQUEST['begin_year'],
                  $_REQUEST['begin_month'],
                  $_REQUEST['begin_day'],
                  $_REQUEST['end_year'],
                  $_REQUEST['end_month'],
                  $_REQUEST['end_day']);
            $begin = null;
            $end = null;
        }

        $activity_map = HMS_Activity_Log::get_activity_mapping();

        $activities = array();

        foreach($activity_map as $i => $t) {
            if(isset($_REQUEST["a$i"]))
                $activities[] = $i;
        }

        $tags['FILTERS'] = HMS_Activity_Log::showFilters($_REQUEST);
        $tags['CONTENT'] = HMS_Activity_Log::showPager($actor, $actee, $exact, $notes, $begin, $end, $activities);
        return PHPWS_Template::Process($tags, 'hms', 'admin/activity_log_box.tpl');
    }
}
?>
