<?php

namespace Homestead\Command;

use Homestead\Term;
use Homestead\RlcFactory;

class GetRlcCardListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'GetRlcCardList');
    }

    public function execute(CommandContext $context)
    {
        $rlcs = RlcFactory::getRlcs(Term::getSelectedTerm());

        echo json_encode($rlcs);
        exit;
    }
}
