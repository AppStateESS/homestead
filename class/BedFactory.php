<?php

/*
 * BedFactory.php
 *
 * @author jbooker
 * @package hms
 */
class BedFactory {

	// Retrieves bed by persistentId
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

	// Retrieves bed by regular Id
	public static function getBedById($bedId)
    {
        PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

    	$db = PdoFactory::getPdoInstance();

        $query = "select * from hms_bed where id = :bedId";
        $stmt = $db->prepare($query);

        $params = array('bedId' => $bedId);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_CLASS, 'BedRestored');

        return $results[0];
    }
}
