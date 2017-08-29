<?php

namespace Homestead\Report\NoShowList;

/**
 * No-show List report
 * Lists all of the students who are currently assigned, but have never checked in
 *
 * @author jbooker
 * @package HMS
 */

class NoShowList extends Report implements iCsvReport {

    const friendlyName = 'Check-in - List of No-shows';
    const shortName = 'NoShowList';

    private $term;

    // Counts
    private $total;

    private $data;

    public function __construct($id = 0){
        parent::__construct($id);

        $this->total = 0;

        $this->data = array();

    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'PdoFactory.php');

        $db = PdoFactory::getInstance()->getPdo();

        $query = 'SELECT hms_assignment.banner_id, hms_assignment.reason,
                  hms_hall_structure.hall_name, hms_hall_structure.room_number, hms_hall_structure.bed_letter
                  FROM hms_assignment
                  JOIN hms_hall_structure ON hms_assignment.bed_id = hms_hall_structure.bedid
                  WHERE hms_assignment.banner_id
                  NOT IN (SELECT hms_assignment.banner_id
                          FROM hms_assignment
                          JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id
                          JOIN hms_checkin ON hms_bed.persistent_id = hms_checkin.bed_persistent_id
                          WHERE hms_assignment.bed_id = hms_checkin.bed_id
                          AND hms_assignment.banner_id = hms_checkin.banner_id
                          AND hms_assignment.term = :term)
                  AND hms_assignment.term = :term';

        $stmt = $db->prepare($query);
        $stmt->execute(array('term'=>$this->term));
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rows = array();
        $i = 0;


        foreach($results as $row){
            $rowVals = array();

            $student = StudentFactory::getStudentByBannerID($row['banner_id'], $this->term);

            $rowVals['banner_id'] = $row['banner_id'];
            $rowVals['username']  = $student->getUsername();
            $rowVals['name'] = $student->getFullName();
            $rowVals['class'] = $student->getClass();
            $rowVals['reason'] = constant($row['reason']);
            $rowVals['hall_name'] = $row['hall_name'];
            $rowVals['room_number'] = $row['room_number'];
            $rowVals['bed_letter'] = $row['bed_letter'];

            $rows[$i] = $rowVals;
            $i++;
        }
        $this->total = $i;
        $this->data = $rows;

    }

    public function getCsvColumnsArray()
    {
      if($this->total != 0)
      {
        return array_keys($this->data[0]);
      }
    }

    public function getCsvRowsArray(){
        return $this->data;
    }

    public function getData(){
        return $this->data;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getTotal(){
        return $this->total;
    }
}
