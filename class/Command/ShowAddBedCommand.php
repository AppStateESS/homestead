<?php

namespace Homestead\Command;

use \Homestead\Room;
use \Homestead\AddBedView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class ShowAddBedCommand extends Command {

    private $roomId;
    private $bedLetter;
    private $bedroomLabel;
    private $phoneNumber;
    private $bannerId;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function setBedLetter($letter){
        $this->bedLetter = $letter;
    }

    public function setBedroomLabel($label){
        $this->bedroomLabel = $label;
    }

    public function setPhoneNumber($num){
        $this->phoneNumber = $num;
    }

    public function setBannerId($id){
        $this->bannerId = $id;
    }

    public function getRequestVars(){
        $vars = array('action'=>'ShowAddBed');

        $vars['roomId'] = $this->roomId;

        if(isset($this->bedLetter)){
            $vars['bedLetter'] = $this->bedLetter;
        }

        if(isset($this->bedroomLabel)){
            $vars['bedroomLabel'] = $this->bedroomLabel;
        }

        if(isset($this->phoneNumber)){
            $vars['phoneNumber'] = $this->phoneNumber;
        }

        if(isset($this->bannerId)){
            $vars['bannerId'] = $this->bannerId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'bed_structure')){
            throw new PermissionException('You do not have permission to create a bed.');
        }

        $roomId = $context->get('roomId');

        $room	= new Room($roomId);
        $floor	= $room->get_parent();
        $hall	= $floor->get_parent();

        $addBedView = new AddBedView($hall, $floor, $room, $context->get('bedLetter'), $context->get('bedroomLabel'), $context->get('phoneNumber'), $context->get('bannerId'));
        $context->setContent($addBedView->show());
    }
}
