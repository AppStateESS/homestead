<?php

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
            throw new InvalidArgumentException('Missing term.');
        }

        $db = new PHPWS_DB('hms_bed');
        
        $db->addJoin('', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_bed.term', $this->term);

        $this->totalBedCount = $db->count();

        test($this->totalBedCount,1);
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
}

?>
