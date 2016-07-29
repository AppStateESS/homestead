<?php

/*
 * BedFactory.php
 *
 * @author jbooker
 * @package hms
 */
class BedFactory {

    public static function getBedByPersistentId($persistentId, $term)
    {
        PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

    	$db = PdoFactory::getPdoInstance();

        $query = "select * from hms_bed where persistent_id = :persistentId AND term = :term";
        $stmt = $db->prepare($query);

        $params = array(
                    'persistentId' => $persistentId,
                    'term'         => $term);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_CLASS, 'BedRestored');

        return $results[0];
    }

		public static function getBedByTermWithId($term, $id)
    {
        PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

    		$db = PdoFactory::getPdoInstance();

        $query = "select * from hms_bed where id = :id AND term = :term";
        $stmt = $db->prepare($query);

        $params = array(
                    'id' => $id,
                    'term'         => $term);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_CLASS, 'BedRestored');

        return $results[0];
    }
}
