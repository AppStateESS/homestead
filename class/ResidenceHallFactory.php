<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;

/**
 * ResidenceHallFactory - Factory methods to aid in creating ResidenceHall objects.
 *
 * @author jbooker
 * @package hms
*/
class ResidenceHallFactory {

    /**
     * Returns an array of ResidenceHall objects for the given term.
     *
     * @param integer $term
     * @throws \InvalidArgumentException
     * @throws DatabaseException
     * @return multitype:ResidenceHall
     */
    public static function getHallsForTerm($term)
    {
        if(!isset($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $halls = array();

        $db = new \PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addOrder('hall_name', 'DESC');

        $db->addWhere('term', $term);

        $results = $db->select();

        if(\PHPWS_Error::logIfError($results)){
            throw new DatabaseException($result->toString());
        }

        //TODO this is terribly inefficient
        foreach($results as $result){
            $halls[] = new ResidenceHall($result['id']);
        }

        return $halls;
    }

    /**
     * Returns a list of hall names in an associative array, where the array key is the hall's database id.
     *
     * @param integer $term
     * @throws \InvalidArgumentException
     * @return multitype:Array
     */
    public static function getHallNamesAssoc($term)
    {
        if(!isset($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $hallArray = array();

        $halls = ResidenceHallFactory::getHallsForTerm($term);

        foreach ($halls as $hall){
            $hallArray[$hall->id] = $hall->hall_name;
        }

        return $hallArray;
    }
}
