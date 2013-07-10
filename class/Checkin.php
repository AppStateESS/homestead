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
    
    const CHECKIN_TIMEOUT = 172800; // Allow max 48 hours between checkins 

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

    /**
     * Causes this check-in to take the ID of the passed-in checkin. When saved,
     * this will effectively overwrite the passed in checkin.
     * @param Checkin $checkin
     */
    public function substitueForExistingCheckin(Checkin $checkin)
    {
        $this->setId($checkin->getId());
    }
    
    /***********************
     * Acessors / Mutators *
    ***********************/
    public function getId(){
        return $this->id;
    }
    
    private function setId($id){
        $this->id = $id;
    }

    public function getBannerId(){
        return $this->banner_id;
    }

    private function setBannerId($bannerId){
        $this->banner_id = $bannerId;
    }

    public function getBedId(){
        return $this->bed_id;
    }
    
    private function setBedId($bedId){
        $this->bed_id = $bedId;
    }

    public function getTerm(){
        return $this->term;
    }
    
    private function setTerm($term){
        $this->term = $term;
    }

    private function setRoomId($roomId){
        $this->room_id = $roomId;
    }

    public function getCheckinDate()
    {
        return $this->checkin_date;
    }
    
    public function setCheckinDate($timestamp){
        $this->checkin_date = $timestamp;
    }

    private function setCheckinBy($checkinBy){
        $this->checkin_by = $checkinBy;
    }

    public function getKeyCode(){
        return $this->key_code;
    }

    public function setKeyCode($keyCode){
        $this->key_code = $keyCode;
    }
    
    public function getCheckoutDate(){
        return $this->checkout_date;
    }
    
    public function setCheckoutDate($date){
        $this->checkout_date = $date;
    }
    
    public function setCheckoutBy($user){
        $this->checkout_by = $user;
    }
    
    public function setImproperCheckout($improper){
        $this->improper_checkout = $improper;
    }
    
    public function setKeyNotReturned($key){
        $this->key_not_returned = $key;
    }
}

class RestoredCheckin extends Checkin {
    public function __construct(){
    } // Empty constructor for resotring state
}
?>
