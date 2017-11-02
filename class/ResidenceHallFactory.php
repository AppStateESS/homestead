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

        $db = PdoFactory::getPdoInstance();
        $sql = "SELECT id
           FROM hms_residence_hall
           WHERE term = :term
           ORDER BY hall_name ASC";
        $sth = $db->prepare($sql);
        $sth->execute(array('term' => $term));
        $results = $sth->fetchAll(\PDO::FETCH_ASSOC);

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
