<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;
use \PHPWS_Error;
use \PHPWS_DB;

/**
 * BannerQueueItem - Represents an single item in the BannerQueue
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class BannerQueueItem {

    public $id;
    public $type;
    public $banner_id;
    public $asu_username;
    public $term;
    public $building_code;
    public $bed_code;
    public $percent_refund;

    public $queued_on;
    public $queued_by;

    public function __construct($id = null, $type = null, Student $student = null, $term = null, HMS_Residence_Hall $hall = null, HMS_Bed $bed = null, $percentRefund = null)
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
        $this->percent_refund   = $percentRefund;
        $this->banner_id        = $student->getBannerId();

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
        $this->queued_by = \Current_User::getId();
    }

    /**
     * Processes a queued item.  This can be something actually queued,
     * or an immediate processing because the queue is disabled.
     */
    public function process()
    {
        $soap = SOAP::getInstance(UserStatus::getUsername(), UserStatus::isAdmin()?(SOAP::ADMIN_USER):(SOAP::STUDENT_USER));

        $result = null;

        switch($this->type) {
            case BANNER_QUEUE_ASSIGNMENT:
                $result = $soap->createRoomAssignment(
                                    $this->banner_id,
                                    $this->term,
                                    $this->building_code,
                                    $this->bed_code);
                if($result === TRUE) {
                    HMS_Activity_Log::log_activity(
                                    $this->asu_username,
                                    ACTIVITY_ASSIGNMENT_REPORTED,
                                    \Current_User::getUsername(),
                                    $this->term . ' ' .
                                    $this->building_code . ' ' .
                                    $this->bed_code);
                }
                break;
            case BANNER_QUEUE_REMOVAL:
                $result = $soap->removeRoomAssignment(
                                    $this->banner_id,
                                    $this->term,
                                    $this->building_code,
                                    $this->bed_code,
                                    $this->percent_refund);

                if($result === TRUE) {
                    HMS_Activity_Log::log_activity(
                                    $this->asu_username,
                                    ACTIVITY_REMOVAL_REPORTED,
                                    \Current_User::getUsername(),
                                    $this->term . ' ' .
                                    $this->building_code . ' ' .
                                    $this->bed_code . ' ');
                }
                break;
        }

        return $result;
    }
}
