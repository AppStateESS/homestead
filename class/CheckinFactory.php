<?php

PHPWS_Core::initModClass('hms', 'Checkin.php');
PHPWS_Core::initModClass('hms', 'PdoFactory.php');

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

    public static function getLastCheckinByBannerId($bannerId, $term)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('banner_id', $bannerId);
        $db->addWhere('term', $term);

        $db->addOrder(array('term DESC', 'checkin_date DESC'));

        $result = $db->getObjects('RestoredCheckin');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if(sizeof($result) <= 0){
            return null;
        }

        return $result[sizeof($result)-1];
    }

    // TODO: There could potentially be multiple check-ins for a student in a single bed
    public static function getCheckinByBed(Student $student, HMS_Bed $bed)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('banner_id', $student->getBannerId());
        //$db->addWhere('term', $term);
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
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_checkin
                    JOIN hms_bed ON hms_checkin.bed_id = hms_bed.id
                    JOIN hms_room ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                    WHERE hms_checkin.term = :term
                    AND checkout_date IS NULL
                    ORDER BY hms_residence_hall.hall_name ASC, hms_room.room_number ASC";

        $stmt = $db->prepare($query);
        $stmt->execute(array('term' => $term));

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'RestoredCheckin');

        return $stmt->fetchAll();
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



        // Smarter term logic: If it's Spring or Summer 2 then we can also look in the previous term
        $term = $hall->getTerm();
        $sem = Term::getTermSem($term);

        if ($sem == TERM_SPRING || $sem == TERM_SUMMER2) {
            $db->addWhere('term', $term, '=', 'OR', 'term_group');
            $db->addWhere('term', Term::getPrevTerm($term), '=', 'OR', 'term_group');
        } else {
            $db->addWhere('term', $term);
        }

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

    public static function getLastCheckoutForStudent(Student $student)
    {
        $db = new PHPWS_DB('hms_checkin');
        $db->addWhere('banner_id', $student->getBannerId());

        $db->addWhere('checkout_date', null, '!=');

        $db->addOrder(array('term DESC', 'checkout_date DESC'));

        $result = $db->getObjects('RestoredCheckin');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if(sizeof($result) <= 0){
            return array();
        }

        return array_shift($result);
    }
}
