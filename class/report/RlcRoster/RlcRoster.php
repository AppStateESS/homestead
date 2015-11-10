<?php

/**
 * The RLC Roster report.
 * Finds and displays all members of RLCs and anyone who is
 * living in the same room as a member of an RLC.
 *
 * @author Chris Detsch
 * @package hms
 */

class RlcRoster extends Report {

  const friendlyName = 'RLC Member Roster';
  const shortName    = 'RlcRoster';

  private $term;
  private $data;
  private $memberCount;

  public function __construct($id = 0)
  {
      parent::__construct($id);


  }

  public function execute()
  {


    $db = PdoFactory::getPdoInstance();

    $query = "SELECT hms_assignment.banner_id, hms_hall_structure.room_number, hms_hall_structure.hall_name
              FROM hms_assignment
              JOIN hms_hall_structure
                  ON hms_assignment.bed_id = hms_hall_structure.bedid
              WHERE
                hms_assignment.term = :term and
                roomid IN (SELECT room_id
                            FROM hms_learning_community_assignment
                            JOIN hms_learning_community_applications
                                ON hms_learning_community_assignment.application_id = hms_learning_community_applications.id
                            JOIN hms_assignment
                                ON (hms_learning_community_applications.username = hms_assignment.asu_username AND hms_learning_community_applications.term = hms_assignment.term)
                            JOIN hms_bed
                                ON hms_assignment.bed_id = hms_bed.id
                            JOIN hms_room
                                ON hms_bed.room_id = hms_room.id
                            WHERE
                                hms_learning_community_applications.term = :term)
              ORDER BY roomid";

    $stmt = $db->prepare($query);

    $params = array(
        'term' =>$this->term
    );

    $stmt->execute($params);

    $queryResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = array();
    $i = 0;
    $count = 0;

    foreach ($queryResult as $result) {
      $tplVals = array();

      $tplVals['BANNER'] = $result['banner_id'];

      $student = StudentFactory::getStudentByBannerID($result['banner_id'], $this->term);

      $tplVals['USERNAME'] = $student->getUsername();
      $tplVals['FIRST_NAME'] = $student->getFirstName();
      $tplVals['LAST_NAME'] = $student->getLastName();

      $membership = RlcMembershipFactory::getMembership($student, $this->term);

      if($membership)
      {
        $tplVals['COMMUNITY'] = $membership->getRlcName();
        $count++;
      }
      else {
        $tplVals['COMMUNITY'] = '';
      }

      $tplVals['HALL'] = $result['hall_name'];

      $tplVals['ROOM'] = $result['room_number'];


      $results[$i] = $tplVals;
      $i++;
    }

    $this->memberCount = $count;

    $this->data = $results;
  }

  /****************************
   * Accessor/Mutator Methods *
   ****************************/

  public function setTerm($term)
  {
    $this->term = $term;
  }

  public function getTerm()
  {
    return $this->term;
  }

  public function getData()
  {
    return $this->data;
  }

  public function getMemberCount()
  {
    return $this->memberCount;
  }

  public function getCsvColumnsArray()
  {
    return array('Banner Id', 'Username', 'First Name', 'Last Name', 'Community', 'Hall', 'Room');
  }

  public function getCsvRowsArray()
  {
    return $this->data;
  }

  // public function getDefaultOutputViewCmd()
  // {
  //     $cmd = CommandFactory::getCommand('ShowReportCsv');
  //     $cmd->setReportId($this->id);
  //
  //     return $cmd;
  // }

}
