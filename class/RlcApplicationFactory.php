<?php

PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

class RlcApplicationFactory {

    public static function getApplication(Student $student, $term)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_learning_community_applications where username = :username and term = :term";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'username' => $student->getUsername(),
                'term'     => $term
        ));
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'RlcApplicationRestored');

        return $stmt->fetch();
    }
}
