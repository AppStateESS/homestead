<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;
use \Homestead\UserStatus;
use \Homestead\Room;
use \Homestead\Bed;

class AddBedCommand extends Command {

    private $roomId;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'AddBed', 'roomId'=>$this->roomId);
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'bed_structure')){
            throw new PermissionException('You do not have permission to add a bed.');
        }

        $errorCmd = CommandFactory::getCommand('ShowAddBed');
        $errorCmd->setRoomId($context->get('roomId'));
        $errorCmd->setBedLetter($context->get('bed_letter'));
        $errorCmd->setBedroomLabel($context->get('bedroom_label'));
        $errorCmd->setBannerId($context->get('banner_id'));

        $viewCmd = CommandFactory::getCommand('EditRoomView');
        $viewCmd->setRoomId($context->get('roomId'));

        $bedLetter		= $context->get('bed_letter');
        $bedroomLabel	= $context->get('bedroom_label');
        $bannerId		= $context->get('banner_id');
        $roomId			= $context->get('roomId');
        $phoneNumber	= $context->get('phone_number');

        if(!isset($bedLetter)){
            \NQ::simple('hms', NotificationView::ERROR, 'You must enter a bed letter.');
            $errorCmd->redirect();
        }

        if(!isset($bedroomLabel)){
            \NQ::simple('hms', NotificationView::ERROR, 'You must enter a bedroom label.');
            $errorCmd->redirect();
        }

        if(!isset($bannerId)){
            \NQ::simple('hms', NotificationView::ERROR, 'You must enter a banner ID.');
            $errorCmd->redirect();
        }

        if(!isset($roomId)){
            \NQ::simple('hms', NotificationView::ERROR, 'Missing room ID.');
            $errorCmd->redirect();
        }

        $raBed = $context->Get('ra') == 1 ? 1 : 0;
        $raRoommate = $context->get('ra_roommate') == 1 ? 1 : 0;
        $intlReserved = $context->get('international_reserved') == 1 ? 1 : 0;

        $room = new Room($roomId);

        if(is_null($room)){
            \NQ::simple('hms', NotificationView::ERROR, 'Could not create bed. Invalid room.');
            $errorCmd->redirect();
        }

        $term = $room->term;

        $persistentId = uniqid();

        # Try to create the bed
        try{
            Bed::addBed($roomId, $term, $bedLetter, $bedroomLabel, $phoneNumber, $bannerId, $raRoommate, $intlReserved, $raBed, $persistentId);
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, 'There was an error creating the bed: ' . $e->getMessage());
            $errorCmd->redirect();
        }

        \NQ::simple('hms', NotificationView::SUCCESS, 'Bed added successfully.');
        $viewCmd->redirect();
    }
}
