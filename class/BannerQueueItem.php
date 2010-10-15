<?php

/**
 * BannerQueueItem - Represents an single item in the BannerQueue
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class BannerQueueItem {

    public $id            = 0;
    public $type          = 0;
    public $asu_username  = null;
    public $building_code = null;
    public $bed_code      = null;
    public $meal_plan     = 'HOME';
    public $meal_code     = 0;
    public $term          = 0;
    public $queued_on     = 0;
    public $queued_by     = null;

    public function __construct($id = null, $type = null, Student $student = null, $term = null, HMS_Residence_Hall $hall = null, HMS_Bed $bed = null, $mealPlan = null, $mealCode = null)
    {
        if(!is_null($id) && $id != 0) {
            $this->load();
            return;
        }

        if(is_null($type)){
            return;
        }

        $this->type          = $type;
        $this->asu_username  = strtolower($student->getUsername());
        $this->building_code = $hall->getBannerBuildingCode();
        $this->bed_code      = $bed->getBannerId();
        $this->meal_plan     = $mealPlan;
        $this->meal_code     = $mealCode;
        $this->term          = $term;

    }

    public function load()
    {
        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }

    /**
     * Saves this queue item
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_banner_queue');

        $this->stamp();

        $result = $db->saveObject($this);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Deletes this item from the queue
     */
    public function delete() {
        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return FALSE;
        }

        return TRUE;
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
     * Processes a queued item.  This can be something actually queued,
     * or an immediate processing because the queue is disabled.
     */
    public function process()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'SOAP.php');

        $soap = SOAP::getInstance();

        $result = null;

        switch($this->type) {
            case BANNER_QUEUE_ASSIGNMENT:
                $result = $soap->reportRoomAssignment(
                $this->asu_username,
                $this->term,
                $this->building_code,
                $this->bed_code,
                    'HOME',
                $this->meal_code);
                if($result === TRUE) {
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
                if($result === TRUE) {
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
}
?>
