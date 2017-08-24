<?php

namespace Homestead;

/**
  * Checks to make sure that the genders of rooms and floors make sense with
  * respect to their parent floors and halls.
  *
  * @author Daniel West <dwest at tux dot appstate dot edu>
  * @package mod
  * @subpackage hms
  **/

class Consistancy_Checker {

    /**
      * Checks each hall, floor, and room for the given term and returns
      * an associative array containing all of the invalid items.
      **/
    public function check($term=null){
        $results = array();

        if(!isset($term)){
            $term = Term::getCurrentTerm();
        }

        $halls = HMS_Residence_Hall::get_halls($term);

        foreach($halls as $hall){
            $floors = $hall->get_floors();

            if(!isset($floors)){
                $results[$hall->hall_name] = "No Floors in Hall!";
            } else {
                foreach($floors as $floor){
                    if($hall->gender_type != COED
                       && $floor->gender_type != $hall->gender_type){
                            $results[$hall->hall_name][$floor->floor_number] = "Gender Mismatch With Hall";
                            continue;
                    }

                    $rooms = $floor->get_rooms();
                    if(!isset($rooms)){
                        $results[$hall->hall_name][$floor->floor_number] = "No rooms in Floor!";
                    } else {
                        foreach($rooms as $room){
                            if($floor->gender_type != COED
                               && $room->gender_type != $floor->gender_type){
                                $results[$hall->hall_name][$floor->floor_number][$room->room_number] = "Gender Mismatch with Floor ".
                                "(Floor: ".$floor->gender_type.
                                ") (Room: ".$room->gender_type.")";
                            }
                        }
                    }

                    $suites = $floor->get_suites();
                    if(isset($suites)){
                        foreach($suites as $suite){
                            $rooms = $suite->get_rooms();

                            $suite_gender = null;
                            foreach($rooms as $room){
                                if(!isset($suite_gender)){
                                    $suite_gender = $room->gender_type;
                                    continue;
                                }
                                if($room->gender_type != $suite_gender){
                                    $results[$hall->hall_name][$floor->floor_number][$room->room_number] = "Suite Gender Mismatch";
                                }
                            }
                        }
                    }
                }
            }
        }

        return $results;
    }
}
