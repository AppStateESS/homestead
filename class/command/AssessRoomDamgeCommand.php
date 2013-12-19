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
        // Check permissions
        if(!Current_User::allow('hms', 'damage_assessment')){
            throw new PermissionException('You do not have permission to perform room damage assessment.');
        }

        // Grab data from JSON source
        $data = $context->getJsonData();

        // For each responsibility object submitted
        foreach($data as $row){

            // Load it from the database
            $resp = RoomDamageResponsibilityFactory::getResponsibilityById($row['id']);
            $resp->setAmount($row['amount']);
            $resp->setState('assessed');
            $resp->setAssessedOn(time());
            $resp->setAssessedBy(UserStatus::getUsername());
        }
    }
}