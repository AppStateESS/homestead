<?php
namespace Homestead;

class EmailLogFactory {

    public static function logMessage(Student $student, Array $messageResult, string $messageType)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "INSERT INTO hms_email_log (banner_id, message_id, message_type, username, time_made)
                  VALUES (:bannerId, :messageId, :messageType, :username, :currentTime);";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'bannerId'    => $student->getBannerId(),
                'messageId'   => $messageResult['_id'],
                'messageType' => $messageType,
                'username'    => $student->getUsername(),
                'currentTime' => time()
        ));
    }

    public static function getMessageByBannerId(string $bannerId){
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_email_log where banner_id = :bannerId";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'bannerId' => $bannerId
        ));

        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }
}
