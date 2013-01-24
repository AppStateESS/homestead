<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class ShowControlPanelCommand extends Command {

    function getRequestVars(){
        return array('action'=>'ShowControlPanel');
    }

    function execute(CommandContext $context)
    {
        NQ::close();
         
        header('HTTP/1.1 303 See Other');
        header("Location: {$_SERVER['SCRIPT_NAME']}?module=controlpanel");
        HMS::quit();
    }
}

?>
