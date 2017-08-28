<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * Contoller to handle saving changes to a bed.
 *
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 * @package HMS
 */

class EditBedCommand extends Command {

    private $bedId;

    public function setBedId($id){
        $this->bedId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'EditBed');

        if(isset($this->bedId)){
            $vars['bedId'] = $this->bedId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() ||  !\Current_User::allow('hms', 'bed_attributes') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit beds.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $bedId = $context->get('bedId');

        $viewCmd = CommandFactory::getCommand('EditBedView');
        $viewCmd->setBedId($bedId);

        // Check that the Banner bed ID is valid (five digits)
        $bannerBedId = trim($context->get('banner_id'));
        if(!is_numeric($bannerBedId) || !preg_match("/\d{5}/",$bannerBedId)){
            \NQ::simple('hms', NotificationView::ERROR, 'Invalid Banner bed ID. No changes were saved.');
            $viewCmd->redirect();
        }

        # Create the bed object given the bed_id
        $bed = new HMS_Bed($bedId);
        if(!$bed){
            \NQ::simple('hms', NotificationView::ERROR, 'Invalid bed.');
            $viewCmd->redirect();
        }

        $bed->bedroom_label = $context->get('bedroom_label');
        $bed->phone_number  = $context->get('phone_number');
        $bed->banner_id     = $context->get('banner_id');

        $context->get('ra_roommate') == 1 ? $bed->ra_roommate = 1 : $bed->ra_roommate = 0;
        $context->get('international_reserved') == 1 ? $bed->international_reserved = 1 : $bed->international_reserved = 0;
        $context->get('ra') == 1 ? $bed->ra = 1 : $bed->ra = 0;

        $result = $bed->save();

        if(!$result || \PHPWS_Error::logIfError($result)){
            \NQ::simple('hms', NotificationView::ERROR, 'Error: There was a problem while saving the bed. No changes were made');
            $viewCmd->redirect();
        }

        \NQ::simple('hms', NotificationView::SUCCESS, 'The room was updated successfully.');
        $viewCmd->redirect();
    }
}
