<?php

/**
 * Factory class for handling room damage types.
 *
 * @author jbooker
 * @package hms
 */
class DamageTypeFactory {

    /**
     * Returns an associative array of damage type objects.
     *
     * @throws DatabaseException
     * @return Array Associative array of damage types
     */
    public static function getDamageTypeAssoc()
    {
        PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        $db = PdoFactory::getPdoInstance();
        
        $query = "SELECT * FROM hms_damage_type ORDER BY category ASC, description ASC";
        
        $stmt = $db->prepare($query);
        
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultById = array();
        
        foreach($result as $row){
        	$resultById[$row['id']] = $row;
        }
        
        return $resultById;
    }
}


