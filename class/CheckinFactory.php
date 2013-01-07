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

}
?>
