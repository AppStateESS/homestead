<?php

namespace Homestead\report\UnassignedBeds;

/**
 * Unassigned Beds Report
 * Computes the list of all unassigned beds.
 *
 * TODO: Horizontal PDF dispaly
 *
 * @author jbooker
 * @package HMS
 */

class UnassignedBeds extends Report implements iCsvReport {

    const friendlyName = 'Unassigned Beds';
    const shortName = 'UnassignedBeds';

    private $term;

    // Counts
    private $total;
    private $male;
    private $female;
    private $coed;

    private $data;

    public function __construct($id = 0){
        parent::__construct($id);

        $this->totalBeds = 0;
        $this->totalRooms = 0;
        $this->male = 0;
        $this->female = 0;
        $this->coed = 0;

        $this->data = array();
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        if (!isset($this->term) || is_null($this->term)) {
            throw new \InvalidArgumentException('Missing term.');
        }

        // Get all of the residence halls for this term
        $halls = HMS_Residence_Hall::get_halls($this->term);

        $hallRow = array();

        foreach($halls as $hall){
            $hallName = $hall->hall_name;
            $maxOccupancy = $hall->get_number_of_online_nonoverflow_beds();
            $currOccupancy = $hall->get_number_of_assignees();

            $offline = "";
            // If the hall is offline, make a note of that
            if($hall->is_online == 0){
                $offline = '(Offline)';
            }

            $query = "select * from hms_room JOIN (SELECT hms_room.id, count(*) as c FROM hms_residence_hall JOIN hms_floor ON hms_residence_hall.id = hms_floor.residence_hall_id JOIN hms_room ON hms_floor.id = hms_room.floor_id JOIN hms_bed ON hms_room.id = hms_bed.room_id LEFT OUTER JOIN hms_assignment ON hms_bed.id = hms_assignment.bed_id WHERE ( hms_assignment.id IS NULL AND hms_residence_hall.id = '{$hall->id}' AND hms_room.offline = 0 AND hms_room.overflow = 0 AND hms_room.reserved = 0 AND hms_room.private = 0 AND hms_room.parlor = 0 AND hms_room.ra = 0 AND hms_bed.room_change_reserved = 0 AND hms_bed.ra_roommate = 0 AND hms_bed.international_reserved = 0) GROUP BY hms_room.id) as foo ON foo.id = hms_room.id ORDER BY hms_room.room_number";

            $results = \PHPWS_DB::getAll($query);

            $maleRoomList = array();
            $femaleRoomList = array();
            $coedRoomList = array();

            // each room on this hall
            foreach($results as $room){
                $this->totalRooms++;
                $roomNum = $room['room_number'];

                // If the room has more than one avaialble bed, note that in the room number
                if($room['c'] > 1){
                    $roomNum .= '(x' . $room['c'] . ')';
                }

                // catagorize it by gender
                if($room['gender_type'] == MALE){
                    $maleRoomList[] = $roomNum;
                    $this->male += $room['c'];
                    $this->totalBeds += $room['c'];
                }else if($room['gender_type'] == FEMALE){
                    $femaleRoomList[] = $roomNum;
                    $this->female += $room['c'];
                    $this->totalBeds += $room['c'];
                }else if($room['gender_type'] == COED || $room['gender_type'] == AUTO){
                    $coedRoomList[] = $roomNum;
                    $this->coed += $room['c'];
                    $this->totalBeds += $room['c'];
                }else{
                    throw new \InvalidArgumentException('Bad room gender. Room id: ' . $room['id']);
                }
            }

            $hallRow[] = array('hallName'=>$hallName . $offline, 'maxOccupancy'=>$maxOccupancy, 'currOccupancy'=>$currOccupancy, 'maleRooms'=>implode(", ", $maleRoomList), 'femaleRooms'=>implode(", ", $femaleRoomList), 'coedRooms'=>implode(", ", $coedRoomList));
        }

        $this->data = $hallRow;
    }

    public function getCsvColumnsArray()
    {
        return array_keys($this->data[0]);
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

    public function getTotalBeds(){
        return $this->totalBeds;
    }

    public function getTotalRooms(){
        return $this->totalRooms;
    }

    public function getMale(){
        return $this->male;
    }

    public function getFemale(){
        return $this->female;
    }

    public function getCoed(){
        return $this->coed;
    }
}
