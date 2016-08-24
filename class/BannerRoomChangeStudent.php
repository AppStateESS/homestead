<?php

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

    public function __construct($bannerId, $old_blg_code, $old_room_code, $new_bldg_code, $new_room_code)
    {
        $this->banner_id = $bannerId;

        $this->old_blg_code = $old_blg_code;
        $this->old_room_code = $old_room_code;

        $this->new_bldg_code = $new_bldg_code;
        $this->new_room_code = $new_room_code;
    }
}
