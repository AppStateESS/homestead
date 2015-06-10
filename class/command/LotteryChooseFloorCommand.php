<?php

class LotteryChooseFloorCommand extends Command {

    private $floorId;
    private $term;
    private $hallId;

    public function getRequestVars()
    {
        return array('action'=>'LotteryChooseFloor', 'floorId'=>$this->floorId, 'term'=>$this->term, 'hallId'=>$this->hallId);
    }

    public function execute(CommandContext $context)
    {
        $this->setHallId((int)$context->get('hallId'));
        $this->setFloorId($context->get('floor_choices'));
        $roomCmd = CommandFactory::getCommand('LotteryShowChooseRoom');
        $roomCmd->setFloorId($this->floorId);
        $roomCmd->redirect();
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setHallId($hallId)
    {
      $this->hallId = $hallId;
    }

    public function getFloors()
    {
      PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
      $hall = new HMS_Residence_Hall($this->hallId);
      return $hall->getFloors();
    }

    public function setFloorId($floor_number)
    {
      foreach ($this->getFloors() as $floor) {
        if($floor->getFloorNumber() === $floor_number)
        {
          $this->floorId = $floor->getId();
        }
      }
    }


}

?>
