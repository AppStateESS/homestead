<?php

namespace Homestead\report\MismatchedRoommates;

/*
 * Mismatched Roommates Report
 * Lists all of the students assigned to rooms that do not contain their roommate.
 *
 * @author Chris Detsch
 * @package HMS
 */

class MismatchedRoommates extends Report
{

    const friendlyName = 'Mismatched Roommates';
    const shortName = 'MismatchedRoommates';

    private $term;

    private $data;

    private $mismatchCount;

    public function __construct($id = 0)
    {
        parent::__construct($id);

        $this->data = array();

        $this->mismatchCount = 0;
    }

    public function execute()
    {
      PHPWS_Core::initModClass('hms', 'PdoFactory.php');

      $db = PdoFactory::getInstance()->getPdo();

      $query = "SELECT requestor, requestee from hms_roommate
                  LEFT OUTER JOIN
                    (select asu_username, hms_room.id as rmid
                      FROM hms_assignment
                      JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id
                      JOIN hms_room ON hms_bed.room_id = hms_room.id
                      WHERE hms_assignment.term = :term)
                as requestor_room_id ON requestor_room_id.asu_username = requestor
                  LEFT OUTER JOIN
                    (select asu_username, hms_room.id as rmid
                      FROM hms_assignment
                      JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id
                      JOIN hms_room ON hms_bed.room_id = hms_room.id
                      WHERE hms_assignment.term = :term)
                AS requestee_room_id ON requestee_room_id.asu_username = requestee
                  WHERE hms_roommate.term = :term and confirmed = 1 AND
                  requestor_room_id.rmid != requestee_room_id.rmid";

      // $query = "SELECT hms_assignment.term, hms_assignment.banner_id, hms_hall_structure.banner_building_code, hms_hall_structure.banner_id
      //             as bed_code, hms_new_application.meal_plan FROM hms_assignment
      //               JOIN hms_hall_structure ON
      //                 hms_assignment.bed_id = hms_hall_structure.bedid
      //               LEFT OUTER JOIN hms_new_application ON
      //                 (hms_assignment.banner_id = hms_new_application.banner_id
      //                 AND hms_assignment.term = hms_new_application.term)
      //               WHERE hms_assignment.term IN (:term) ORDER BY hms_assignment.term";

      $stmt = $db->prepare($query);
      $stmt->execute(array('term'=>$this->term));
      $queryResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $result = array();
      $i = 0;

      foreach($queryResult as $row)
      {
        $requestee = StudentFactory::getStudentByUsername($row['requestee'], $this->term);
        $requestor = StudentFactory::getStudentByUsername($row['requestor'], $this->term);

        $row['requestee_banner'] = $requestee->getBannerId();
        $row['requestor_banner'] = $requestor->getBannerId();

        $row['requestee_name'] = $requestee->getFullName();
        $row['requestor_name'] = $requestor->getFullName();
        $result[$i] = $row;
        $i++;
      }

      $this->data = $result;
      $this->mismatchCount = $i;

    }



    public function getCsvColumnsArray()
    {
        if(sizeof($this->data) === 0){
            return array();
        } else {
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

    public function getMismatchCount()
    {
      return $this->mismatchCount;
    }

}
