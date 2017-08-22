<?php

namespace Homestead;

PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

abstract class AssignmentStrategy {

    protected $term;

    public function __construct($term)
    {
        $this->term  = $term;
    }

    public abstract function doAssignment($pair);

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
            HMS_Assignment::assignStudent($pair->getStudent1(), $this->term, $room->id, NULL, 'Auto-assigned', false, ASSIGN_FR_AUTO);
        }catch(Exception $e){
            echo "Could not assign '{$pair->getStudent1()->getUsername()}': {get_class($e)}: {$e->getMessage()}<br />\n";
        }

        $pair->setBed1($room->__toString());

        // Check for a meal plan, create and queue a new meal plan if needed
        $existingMealPlan1 = MealPlanFactory::getMealByBannerIdTerm($pair->getStudent1()->getBannerID(), $this->term);

        if($existingMealPlan1 === null){
            // Setup the meal plan for student 1
            $application = HousingApplicationFactory::getAppByStudent($pair->getStudent1(), $this->term);

            $plan1 = MealPlanFactory::createPlan($pair->getStudent1(), $this->term, $application);
            MealPlanFactory::saveMealPlan($plan1);
        }


        try{

        } catch(\Exception $e){
            echo "Exception while creating meal plan for {$pair->getStudent1()->getBannerId()}\n";
        }

        try{
            HMS_Assignment::assignStudent($pair->getStudent2(), $this->term, $room->id, NULL, 'Auto-assigned', false, ASSIGN_FR_AUTO);
        }catch(Exception $e){
            echo "Could not assign '{$pair->getStudent2()->getUsername()}': " . get_class($e) . ": {$e->getMessage()}<br />\n";
        }

        $pair->setBed2($room->__toString());

        // Check for a meal plan, create and queue a new meal plan if needed
        $existingMealPlan2 = MealPlanFactory::getMealByBannerIdTerm($pair->getStudent2()->getBannerID(), $this->term);

        if($existingMealPlan2 === null){
            // Setup the meal plan for student 1
            $application = HousingApplicationFactory::getAppByStudent($pair->getStudent2(), $this->term);

            $plan2 = MealPlanFactory::createPlan($pair->getStudent2(), $this->term, $application);
            MealPlanFactory::saveMealPlan($plan2);
        }
    }

    // TODO: this, better?
    protected function roomSearch($gender = FALSE, $lifestyle = FALSE, $pbuilding = FALSE, $pfloor = FALSE, $proom = FALSE)
    {
        $pre_sql = "SELECT hms_room.*, sub.banner_building_code FROM (SELECT hms_room.id, hms_residence_hall.banner_building_code ,  (count(hms_bed.id)) AS bed_count FROM hms_residence_hall INNER JOIN hms_floor ON hms_residence_hall.id = hms_floor.residence_hall_id  INNER JOIN hms_room ON hms_floor.id = hms_room.floor_id  INNER JOIN hms_bed ON hms_room.id = hms_bed.room_id  LEFT OUTER JOIN hms_assignment ON hms_bed.id = hms_assignment.bed_id WHERE hms_assignment.asu_username IS NULL AND hms_room.offline = 0 AND hms_room.reserved = 0 AND hms_room.ra = 0 AND hms_room.overflow = 0 AND hms_room.private = 0 AND hms_room.parlor = 0 AND hms_room.term = '{$this->term}' AND hms_floor.is_online = 1 AND hms_residence_hall.is_online = 1 ";
        $post_sql = " GROUP BY hms_residence_hall.banner_building_code, hms_room.id) AS sub INNER JOIN hms_room ON sub.id = hms_room.id WHERE sub.bed_count = 2 ORDER BY random() LIMIT 1";

        // Limit to selection
        $moar = array();
        if($gender !== FALSE) {
            $moar[] = " hms_room.gender_type = $gender ";
        }
        if($lifestyle !== FALSE) {
            $lf = ($lifestyle == 2 ? '=' : '!=');
            $moar[] = " hms_residence_hall.gender_type $lf 2 ";
        }
        if($pbuilding !== FALSE) {
            $moar[] = " hms_residence_hall.banner_building_code = '$pbuilding' ";
        }
        if($pfloor !== FALSE) {
            $moar[] = " hms_floor.floor_number = '$pfloor' ";
        }
        if($proom !== FALSE) {
            $moar[] = " hms_room.room_number = '$proom' ";
        }

        // Assemble SQL
        $sql = $pre_sql .
               (count($moar) > 0 ? ' AND ' . implode(' AND ', $moar) : '') .
               $post_sql;

        $db = new \PHPWS_DB;
        $db->setSQLQuery($sql);

        $result = $db->select('row');

        if(\PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->getMessage());
        }

        if(is_null($result)){
            return null;
        }

        $room = new HMS_Room();
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
