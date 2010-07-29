<?php

/**
 * Queues up assignments so if we can't SOAP it over to Banner, Housing
 * can still do their jobs
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class HMS_Banner_Queue {

    var $id            = 0;
    var $type          = 0;
    var $asu_username  = null;
    var $building_code = null;
    var $bed_code      = null;
    var $meal_plan     = 'HOME';
    var $meal_code     = 0;
    var $term          = 0;
    var $queued_on     = 0;
    var $queued_by     = null;

    public function HMS_Banner_Queue($id = 0)
    {
        if(!$id) {
            return;
        }

        $this->id = $id;
        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(!$result || PHPWS_Error::logIfError($result)) {
            $this->id = 0;
        }
    }

    /**
     * Resets this process item to zero values
     */
    public function reset()
    {
        $this->id        = 0;
        $this->queued_on = 0;
        $this->queued_by = null;
    }

    /**
     * Sets up the queuer and the timestamp
     */
    public function stamp()
    {
        $this->queued_on = mktime();
        $this->queued_by = Current_User::getId();
    }

    /**
     * Saves this queue item
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_banner_queue');

        $this->stamp();

        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)) {
            return FALSE;
        }
        return TRUE;
    }

    public function set_id($id) {
        $this->id = $id;
    }

    public function get_id() {
        return $this->id;
    }

    public function delete() {
        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)) {
            return $result;
        }

        return TRUE;
    }

    /**
     * Queues a Create Assignment
     */
    public function queue_create_assignment($username, $term, $bldg, $bed, $mealplan, $mealcode)
    {
        $entry                = new HMS_Banner_Queue();
        $entry->type          = BANNER_QUEUE_ASSIGNMENT;
        $entry->asu_username  = strtolower($username);
        $entry->building_code = $bldg;
        $entry->bed_code      = $bed;
        $entry->meal_plan     = $mealplan;
        $entry->meal_code     = $mealcode;
        $entry->term          = $term;

        if(HMS_Banner_Queue::process_immediately($term)) {
            return $entry->process();
        }

        if(!$entry->save())
        return "DB Error";

        return 0;
    }

    /**
     * Queues a Remove Assignment
     *
     * NOTE: If the queue contains a Create Assignment for the same
     * user to the same room, this will NOT queue a room assignment,
     * but rather will delete the original assignment, UNLESS the
     * $force_queue flag is set.  The $force_queue flag being true will
     * queue a removal no matter what.
     *
     * MORE NOTE: If this requires immediate processing because banner
     * commits are enabled, the it will be sent straight to Banner,
     * and so the force_queue flag will be ignored.
     */
    public function queue_remove_assignment($username, $term, $bldg, $bed, $force_queue = FALSE)
    {
        $entry                = new HMS_Banner_Queue();
        $entry->type          = BANNER_QUEUE_REMOVAL;
        $entry->asu_username  = strtolower($username);
        $entry->building_code = $bldg;
        $entry->bed_code      = $bed;
        $entry->term          = $term;

        if(HMS_Banner_Queue::process_immediately($term)) {
            return $entry->process();
        }

        if($force_queue === TRUE) {
            if(!$entry->save())
            return "DB Error";
            return 0;
        }

        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('type',          BANNER_QUEUE_ASSIGNMENT);
        $db->addWhere('asu_username',  $username);
        $db->addWhere('building_code', $bldg);
        $db->addWhere('bed_code',      $bed);
        $db->addWhere('term',          $term);
        $result = $db->count();

        if(PHPWS_Error::logIfError($result)) {
            return "DB Error";
        }

        if($result == 0) {
            if(!$entry->save())
            return "DB Error";
            return 0;
        }

        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)) {
            return 'DB Error';
        }

        return 0;
    }

    /**
     * Returns TRUE if an action should be processed immediately (queue is disabled)
     * or FALSE if an action should be queued
     */
    public function process_immediately($termCode) {
        $term = new Term($termCode);
        $queue = $term->getBannerQueue();
        return $queue == 0;
    }

    /**
     * Processes a queued item.  This can be something actually queued,
     * or an immediate processing because the queue is disabled.
     */
    public function process()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'SOAP.php');

        $soap = SOAP::getInstance();

        $result = -1;

        switch($this->type) {
            case BANNER_QUEUE_ASSIGNMENT:
                $result = $soap->reportRoomAssignment(
                $this->asu_username,
                $this->term,
                $this->building_code,
                $this->bed_code,
                    'HOME',
                $this->meal_code);
                if($result == "0") {
                    HMS_Activity_Log::log_activity(
                    $this->asu_username,
                    ACTIVITY_ASSIGNMENT_REPORTED,
                    Current_User::getUsername(),
                    $this->term . ' ' .
                    $this->building_code . ' ' .
                    $this->bed_code . ' ' .
                        'HOME' . ' ' .
                    $this->meal_code);
                }
                break;
            case BANNER_QUEUE_REMOVAL:
                $result = $soap->removeRoomAssignment(
                $this->asu_username,
                $this->term,
                $this->building_code,
                $this->bed_code);
                if($result == "0") {
                    HMS_Activity_Log::log_activity(
                    $this->asu_username,
                    ACTIVITY_REMOVAL_REPORTED,
                    Current_User::getUsername(),
                    $this->term . ' ' .
                    $this->building_code . ' ' .
                    $this->bed_code . ' ');
                }
                break;
        }

        return $result;
    }

    /********************
     * Static Functions *
     ********************/
    public function processAll($term)
    {
        // TODO: Process All.  Return an array of username=>error if there were
        // any.  Don't stop on errors, just tally them up.  Return TRUE if
        // there were no errors.

        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('term', $term);
        $db->addOrder('id');
        $items = $db->getObjects('HMS_Banner_Queue');

        $errors = array();
        foreach($items as $item) {
            $result = null;

            try{
                $result = $item->process();
            }catch(Exception $e){
                $error = array();
                $error['id'] = $item->id;
                $error['username'] = $item->asu_username;
                $error['code'] = $result;
                $errors[] = $error;
            }

            if($result === FALSE) {
                $error = array();
                $error['id'] = $item->id;
                $error['username'] = $item->asu_username;
                $error['code'] = $result;
                $errors[] = $error;
            } else {
                $r = $item->delete();
            }
        }

        if(empty($errors))
        return TRUE;

        return $errors;
    }
}

?>