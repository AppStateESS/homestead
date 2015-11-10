<?php

/**
 * Assignment Type By Floor Report class
 *
 * Calculates the number of assignments of each type (reason) for each
 * floor in the given term.
 *
 * @author jbooker
 * @package HMS
 */
class AssignmentTypeByFloor extends Report {

    const friendlyName = 'Assignment Type By Floor';
    const shortName    = 'AssignmentTypeByFloor';

    private $term;

    private $halls;

    private $hallCounts;
    private $floorCounts;

    public function __construct($id = 0)
    {
        parent::__construct($id);

        $this->hallCounts  = array();
        $this->floorCounts = array();
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        foreach($this->getHalls() as $hall){
            $hallCount = PHPWS_DB::getAssoc("select reason, count(*) from hms_assignment JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id JOIN hms_room ON hms_bed.room_id = hms_room.id JOIN hms_floor ON hms_room.floor_id = hms_floor.id JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id where hms_residence_hall.id = {$hall->getId()} AND hms_assignment.term = {$this->getTerm()} group by reason order by reason");

            if(PHPWS_Error::isError($hallCount)){
                throw new DatabaseException($hallCount->toString());
            }

            $this->hallCounts[$hall->getId()] = $hallCount;

            $floors = $hall->get_floors();

            foreach($floors as $floor){
                $floorCount = PHPWS_DB::getAssoc("select reason, count(*) from hms_assignment JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id JOIN hms_room ON hms_bed.room_id = hms_room.id JOIN hms_floor ON hms_room.floor_id = hms_floor.id where hms_floor.id = {$floor->getId()} AND hms_assignment.term = {$this->getTerm()} group by reason order by reason");

                if(PHPWS_Error::isError($floorCount)){
                    throw new DatabaseException($floorCount->toString());
                }

                $this->floorCounts[$floor->getId()] = $floorCount;
            }
        }
    }

    /****************************
     * Accessor/Mutator Methods *
    ****************************/

    public function setTerm($term){
        $this->term = $term;
    }

    public function getTerm(){
        return $this->term;
    }

    public function getHalls(){
        return ResidenceHallFactory::getHallsForTerm($this->getTerm());
    }

    public function getCountsForHall(HMS_Residence_Hall $hall){
        return $this->hallCounts[$hall->getId()];
    }

    public function getCountsForFloor(HMS_Floor $floor){
        return $this->floorCounts[$floor->getId()];
    }
}
