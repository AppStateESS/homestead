<?php

namespace Homestead\Command;

use \Homestead\HMS_Learning_Community;
use \Homestead\ShowViewByRlc;
use \Homestead\Exception\PermissionException;

class ShowViewByRlcCommand extends Command {
    private $rlcId;

    public function getRlcId()
    {
        return $this->rlcId;
    }

    public function setRlcId($id){
        if(is_numeric($id)){
            $this->rlcId = $id;
        }
    }

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowViewByRlc',
                      'rlc'    => $this->rlcId);

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'view_rlc_members')){
            throw new PermissionException('You do not have permission to view RLC members.');
        }

        $rlcId = $context->get('rlc');

        if(!isset($rlcId)){
            throw new \InvalidArgumentException('Missing RLC id.');
        }

        $rlc = new HMS_Learning_Community($rlcId);

        $view = new ShowViewByRlc($rlc);

        $context->setContent($view->show());
    }
}
