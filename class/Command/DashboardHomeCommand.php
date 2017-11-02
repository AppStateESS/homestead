<?php

namespace Homestead\Command;

class DashboardHomeCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'DashboardHome');
    }

    public function execute(CommandContext $context){
        $tpl = array();

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'admin/dashboardHome.tpl'));
    }
}
