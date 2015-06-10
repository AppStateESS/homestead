<?php

class LotteryChooseFloorView extends hms\View {

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
        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('LotteryChooseFloor');
        $submitCmd->setTerm($this->term);
        $submitCmd->setHallId($this->hallId);
        $submitCmd->initForm($form);

        $tpl = array();

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');

        $hall = new HMS_Residence_Hall($this->hallId);


        $tpl['HALL'] = $hall->hall_name;
        if(isset($hall->exterior_image_id)){
            $tpl['EXTERIOR_IMAGE']  = Cabinet::getTag($hall->exterior_image_id);
        }

        if(isset($hall->room_plan_image_id)){
            $file = Cabinet::getFile($hall->room_plan_image_id);
            //$tpl['ROOM_PLAN_IMAGE'] = $file->parentLinked();
        }

        if(isset($hall->map_image_id)){
            $tpl['MAP_IMAGE']       = Cabinet::getTag($hall->map_image_id);
        }

        if(isset($hall->other_image_id) && $hall->other_image_id != 0 && $hall->other_image_id != '0'){
            $file = Cabinet::getFile($hall->other_image_id);
            //$tpl['OTHER_IMAGE'] = $file->parentLinked();
        }

        if($this->rlcAssignment != null) {
            $rlcId = $this->rlcAssignment->getRlc()->getId();
        } else {
            $rlcId = null;
        }

        $floors = $hall->get_floors();

        $floor_list = array();

        foreach ($floors as $floor)
        {
          if($floor->count_avail_lottery_rooms($this->student->getGender(), $rlcId) > 0)
          {
            $floor_list[$floor->floor_number] = HMS_Util::ordinal($floor->floor_number);
            $somethingsAvailable = true;
          }
        }

        $form->addDropBox('floor_choices', $floor_list);
        $form->addCssClass('floor_choices', 'form-control');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_floor.tpl');
    }
}
