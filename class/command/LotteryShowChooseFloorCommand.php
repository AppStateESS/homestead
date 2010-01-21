<?php

class LotteryShowChooseFloorCommand extends Command {
    
    private $hallId;
    
    public function setHallId($id){
        $this->hallId = $id;
    }
    
    public function getRequestVars()
    {
        $vars = array('action'=>'LotteryShowChooseFloor', 'hallId'=>$this->hallId);
        return $vars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'LotteryChooseFloorView.php');
        
        $hallId = $context->get('hallId');
        
        if(!isset($hallId) || is_null($hallId) || empty($hallId)){
            throw new InvalidArgumentException('Missing hall id.');
        }
        
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $view = new LotteryChooseFloorView($student, $term, $hallId);
        
        $context->setContent($view->show());
    }
}

?>