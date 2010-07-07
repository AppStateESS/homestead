<?php

PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

abstract class AssignmentStrategy {

    protected $term;

    public function __construct($term)
    {
        $this->term  = $term;
    }

    abstract function doAssignment($pair);

    public function init(&$pairs)
    {
    }

    protected function allowed(AssignmentPairing $pair, HMS_Room $room)
    {
        // If the genders don't match...
        if($pair->getGender() != $room->gender_type) {
            // If they don't want to live in a coed room...
            if($room->gender_type != 2 || $pair->getLifestyle() != 2) {
                return false;
            }
            // Otherwise, we might be okay.
        }

        // TODO: More checks?
        return true;
    }

    protected function assign(AssignmentPairing $pair, HMS_Room $room)
    {
        if(!$this->allowed($pair, $room)) {
            PHPWS_Core::initModClass('hms', 'exception/AssignmentException.php');
            throw new AssignmentException('Cannot assign ' . $pair->__tostring() . ' to ' . $room->__tostring());
        }

        echo get_class($this) . " is assigning " . $pair->__tostring() . " to room " . $room->__tostring() . "\n";


        // Actually assign the given pairing to the given room
        try{
            $application = HousingApplication::getApplicationByUser($pair->getStudent1()->getUsername(), $this->term);

            if(is_null($application)){
                $student1MealPlan = BANNER_MEAL_STD;
            }else{
                $student1MealPlan = $application->getMealPlan();
            }
            HMS_Assignment::assignStudent($pair->getStudent1(), $this->term, $room->id, NULL, $student1MealPlan, 'Auto-assigned');
        }catch(Exception $e){
            echo "Could not assign '{$pair->getStudent1()->getUsername()}': {get_class($e)}: {$e->getMessage()}<br />\n";
        }

        try{
            $application = HousingApplication::getApplicationByUser($pair->getStudent2()->getUsername(), $this->term);

            if(is_null($application)){
                $student2MealPlan = BANNER_MEAL_STD;
            }else{
                $student2MealPlan = $application->getMealPlan();
            }
            HMS_Assignment::assignStudent($pair->getStudent2(), $this->term, $room->id, NULL, $student2MealPlan, 'Auto-assigned');
        }catch(Exception $e){
            echo "Could not assign '{$pair->getStudent2()->getUsername()}': " . get_class($e) . ": {$e->getMessage()}<br />\n";
        }
    }

    // TODO: this, better?
    protected function roomSearch($gender = FALSE, $lifestyle = FALSE, $building = FALSE, $floor = FALSE, $room = FALSE)
    {
        $db = new PHPWS_DB('hms_room');
        $db->addColumn('hms_room.*');
        $db->addColumn('hms_residence_hall.banner_building_code');

        // Join other tables so we can do the other 'assignable' checks
        $db->addJoin('LEFT', 'hms_room', 'hms_bed', 'id', 'room_id');
        $db->addJoin('LEFT', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        // Term
        $db->addWhere('hms_room.term', $this->term);

        // Only get rooms with free beds
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addWhere('hms_assignment.asu_username', NULL);

        // Make sure everything is online
        $db->addWhere('hms_room.is_online', 1);
        $db->addWhere('hms_floor.is_online', 1);
        $db->addWhere('hms_residence_hall.is_online', 1);

        // Make sure nothing is reserved
        $db->addWhere('hms_room.is_reserved', 0);
        $db->addWhere('hms_room.is_medical', 0);

        // Don't get RA beds
        $db->addWhere('hms_room.ra_room', 0);

        // Don't get lobbies
        $db->addWhere('hms_room.is_overflow', 0);

        // Don't get private rooms
        $db->addWhere('hms_room.private_room', 0);

        // Don't get rooms on floors reserved for an RLC
        $db->addWhere('hms_floor.rlc_id', NULL);

        // Random Order
        $db->addOrder('random()');

        // We only need one
        $db->setLimit(1);

        // Limit to selection
        if($gender !== FALSE) {
            $db->addWhere('gender_type', $gender);
        }
        if($lifestyle !== FALSE) {
            $db->addWhere('hms_residence_hall.gender_type', 2, ($lifestyle == 2 ? '=' : '!='));
        }
        if($building !== FALSE) {
            $db->addWhere('hms_residence_hall.banner_building_code', $building);
        }
        if($floor !== FALSE) {
            $db->addWhere('hms_floor.floor_number', $floor);
        }
        if($room !== FALSE) {
            $db->addWhere('room_number', $room);
        }

        $room = new HMS_Room();
        $result = $db->loadObject($room);

        if($result !== TRUE) {
            if($result === FALSE) {
                return null;
            }
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->getMessage());
        }

        return $room;
    }

    // This thing tries the specified gender first, then looks for coed rooms.  It respects
    // the lifestyle option, so if a student has picked single-gender but somehow actually got
    // to an assigment strategy that uses this function, we'll try not to assign them to a
    // gender-switchable room.  Not to say they couldn't end up in a coed floor or res hall
    // whose room genders are staticly defined.  This really shouldn't be used in single-gender
    // assignment strategies.
    protected function roomSearchPlusCoed($gender = FALSE, $lifestyle = FALSE, $building = FALSE, $floor = FALSE, $room = FALSE)
    {
        $room = $this->roomSearch($gender, $lifestyle, $building, $floor, $room);
        if(is_null($room) && $lifestyle == 2 && $gender != 2) {
            $room = $this->roomSearch(2, $lifestyle, $building, $floor, $room);
        }
        return $room;
    }
}

?>
