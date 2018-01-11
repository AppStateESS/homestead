<?php

namespace Homestead\Command;

use \Homestead\HMS_RLC_Assignment;
use \Homestead\UserStatus;
use \Homestead\AcceptRlcInviteView;

class ShowAcceptRlcInviteCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action'=>'ShowAcceptRlcInvite', 'term'=>$this->term);
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        if(!isset($term)){
            throw new \InvalidArgumentException('Missing term!');
        }

        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername(UserStatus::getUsername(), $term);
        $rlcApplication = $rlcAssignment->getApplication();

        $view = new AcceptRlcInviteView($rlcApplication, $rlcAssignment, $term);

        $context->setContent($view->show());
    }
}
