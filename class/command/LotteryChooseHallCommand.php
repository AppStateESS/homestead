<?php

class LotteryChooseHallCommand extends Command {

    private $hallId;
    private $term;

    public function getRequestVars(){
        $vars = array('action'=>'LotteryChooseHall', 'hallId'=>$this->hallId, 'term'=>$this->term);

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $this->setTerm($context->get('term'));
        $this->setHallId($context);
        $cmd = CommandFactory::getCommand('LotteryShowChooseFloor');
        $cmd->setHallId($this->getHallId());
        $cmd->redirect();
    }

    public function setTerm($term)
    {
      $this->term = $term;
    }

    public function setHallId($context)
    {
      $hall_choice = $context->get('hall_choices');

      $halls = ResidenceHallFactory::getHallNamesAssoc($this->term);

      $this->hallId = array_search($hall_choice, $halls);

    }

    public function getHallId()
    {
      return $this->hallId;
    }
}
