<?php

namespace Homestead\Report\UnavailableBeds;

/**
 * Lists unavailable beds
 *
 * @author Jeremy Booker
 * @package hms
 */

class UnavailableBeds extends Report {
    const friendlyName = 'Unavailable Beds';
    const shortName = 'UnavailableBeds';

    private $rows;
    private $term;

    private $totalBedCount;
    private $unavailableBeds;

    public function __construct($id=0)
    {
        parent::__construct($id);
    }

    public function execute()
    {
        if(!isset($this->term) || is_null($this->term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        /*****
         * Total Beds
         */
        $db = new \PHPWS_DB('hms_bed');

        $db->addJoin('', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_bed.term', $this->term);

        $this->totalBedCount = $db->count();

        if(\PHPWS_Error::logIfError($this->totalBedCount)){
            PHPWS_Core::initModClass('hms', 'exception', 'DatabaseException.php');
            throw new DatabaseException($this->totalBedCount->toString());
        }

        /*******
         * Unavailable Beds
         */
        $db = new \PHPWS_DB('hms_bed');
        $db->addColumn('hms_residence_hall.hall_name');
        $db->addColumn('hms_bed.*');
        $db->addColumn('hms_room.*');

        $db->addJoin('', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_room.reserved', 1, null, 'OR', 'foo');
        $db->addWhere('hms_room.ra', 1, null, 'OR', 'foo');
        $db->addWhere('hms_room.private', 1, null, 'OR', 'foo');
        $db->addWhere('hms_room.overflow', 1, null, 'OR', 'foo');
        $db->addWhere('hms_room.parlor', 1, null, 'OR', 'foo');
        $db->addWhere('hms_room.offline', 1, null, 'OR', 'foo');
        $db->addWhere('hms_bed.ra_roommate', 1, null, 'OR', 'foo');
        $db->addWhere('hms_bed.international_reserved', 1, null, 'OR', 'foo');

        $db->addWhere('hms_bed.term', $this->term);

        $db->addOrder(array('hms_residence_hall.hall_name', 'hms_room.room_number', 'bed_letter'));

        $this->unavailableBeds = $db->select();

        if(\PHPWS_Error::logIfError($this->unavailableBeds)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($this->unavailableBeds->toString());
        }
    }

    public function getTotalBedCount()
    {
        return $this->totalBedCount;
    }

    public function getUnavailableBeds()
    {
        return $this->unavailableBeds;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }
}
