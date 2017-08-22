<?php

namespace Homestead;

/**
 * StudentAssignmentHistory.php
 *
 * @author Adam D. Dixon
 */

PHPWS_Core::initModClass('hms', 'AssignmentHistory.php');
PHPWS_Core::initModClass('hms', 'Term.php');

class StudentAssignmentHistory extends ArrayObject{

    private $bannerId;
    private $assignmentHistory;

    // http://weierophinney.net/matthew/archives/131-Overloading-arrays-in-PHP-5.2.0.html
    public function __construct($bannerId) {

        if(is_null($bannerId)){
            throw InvalidArgumentException('Missing id.');
        }else{
            $this->bannerId = $bannerId;
        }

        $this->assignmentHistory = array();

        $this->init();
    }

    public function getHistory() {
        return $this->assignmentHistory;
    }

    /**
     * initialize this object (fill the array) with assignment histories
     *
     * @param int $bannerID banner id of student
     * @param int $term term to be searching
     * @return boolean flag to signal if the initialization was a success
     */
    private function init () {
        $db = new \PHPWS_DB('hms_assignment_history');
        $db->addWhere('banner_id', $this->bannerId);
        $db->loadClass('hms', 'AssignmentHistory.php');
        $db->addOrder(array('term DESC', 'assigned_on DESC'));
        $result = $db->getObjects('AssignmentHistory');

        if(\PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if (isset($result)) {
            $this->assignmentHistory = $result;
        }

        return true;
    }

    /**
     * adds an assignment to this object's array
     *
     * @param AssignmentHistory an assignment history object
     * @return boolean result of addition
     */
    public function add(AssignmentHistory $assignmentHistory) {
        $id = $assignmentHistory->getID();

        if(isset($id)) {
            $this->assignmentHisotry[$assignmentHisotry->getID()] = $assignmentHistory;
            return true;
        }

        return false;
    }

    /**
     * removes an assignment from this object's array
     *
     * @param AssignmentHistory $assignmentHistory an assignment history object
     * @return boolean|AssignmentHistory false if failed or the removed assignment object if success
     */
    public function remove(AssignmentHistory $assignmentHistory) {
        $id = $assignmentHistory->getID();

        if (isset($this->theArray[$id])){
            $rObject = $this->theArray[$id];
            unset($this->theArray[$id]);
            return $rObject;
        }

        return false;
    }

    /**
     * Static function to allow direct pull of assignments
     * when passed banner id
     *
     * @param int $banner_id the student's banner ID
     * @return array assignments associated with student
     */
    public static function getAssignments($bannerId) {
        $sah = new StudentAssignmentHistory($bannerId);
        return $sah;
    }
}
