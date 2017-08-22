<?php

namespace Homestead;

/**
 * Damage - Model class for representing Room Damages.
 *
 * @author Jeremy Booker
 * @package hms
 */
class Damage {

    public $id; // Unique id for the hms_damage table

    public $room_id; // The room ID where this damage happened

    public $damage_type; // Foreign key to the hms_damage_type table

    // Checkout Info
    public $checkout_id; // Optional foreign key to the checkin-checkout table

    // Student data
    public $banner_id; // Optional banner ID of the student who caused the damage
    public $split_with_roommate; // Indicates that this damage was split with the person's roommate

    // Initial reporter
    public $reported_by; // User name of the person who reported the damage
    public $reported_on; // Date damage was initially reported

    // Duckets
    public $cost;
    public $cost_set_by;
    public $cost_set_date;

    public $charged; // Whether or not the student's account has been charged the cost yet (i.e. charges reported to Banner)
    public $charged_by;
    public $charged_date;

    // Workorder info
    public $workorder_submitted; // Whether or not a workorder has been submitted for this damage
    public $workorder_submitted_date;
    public $workorder_submitted_by;
    public $repaired; // Whether or not this damage has been repaired
    public $repaired_date;

    public function __construct($roomId, $bannerId, $damageType, $checkoutId, $splitWithRoommate, $reportedBy)
    {
        $this->setRoomid($roomId);
        $this->setBannerId($bannerId);
        $this->setDamageType($damageType);
        $this->setCheckoutId($checkoutId);
        $this->setSplitWithRoommate($splitWithRoommate);
        $this->setReportedBy($reportedBy);
        $this->setReportedOn(time());
    }


    /***********************
     * Acessors / Mutators *
     ***********************/

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    //TODO More here..
}
