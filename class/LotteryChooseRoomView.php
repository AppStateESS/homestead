<?php

class LotteryChooseRoomView extends hms\View {

    public $student;
    public $term;
    public $floorId;
    private $rlcAssignment;

    public function __construct(Student $student, $term, $floorId, HMS_RLC_Assignment $rlcAssignment = null)
    {
        $this->student = $student;
        $this->term = $term;
        $this->floorId = $floorId;
        $this->rlcAssignment = $rlcAssignment;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

        $floor  = new HMS_Floor($this->floorId);

        $tpl = array();

        $tpl['HALL_FLOOR'] = $floor->where_am_i();

        if(isset($floor->floor_plan_image_id) && $floor->floor_plan_image_id != 0){
            $file = Cabinet::getFile($floor->floor_plan_image_id);

            //if the image loaded properly
            if($file->id == $floor->floor_plan_image_id)
                $tpl['FLOOR_PLAN_IMAGE'] = $file->parentLinked();
        }

        if($this->rlcAssignment != null && ($this->rlcAssignment->getStateName() == 'confirmed' || $this->rlcAssignment->getStateName() == 'selfselect-invite')) {
            $rlcId = $this->rlcAssignment->getRlc()->getId();
        } else {
            $rlcId = null;
        }

        $rooms = $floor->get_rooms();

        $tpl['room_list'] = array();

        foreach($rooms as $room){
            $row = array();


            $num_avail_beds = $room->count_avail_lottery_beds();

            // We list the room dispite whether it's actually available to choose or not,
            // so decide whether to "gray out" this row in the room list or not
            if(($room->gender_type != $this->student->getGender() && $room->gender_type != AUTO)
                || $num_avail_beds     == 0
                || $room->reserved == 1
                || $room->offline  == 1
                || $room->private  == 1
                || $room->overflow == 1
                || $room->parlor   == 1
                || $room->getReservedRlcId() != $rlcId){

                // Show a grayed out row and no link
                $row['ROOM_NUM']        = $room->room_number;
                $row['ROW_TEXT_COLOR']  = 'text-muted';
                $row['AVAIL_BEDS']      = 0; // show 0 available beds since this room is unavailable to the user

            }else{
                // Show the room number as a link
                $roomCmd = CommandFactory::getCommand('LotteryChooseRoom');
                $roomCmd->setRoomId($room->id);
                $row['ROOM_NUM']        = $roomCmd->getLink($room->room_number);
                $row['ROW_TEXT_COLOR']  = 'black';
                $row['AVAIL_BEDS']      = $num_avail_beds;
            }

            if($room->isADA())
            {
              $row['ADA'] = '<i class="fa fa-wheelchair" title="ADA Compliant"></i>';
            }
            if($room->isHearingImpaired())
            {
              $row['HEARING_IMPAIRED'] = '<i class="fa fa-bell-slash" title="Equiped for Hearing Impaired"></i>';
            }
            if($room->bathEnSuite())
            {
              $row['BATH_EN_SUITE'] = '<i class="fa fa-female" title="Bathroom en Suite">|</i><i class="fa fa-male" title="Bathroom en Suite"></i>';
            }

            $row['NUM_BEDS']    = $room->get_number_of_beds();

            $tpl['room_list'][] = $row;
        }

        Layout::addPageTitle("Lottery Choose Room");

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_room.tpl');
    }
}
