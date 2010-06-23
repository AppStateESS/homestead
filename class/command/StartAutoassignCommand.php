<?php

class StartAutoassignCommand {

    public function getRequestVars(){
        return array('action'=>'StartAutoassign');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'Autoassigner.php');

        $assigner = new Autoassigner(Term::getSelectedTerm());
    }
}

?>