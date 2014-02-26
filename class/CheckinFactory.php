<?php

PHPWS_Core::initModClass('hms', 'Checkin.php');

class CheckinFactory {

    //TODO.. This only gets a single checkin.. There coule be multiple, so which checkin this returns in undefined.
    // Find the places it's used and correct them.
    public static function getCheckinByBannerId($bannerId, $term)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('banner_id', $bannerId);
        $db->addWhere('term', $term);

        $checkin = new RestoredCheckin();
        $result = $db->loadObject($checkin);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if($checkin->getId() == null){
            return null;
        }

        return $checkin;
    }

    public static function getCheckinsForStudent(Student $student)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('banner_id', $student->getBannerId());

        $db->addOrder(array('term DESC', 'checkin_date DESC'));

        $result = $db->getObjects('RestoredCheckin');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if(sizeof($result) <= 0){
            return array();
        }

        return $result;
    }

    public static function getCheckinByBed(Student $student, HMS_Bed $bed, $term)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('banner_id', $student->getBannerId());
        $db->addWhere('term', $term);
        //$db->addWhere('bed_id', $bed->getId());
        $db->addWhere('bed_persistent_id', $bed->getPersistentId());

        $checkin = new RestoredCheckin();
        $result = $db->loadObject($checkin);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if($checkin->getId() == null){
            return null;
        }

        return $checkin;
    }

    public static function getCheckinById($checkinId)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('id', $checkinId);

        $checkin = new RestoredCheckin();
        $result = $db->loadObject($checkin);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if($checkin->getId() == null){
            return null;
        }

        return $checkin;
    }

    /**
     * Returns an array of Checkin objects order by hall, and room
     * @param unknown $term
     * //TODO update for persistent ID
     */
    public static function getCheckinsOrderedByRoom($term)
    {
    	$db = new PHPWS_DB('hms_checkin');
    	$db->addWhere('term', $term);

    	$db->addWhere('checkout_date', 'NULL');

    	$db->addJoin('', 'hms_checkin', 'hms_bed', 'bed_id', 'id');
    	$db->addJoin('', 'hms_bed', 'hms_room', 'room_id', 'id');
    	$db->addJoin('', 'hms_room', 'hms_floor', 'floor_id', 'id');
    	$db->addJoin('', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

    	$db->addOrder(array('hms_residence_hall.hall_name ASC', 'hms_room.room_number ASC'));

    	$results = $db->getObjects('RestoredCheckin');

    	if(PHPWS_Error::logIfError($results)){
    		throw new DatabaseException($results->toString());
    	}

    	return $results;
    }

    /**
     * Returns an array of Checkin objects order by hall, and room
     * @param unknown $term
     * //TODO update for persistent ID
     */
    public static function getCheckinsOrderedByHallAlpha($term)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('term', $term);

        $db->addWhere('checkout_date', 'NULL');

        $db->addJoin('', 'hms_checkin', 'hms_bed', 'bed_id', 'id');
        $db->addJoin('', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');
        $db->addJoin('', 'hms_checkin', 'hms_assignment', 'banner_id', 'banner_id AND hms_checkin.term = hms_assignment.term');

        $db->addOrder(array('hms_residence_hall.hall_name ASC', 'hms_assignment.asu_username ASC'));

        $results = $db->getObjects('RestoredCheckin');

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException($results->toString());
        }

        return $results;
    }

    /**
     * Returns the earliest check-in for the given student, in the given hall, which the student
     * has not checked out of yet.
     * //TODO update for persistent ID
     */
    public static function getPendingCheckoutForStudentByHall(Student $student, HMS_Residence_Hall $hall)
    {
        $db = new PHPWS_DB('hms_checkin');

        // Join the hall structure
        $db->addJoin('', 'hms_checkin', 'hms_hall_structure', 'bed_id', 'bedid');

        $db->addWhere('banner_id', $student->getBannerId());
        //$db->addWhere('term', $hall->getTerm());

        // Checkin bed ID must be in the request hall
        //$db->addWhere('hms_hall_structure.hallid', $hall->getId());

        $db->addWhere('checkout_date', null, 'IS NULL');

        $db->addOrder(array('hms_checkin.checkin_date ASC')); // Earliest checkin first

        $checkin = new RestoredCheckin();
        $result = $db->loadObject($checkin);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if($checkin->getId() == null){
            return null;
        }

        return $checkin;
    }
}
?>
