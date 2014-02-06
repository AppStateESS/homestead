<?php

class LotteryChooseRoomView extends View {
    
    public $student;
    public $term;
    public $floorId;
    
    public function __construct(Student $student, $term, $floorId)
    {
        $this->student = $student;
        $this->term = $term;
        $this->floorId = $floorId;
    }
    
    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

        $floor  = new HMS_Floor($this->floorId);
        $hall   = $floor->get_parent();

        //$full_rooms = $hall->count_lottery_full_rooms();
        //$used_rooms = $hall->count_lottery_used_rooms();

        $tpl['HALL_FLOOR'] = $floor->where_am_i();

        if(isset($floor->floor_plan_image_id) && $floor->floor_plan_image_id != 0){
            $file = Cabinet::getFile($floor->floor_plan_image_id);

            //if the image loaded properly
            if($file->id == $floor->floor_plan_image_id)
                $tpl['FLOOR_PLAN_IMAGE'] = $file->parentLinked();
        }

        $rooms = $floor->get_rooms();

        $tpl['room_list'] = array();

        foreach($rooms as $room){
            $row = array();

            $num_avail_beds = $room->count_avail_lottery_beds();
            $total_beds     = $room->get_number_of_beds();

            // We list the room dispite whether it's actually available to choose or not,
            // so decide whether to "gray out" this row in the room list or not
            if(($room->gender_type != $this->student->getGender() && $room->gender_type != AUTO)
                || $num_avail_beds     == 0 
                || $room->reserved == 1 
                || $room->offline  == 1 
                || $room->private  == 1 
                || $room->overflow == 1
                || $room->parlor   == 1){
        
                // Show a grayed out row and no link
                $row['ROOM_NUM']        = $room->room_number;
                $row['ROW_TEXT_COLOR']  = 'grey';
                $row['AVAIL_BEDS']      = 0; // show 0 available beds since this room is unavailable to the user
            
            }else{
                // Show the room number as a link
                $roomCmd = CommandFactory::getCommand('LotteryChooseRoom');
                $roomCmd->setRoomId($room->id);
                $row['ROOM_NUM']        = $roomCmd->getLink($room->room_number);
                $row['ROW_TEXT_COLOR']  = 'black';
                $row['AVAIL_BEDS']      = $num_avail_beds;
            }

            $row['NUM_BEDS']    = $room->get_number_of_beds();

            $tpl['room_list'][] = $row;
        }

        Layout::addPageTitle("Lottery Choose Room");

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_room.tpl');
    }
}
