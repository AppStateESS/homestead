<?php

class LotteryShowChooseRoomCommand extends Command {
    
    private $floorId;
    
    public function setFloorId($id){
        $this->floorId = $id;
    }
    
    public function getRequestVars()
    {
        $vars = array('action'=>'LotteryShowChooseRoom', 'floorId'=>$this->floorId);
        return $vars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'LotteryChooseRoomView.php');
        PHPWS_Core::initModClass('hms', 'RlcMembershipFactory.php');
        
        $floorId = $context->get('floorId');
        
        if(!isset($floorId) || is_null($floorId) || empty($floorId)){
            throw new InvalidArgumentException('Missing hall id.');
        }
        
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $rlcAssignment = RlcMembershipFactory::getMembership($student, $term);
        
        if($rlcAssignment == false) {
        	$rlcAssignment = null;
        }
        
        $view = new LotteryChooseRoomView($student, $term, $floorId, $rlcAssignment);
        
        $context->setContent($view->show());
    }
}

?>