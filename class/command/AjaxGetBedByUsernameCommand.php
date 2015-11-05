<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetBedByUsernameCommand extends Command {

    private $floorId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetBedByUsername');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $username = $context->get('username');

        $term = Term::getSelectedTerm();

        $assignment = HMS_Assignment::getAssignment($username, $term);

        $bed = BedFactory::getBedByBedId($assignment->getBedId(), $term);

        $nameInfo = array('bedId' => $bed->getId(), 'location' => $bed->where_am_i());

        $context->setContent(json_encode($nameInfo));
    }
}
