<?php

/**
 * Controller class to handle POST request from angular front end
 * for submitting a list of room damage responsibilities and the
 * amounts which have been assessed for each.
 *
 * @author jbooker
 * @package hms
 */
class AssessRoomDamgeCommand extends Command {

    public function getRequestVars()
    {
        // Handeled by Angular, so we don't need anything here
        return array ();
    }

    public function execute(CommandContext $context)
    {

    }
}