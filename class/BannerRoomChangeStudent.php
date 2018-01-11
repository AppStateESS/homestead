<?php

namespace Homestead;

/**
 * Class used to represent room changes for the Banner web Service
 *
 * @package Homestead
 * @author Jeremy Booker
 */

class BannerRoomChangeStudent {

    // NB: These fields must be public, and must match the exact names defined
    // in the Banner web service's WSDL file.
    public $banner_id;
    public $old_room_code;
    public $old_bldg_code;
    public $new_room_code;
    public $new_bldg_code;

    private $student;
    private $oldBed;
    private $newBed;

    public function __construct(Student $student, Bed $oldBed, Bed $newBed)
    {
        $this->student = $student;
        $this->oldBed = $oldBed;
        $this->newBed = $newBed;

        $this->banner_id = $student->getBannerId();

        $this->old_bldg_code = $oldBed->get_banner_building_code();
        $this->old_room_code = $oldBed->getBannerId();

        $this->new_bldg_code = $newBed->get_banner_building_code();
        $this->new_room_code = $newBed->getBannerId();
    }

    public function getStudent() {
        return $this->student;
    }

    public function getNewBed() {
        return $this->newBed;
    }
}
