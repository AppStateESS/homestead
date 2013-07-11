<?php

PHPWS_Core::initModClass('hms', 'Checkin.php');

class CheckinFactory {

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
        $db->addWhere('bed_id', $bed->getId());
        
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
}
?>
