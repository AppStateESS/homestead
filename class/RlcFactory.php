<?php

namespace Homestead;

/**
 * RlcFactory.php
 *
 * @copyright Appalachian State University, 2013
 * @license http://opensource.org/licenses/gpl-3.0.html GNU GPLv3
 */

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

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
            throw new InvalidArgumentException('Missing RLC id.');
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
}
