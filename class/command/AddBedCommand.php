<?php

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
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'bed_structure')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add a bed.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

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
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must enter a bed letter.');
            $errorCmd->redirect();
        }

        if(!isset($bedroomLabel)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must enter a bedroom label.');
            $errorCmd->redirect();
        }

        if(!isset($bannerId)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must enter a banner ID.');
            $errorCmd->redirect();
        }

        if(!isset($roomId)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Missing room ID.');
            $errorCmd->redirect();
        }

        $raRoommate = $context->get('ra_roommate') == 1 ? 1 : 0;
        $intlReserved = $context->get('international_reserved') == 1 ? 1 : 0;

        $room = new HMS_Room($roomId);

        if(is_null($room)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not create bed. Invalid room.');
            $errorCmd->redirect();
        }

        $term = $room->term;

        # Try to create the bed
        try{
            HMS_Bed::addBed($roomId, $term, $bedLetter, $bedroomLabel, $phoneNumber, $bannerId, $raRoommate, $intlReserved);
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error creating the bed: ' . $e->getMessage());
            $errorCmd->redirect();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Bed added successfully.');
        $viewCmd->redirect();
    }
}