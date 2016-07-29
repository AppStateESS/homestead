<?php

/**
* Controller class for retrieving the assignment history for a student in JSON format
*
* @author Chris Detsch
* @package hms
*/

class AjaxEditAssignmentHistoryCommand {

    public function __construct()
    {

    }

    public function execute()
    {
        $reason = $_REQUEST['reason'];
        $banner = $_REQUEST['bannerId'];
        $term   = $_REQUEST['term'];

        $assignment = HMS_Assignment::getAssignmentByBannerId($banner, $term);
        $id = $assignment->getId();

        $this->updateAssignmentReason($reason, $id);
        $this->updateHistoryReason($reason, $id);

        echo json_encode("success");
        exit;
    }

    public function updateAssignmentReason($reason, $id)
    {
        $db    = PdoFactory::getPdoInstance();
        $query = 'UPDATE hms_assignment set reason = :newReason where id = :id';
        $stmt  = $db->prepare($query);

        $params = array(
                            'newReason' => $reason,
                            'id'        => $id
        );

        $stmt->execute($params);
    }

    public function updateHistoryReason($reason, $id)
    {
        $db    = PdoFactory::getPdoInstance();
        $query = 'UPDATE hms_assignment_history set assigned_reason = :newReason where id = :id';
        $stmt  = $db->prepare($query);

        $params = array(
                            'newReason' => $reason,
                            'id'        => $id
        );

        $stmt->execute($params);
    }
}
