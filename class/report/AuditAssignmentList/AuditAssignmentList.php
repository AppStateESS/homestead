<?php

namespace Homestead\report\AuditAssignmentList;

/**
 *
 * @author Jeremy Booker
 */

class AuditAssignmentList extends Report implements iCsvReport {
    const friendlyName = 'Audit Assignment List';
    const shortName = 'AuditAssignmentList';

    private $term;
    private $rows;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'PdoFactory.php');

        $db = PdoFactory::getInstance()->getPdo();

        $query = "SELECT hms_assignment.term, hms_assignment.banner_id, hms_hall_structure.banner_building_code, hms_hall_structure.banner_id as bed_code, hms_new_application.meal_plan FROM hms_assignment JOIN hms_hall_structure ON hms_assignment.bed_id = hms_hall_structure.bedid LEFT OUTER JOIN hms_new_application ON (hms_assignment.banner_id = hms_new_application.banner_id AND hms_assignment.term = hms_new_application.term) WHERE hms_assignment.term IN (:term) ORDER BY hms_assignment.term";

        $stmt = $db->prepare($query);
        $stmt->execute(array('term'=>$this->term));
        $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCsvColumnsArray()
    {
        return array('Term', 'Banner ID', 'Building Code', 'Bed ID', 'Meal Code');
    }

    public function getCsvRowsArray()
    {
        return $this->rows;
    }

    public function getDefaultOutputViewCmd()
    {
        $cmd = CommandFactory::getCommand('ShowReportCsv');
        $cmd->setReportId($this->id);

        return $cmd;
    }
}
