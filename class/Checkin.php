<?php

/**
 * Checkin - Model class for representing checkins and checkouts
 * 
 * @author Jeremy Booker
 * @package hms
 */
class Checkin {

    public $id;

    public $banner_id;
    public $bed_id;
    
    public $room_id; // Just for convenience

    public $checkin_date;
    public $checkin_by;

    public $key_code;

    public $checkout_date;
    public $checkout_by;

    public $expressCheckout;
    public $improperCheckout;

    public function __construct(Student $student, HMS_Bed $bed, $checkinBy, $keyCode)
    {
        $this->setBannerId($student->getBannerId());
        $this->setBedId($bed->getId());
        $this->setRoomId($bed->getParent()->getId());
        $this->setCheckinby($checkinBy);
        $this->setKeyCode($keyCode);
    }

    /***********************
     * Acessors / Mutators *
     ***********************/
    //TODO
}
?>
