<?php

namespace Homestead;

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
    	$db = PdoFactory::getPdoInstance();

        $query = "select * from hms_bed where persistent_id = :persistentId AND term = :term";
        $stmt = $db->prepare($query);

        $params = array(
                    'persistentId' => $persistentId,
                    'term'         => $term);
        $stmt->execute($params);

        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, 'BedRestored');

        return $results[0];
    }

	// Retrieves bed by regular Id
	public static function getBedById($bedId, $term)
    {
    	$db = PdoFactory::getPdoInstance();

        $query = "select * from hms_bed where id = :bedId AND term = :term";
        $stmt = $db->prepare($query);

        $params = array(
                    'bedId' 	   => $bedId,
                    'term'         => $term
		);
        $stmt->execute($params);

        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, 'BedRestored');

        return $results[0];
    }

}
