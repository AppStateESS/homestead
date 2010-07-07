<?php

PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

abstract class AssignmentStrategy {

    protected $term;

    public function __construct($term)
    {
        $this->term  = $term;
    }

    abstract function doAssignment($pair);

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

        $pair->setBed1($room->__toString());

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

        $pair->setBed2($room->__toString());
    }

    // TODO: this, better?
    protected function roomSearch($gender = FALSE, $lifestyle = FALSE, $pbuilding = FALSE, $pfloor = FALSE, $proom = FALSE)
    {
        PHPWS_Core::initCoreClass('DB2.php');

        $sub = new DB2;

        $ass   = $sub->addTable('hms_assignment');
        $bed   = $sub->addTable('hms_bed');
        $room  = $sub->addTable('hms_room');
        $floor = $sub->addTable('hms_floor');
        $hall  = $sub->addTable('hms_residence_hall');
        $ass->showAllFields(false);
        $bed->showAllFields(false);
        $room->showAllFields(false);
        $floor->showAllFields(false);
        $hall->showAllFields(false);

        $bbc = $hall->getField('banner_building_code');
        $rid = $room->getField('id');
        $room->addField($rid);
        $hall->addField($bbc);

        $sub->join($hall->getField('id'), $floor->getField('residence_hall_id'));
        $sub->join($floor->getField('id'), $room->getField('floor_id'));
        $sub->join($room->getField('id'), $bed->getField('room_id'));
        $sub->join($bed->getField('id'), $ass->getField('bed_id'), 'left outer');

        // Make sure everything is online
        $hall->addWhere('is_online', 1);
        $floor->addWhere('is_online', 1);
        $room->addWhere('is_online', 1);

        // Make sure nothing is reserved
        $room->addWhere('is_reserved', 0);
        $room->addWhere('is_medical', 0);

        // Don't get RA beds
        $room->addWhere('ra_room', 0);

        // Don't get lobbies
        $room->addWhere('is_overflow', 0);

        // Don't get private rooms
        $room->addWhere('private_room', 0);

        // Don't get RLC floors
        $floor->addWhere('rlc_id', NULL, 'is');

        // Term
        $room->addWhere('term', $this->term);

        // Count Free Beds
        $ass->addWhere('asu_username', NULL, 'is');
        $bid = $bed->getField('id');
        $sub->addExpression("count($bid)", 'bed_count');
        $sub->setGroupBy(array($bbc, $rid));

        // Limit to selection
        if($gender !== FALSE) {
            $room->addWhere('gender_type', $gender);
        }
        if($lifestyle !== FALSE) {
            $hall->addWhere('gender_type', 2, ($lifestyle == 2 ? '=' : '!='));
        }
        if($pbuilding !== FALSE) {
            $hall->addWhere('banner_building_code', $pbuilding);
        }
        if($pfloor !== FALSE) {
            $floor->addWhere('floor_number', $pfloor);
        }
        if($proom !== FALSE) {
            $room->addWhere('room_number', $proom);
        }

        // Select only rooms with two free beds
        $main = new DB2;
        $sel = $main->addSubSelect($sub, 'sub');
        $room = $main->addTable('hms_room');
        $main->join($sel->getField('id'), $room->getField('id'));
        $sel->addField('banner_building_code');
        $sel->addWhere('bed_count', 2);

        // Random order
        $room->addOrderBy($main->getExpression('random()'));

        // We only need one
        $main->setLimit(1);

        $room = new HMS_Room();
        $result = $main->select(DB2_ROW);
        if(is_null($result)) return null;

        PHPWS_Core::plugObject($room, $result);

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
