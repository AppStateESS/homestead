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
}
?>
