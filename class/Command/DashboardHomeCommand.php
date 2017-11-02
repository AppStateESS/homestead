<?php

namespace Homestead\Command;

use Homestead\PdoFactory;
use Homestead\Term;

class DashboardHomeCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'DashboardHome');
    }

    public function execute(CommandContext $context){
        $tpl = array();

        $tpl['NUM_RESIDENTS'] = $this->getNumResidents();

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'admin/dashboardHome.tpl'));
    }

    private function getNumResidents(){
        $pdo = PdoFactory::getPdoInstance();
        $query = 'SELECT count(*) FROM hms_assignment WHERE term = :term';

        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>Term::getCurrentTerm()));

        $result = $stmt->fetch();

        return $result[0];
    }
}
