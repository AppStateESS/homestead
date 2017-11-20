<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;

/**
 * RlcFactory.php
 *
 * @copyright Appalachian State University, 2013
 * @license http://opensource.org/licenses/gpl-3.0.html GNU GPLv3
 */

/**
 * RlcFactory - Factory class for loading Rlc objects.
 *
 * @package  Hms
 * @author   Jeremy Booker
 * @license  http://opensource.org/licenses/gpl-3.0.html GNU GPLv3
 */
class RlcFactory {

    /**
     * Loads a HMS_Learning_Community object from the database given its id.
     *
     * @param int $id
     * @return HMS_Learning_Community The requested learning community object.
     */
    public static function getRlcById($id)
    {
        if (is_null($id) || !is_numeric($id)) {
            throw new \InvalidArgumentException('Missing RLC id.');
        }

        $db = new \PHPWS_DB('hms_learning_communities');
        $db->addWhere('id', $id);

        $community = new RestoredRlc();

        $result = $db->loadObject($community);

        if (\PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $community;
    }

    /**
     * Returns an associative array containing the list of RLCs using their full names, keyed by their id.
     *
     * @param int $term
     * @param string $studentType
     * @param string $hidden
     * @throws DatabaseException
     * @return Array Array of communities
     */
    public static function getRlcList($term, $studentType = NULL, $hidden = NULL)
    {
        $db = new \PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');

        if (!is_null($studentType) && strlen($studentType) == 1) {
            $db->addWhere('allowed_student_types', "%{$student_type}%", 'ilike');
        }

        if ($hidden === FALSE) {
            $db->addWhere('hide', 0);
        }

        $db->addOrder('community_name ASC');

        $rlcs = $db->select('assoc');

        if (\PHPWS_Error::logIfError($rlcs)) {
            throw new DatabaseException($rlcs->toString());
        }

        return $rlcs;
    }

    public static function getRlcs($term)
    {
        $pdo = PdoFactory::getPdoInstance();

        $query = 'SELECT hms_learning_communities.*, COALESCE(foo.member_count, 0) as member_count FROM hms_learning_communities
                        LEFT OUTER JOIN (SELECT rlc_id, count(*) as member_count
                                FROM hms_learning_community_assignment
                                JOIN hms_learning_community_applications ON hms_learning_community_assignment.application_id = hms_learning_community_applications.id
                                WHERE term = :term GROUP BY hms_learning_community_assignment.rlc_id)
                            as foo ON foo.rlc_id = hms_learning_communities.id
                        ORDER BY community_name';

        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>$term));
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Homestead\HMS_Learning_CommunityRestored');

        return $stmt->fetchAll();
    }
}
