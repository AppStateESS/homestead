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
    public $key_not_returned;

    public function __construct(Student $student, HMS_Bed $bed, $term, $checkinBy, $keyCode)
    {
        $this->setBannerId($student->getBannerId());
        $this->setBedId($bed->getId());
        $this->setTerm($term);
        $this->setRoomId($bed->get_parent()->getId());
        $this->setCheckinDate(time());
        $this->setCheckinby($checkinBy);
        $this->setKeyCode($keyCode);
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_checkin');
         
        try {
            $result = $db->saveObject($this);
        } catch (Exception $e) {
            // rethrow any exceptions
            throw $e;
        }
         
        if (PHPWS_Error::logIfError($result)) {
            throw new Exception($result->toString());
        }
         
        return $this->id;
    }

    /***********************
     * Acessors / Mutators *
    ***********************/
    public function getId(){
        return $this->id;
    }

    public function getBannerId(){
        return $this->banner_id;
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

    private function setCheckinDate($timestamp){
        $this->checkin_date = $timestamp;
    }

    private function setCheckinBy($checkinBy){
        $this->checkin_by = $checkinBy;
    }

    public function getKeyCode(){
        return $this->key_code;
    }

    private function setKeyCode($keyCode){
        $this->key_code = $keyCode;
    }
    
    public function setCheckoutDate($date)
    {
        $this->checkout_date = $date;
    }
    
    public function setCheckoutBy($user)
    {
        $this->checkout_by = $user;
    }
    
    public function setImproperCheckout($improper)
    {
        $this->improper_checkout = $improper;
    }
    
    public function setKeyNotReturned($key)
    {
        $this->key_not_returned = $key;
    }
}

class RestoredCheckin extends Checkin {
    public function __construct(){
    } // Empty constructor for resotring state
}
?>
