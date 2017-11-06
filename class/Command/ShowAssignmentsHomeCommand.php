<?php

namespace Homestead\Command;

use Homestead\AssetResolver;
use Homestead\CommandFactory;

class ShowAssignmentsHomeCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'ShowAssignmentsHome');
    }

    public function execute(CommandContext $context)
    {
        $tpl = array();

        $assignCmd = CommandFactory::getCommand('ShowAssignStudent');
        $tpl['ASSIGN_STUDENT_URI'] = $assignCmd->getUri();

        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'assignmentsTable');

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'admin/AssignmentsHome.tpl'));
    }
}
