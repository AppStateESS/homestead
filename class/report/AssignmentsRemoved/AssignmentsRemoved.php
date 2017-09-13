<?php

PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class AssignmentsRemoved extends Report implements iCsvReport {

    const friendlyName = 'Assignments Removed';
    const shortName = 'AssignmentsRemoved';

    private $term;
    private $rows;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function execute()
    {
        $db = PDOFactory::getPdoInstance();

        $query = "SELECT
                    hms_assignment_history.banner_id,
                    assigned_on,
                    assigned_by,
                    assigned_reason,
                    removed_on,
                    removed_by,
                    removed_reason,
                    gender,
                    hms_new_application.application_term,
                    student_type
                FROM hms_assignment_history
                LEFT OUTER JOIN hms_new_application ON (
                    hms_new_application.banner_id = hms_assignment_history.banner_id AND
                    hms_new_application.term =
                        (SELECT max(term) FROM hms_new_application
                            WHERE term <= hms_assignment_history.term AND
                            banner_id = hms_assignment_history.banner_id
                        )
                )
                WHERE
                    removed_reason IS NOT NULL AND
                    removed_reason NOT IN ('ureassign','uchange') AND
                    hms_assignment_history.term = :term";

        $stmt = $db->prepare($query);
        $stmt->execute(array('term' => $this->getTerm()));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $results = $stmt->fetchAll();

        foreach($results as $row){
            $tempArray = $row;

            $tempArray['assigned_on'] = date('m/d/Y', $row['assigned_on']);
            $tempArray['removed_on'] = date('m/d/Y', $row['removed_on']);
            $tempArray['gender'] = HMS_Util::formatGender($row['gender']);

            $this->rows[] = $tempArray;
        }
    }

    public function getCsvColumnsArray()
    {
        return array('Banner Id', 'Assigned Date', 'Assigned By', 'Assigned Reason', 'Removed Date', 'Removed By', 'Removed Reason', 'Gender', 'Application Term', 'Student Type');
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
