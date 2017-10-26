<?php

namespace Homestead;

class RlcMembershipFactory {

    /**
     * Retrieves the given students assignment for the term.
     */
    public static function getMembership(Student $student, $term)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "select hms_learning_community_assignment.* from hms_learning_community_assignment JOIN hms_learning_community_applications ON hms_learning_community_assignment.application_id = hms_learning_community_applications.id where term = :term and username = :username;";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'username' => $student->getUsername(),
                'term'     => $term
        ));
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Homestead\RlcMembershipRestored');

        return $stmt->fetch();
    }

    public static function getRlcMembersByCommunityId($rlcId, $term)
    {
        $db = PdoFactory::getPdoInstance();

        $query = 'SELECT hms_learning_community_assignment.*, hms_learning_community_applications.*
                    FROM hms_learning_community_assignment
                    JOIN hms_learning_community_applications
                        ON hms_learning_community_assignment.application_id = hms_learning_community_applications.id
                    WHERE term = :term and hms_learning_community_assignment.rlc_id = :id';

        $params = array(
                'id' => $rlcId,
                'term' => $term
        );

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
