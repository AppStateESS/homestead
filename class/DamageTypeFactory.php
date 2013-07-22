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
        $db = new PHPWS_DB('hms_damage_type');

        $db->addOrder('id');

        $result = $db->select('assoc');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $result;
    }
}

?>