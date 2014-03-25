<?php

PHPWS_Core::initModClass('hms', 'View.php');

class BedView extends hms\View{

    private $hall;
    private $floor;
    private $room;
    private $bed;

    public function __construct(HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room, HMS_Bed $bed){
        $this->hall		= $hall;
        $this->floor	= $floor;
        $this->room		= $room;
        $this->bed		= $bed;
    }

    public function show()
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'bed_view')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You are not allowed to edit or view beds.');
        }
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl['TERM']                = Term::toString($this->bed->getTerm());
        $tpl['HALL_NAME']           = $this->hall->getLink();
        $tpl['FLOOR_NUMBER']        = $this->floor->getLink('Floor');
        $tpl['ROOM_NUMBER_LINK']    = $this->room->getLink();
        $tpl['ROOM_NUMBER']         = $this->room->getRoomNumber();
        $tpl['BED_LABEL']           = $this->bed->getBedroomLabel() . ' ' . $this->bed->getLetter();

        $tpl['ASSIGNED_TO'] = $this->bed->get_assigned_to_link();

        $tpl['HALL_ABBR'] = $this->hall->getBannerBuildingCode();

        $submitCmd = CommandFactory::getCommand('EditBed');
        $submitCmd->setBedId($this->bed->id);

        $form = new PHPWS_Form();
        $submitCmd->initForm($form);

        $form->addText('bedroom_label', $this->bed->bedroom_label);

        $form->addText('phone_number', $this->bed->phone_number);
        $form->setMaxSize('phone_number', 4);
        $form->setSize('phone_number', 5);

        $form->addText('banner_id', $this->bed->banner_id);

        $form->addCheckBox('ra', 1);
        if($this->bed->isRa()){
            $form->SetExtra('ra', 'checked');
        }

        $form->addCheckBox('ra_roommate', 1);

        if($this->bed->ra_roommate == 1){
            $form->setExtra('ra_roommate', 'checked');
        }

        $form->addCheckBox('international_reserved');

        if($this->bed->international_reserved == 1){
            $form->setExtra('international_reserved', 'checked');
        }

        $form->addSubmit('submit', 'Submit');

        # if the user has permission to view the form but not edit it
        if(   !Current_User::allow('hms', 'bed_view')
        && !Current_User::allow('hms', 'bed_attributes')
        && !Current_User::allow('hms', 'bed_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Edit Bed");

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
    }
}

?>