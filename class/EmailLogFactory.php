<?php
PHPWS_Core::initModClass('hms', 'PdoFactory.php');

class EmailLogFactory {

    public static function logMessage(Student $student, Array $messageResult, string $messageType)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "INSERT INTO hms_email_log VALUES (:bannerId, :messageId, :messageType, :username);";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'bannerId' => $student->getBannerId(),
                'messageId' => $messageResult['_id'],
                'messageType' => $messageType,
                'username' => $student->getUsername()
        ));
    }
}
