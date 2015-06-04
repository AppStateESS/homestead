<?php

/**
 * BannerQueueItem - Represents an single item in the BannerQueue
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class BannerQueueItem {

    public $id;
    public $type;
    public $asu_username;
    public $term;
    public $building_code;
    public $bed_code;
    public $meal_plan = 'HOME';
    public $meal_code;
    public $percent_refund;

    public $queued_on;
    public $queued_by;

    public function __construct($id = null, $type = null, Student $student = null, $term = null, HMS_Residence_Hall $hall = null, HMS_Bed $bed = null, $mealPlan = null, $mealCode = null, $percentRefund = null)
    {
        if(!is_null($id) && $id != 0) {
            $this->load();
            return;
        }

        if(is_null($type)){
            return;
        }

        $this->type             = $type;
        $this->asu_username     = strtolower($student->getUsername());
        $this->term             = $term;
        $this->building_code    = $hall->getBannerBuildingCode();
        $this->bed_code         = $bed->getBannerId();
        $this->meal_plan        = $mealPlan;
        $this->meal_code        = $mealCode;
        $this->percent_refund   = $percentRefund;

    }

    public function load()
    {
        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
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
            throw new DatabaseException($result->toString());
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
            throw new DatabaseException($result->toString());
        }

        return TRUE;
    }

    /**
     * Sets up the queuer and the timestamp
     */
    public function stamp()
    {
        $this->queued_on = time();
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

        $soap = SOAP::getInstance(UserStatus::getUsername(), UserStatus::isAdmin()?(SOAP::ADMIN_USER):(SOAP::STUDENT_USER));

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
                // Get the Banner ID from the user name
                // TODO fix this to use BannerID directly
                $bannerId = $soap->getBannerId($this->asu_username);

                $result = $soap->removeRoomAssignment(
                                    $bannerId,
                                    $this->term,
                                    $this->building_code,
                                    $this->bed_code,
                                    $this->percent_refund);

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
