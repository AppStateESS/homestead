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
    public $term;
    public $bed_id;
    
    public $room_id; // Just for convenience

    public $checkin_date;
    public $checkin_by;

    public $key_code;

    public $checkout_date;
    public $checkout_by;

    public $express_checkout;
    public $improper_checkout;

    public function __construct(Student $student, HMS_Bed $bed, $term, $checkinBy, $keyCode)
    {
        $this->setBannerId($student->getBannerId());
        $this->setBedId($bed->getId());
        $this->setTerm($term);
        $this->setRoomId($bed->getParent()->getId());
        $this->setCheckinby($checkinBy);
        $this->setKeyCode($keyCode);
    }

    /***********************
     * Acessors / Mutators *
     ***********************/
    public function getId(){
        return $this->id;
    }

    private function setBannerId($bannerId){
        $this->banner_id = $bannerId;
    }

    private function setBedId($bedId){
        $this->bed_id = $bedId;
    }

    private function setTerm($term){
        $this->term = $term;
    }

    private function setRoomId($roomId){
        $this->room_id = $roomId;
    }

    private function setCheckinBy($checkinBy){
        $this->checkin_by = $checkinBy;
    }

    private function setKeyCode($keyCode){
        $this->key_code = $keyCode;
    }
}

class RestoredCheckin extends Checkin {
    public function __construct(){} // Empty constructor for resotring state
}
?>
