<?php

namespace Homestead;

class LotteryChooseFloorView extends View {

    private $student;
    private $term;
    private $hallId;
    private $rlcAssignment;

    public function __construct(Student $student, $term, $hallId, HMS_RLC_Assignment $rlcAssignment = null){
        $this->student = $student;
        $this->term = $term;
        $this->hallId = $hallId;
        $this->rlcAssignment = $rlcAssignment;
    }

    public function show()
    {
        \PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');

        $hall = new ResidenceHall($this->hallId);

        $tpl = array();

        $tpl['HALL']            = $hall->hall_name;
        if(isset($hall->exterior_image_id)){
            $tpl['EXTERIOR_IMAGE']  = \Cabinet::getTag($hall->exterior_image_id);
        }

        if(isset($hall->room_plan_image_id) && $hall->room_plan_image_id != 0){
            $file = \Cabinet::getFile($hall->room_plan_image_id);
            if($file->id !== 0){
                $tpl['ROOM_PLAN_IMAGE'] = $file->parentLinked();
            }
        }

        if(isset($hall->map_image_id)){
            $tpl['MAP_IMAGE']       = \Cabinet::getTag($hall->map_image_id);
        }

        if(isset($hall->other_image_id) && $hall->other_image_id != 0 && $hall->other_image_id != '0'){
            $file = \Cabinet::getFile($hall->other_image_id);
            if($file->id !== 0){
                $tpl['OTHER_IMAGE'] = $file->parentLinked();
            }
        }

        if($this->rlcAssignment != null && ($this->rlcAssignment->getStateName() == 'confirmed' || $this->rlcAssignment->getStateName() == 'selfselect-invite')) {
            $rlcId = $this->rlcAssignment->getRlc()->getId();
        } else {
            $rlcId = null;
        }

        $floors = $hall->get_floors();

        foreach($floors as $floor){
            $row = array();

            if($floor->count_avail_lottery_rooms($this->student->getGender(), $rlcId) <= 0){
                $row['FLOOR']           = HMS_Util::ordinal($floor->floor_number);
                $row['ROW_TEXT_COLOR']  = 'class="text-muted"';
                $tpl['floor_list'][]    = $row;
                continue;
            }

            $floorCmd = CommandFactory::getCommand('LotteryChooseFloor');
            $floorCmd->setFloorId($floor->id);

            $row['FLOOR']           = $floorCmd->getLink(HMS_Util::ordinal($floor->floor_number) . ' floor');
            $row['ROW_TEXT_COLOR']  = 'grey';
            $tpl['floor_list'][]    = $row;
        }

        \Layout::addPageTitle("Choose Floor");

        return \PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_floor.tpl');
    }
}

//
