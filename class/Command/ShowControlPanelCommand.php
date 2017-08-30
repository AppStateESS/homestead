<?php

namespace Homestead\Command;

use \Homestead\HMS;

class ShowControlPanelCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowControlPanel');
    }

    public function execute(CommandContext $context)
    {
        \NQ::close();

        header('HTTP/1.1 303 See Other');
        header("Location: {$_SERVER['SCRIPT_NAME']}?module=controlpanel");
        HMS::quit();
    }
}
