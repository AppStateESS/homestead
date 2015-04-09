<?php

PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

class RlcMembershipFactory {

    public static function getMembership(Student $student, $term)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "select * from hms_learning_community_assignment JOIN hms_learning_community_applications ON hms_learning_community_assignment.application_id = hms_learning_community_applications.id where term = :term and username = :username;";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'username' => $student->getUsername(),
                'term'     => $term
        ));
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'RlcMembershipRestored');

        return $stmt->fetch();
    }
}

?>