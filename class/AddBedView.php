<?php

PHPWS_Core::initModClass('hms', 'View.php');

class AddBedView extends homestead\View {

    private $hall;
    private $floor;
    private $room;

    private $bedLetter;
    private $bedroomLabel;
    private $phoneNumber;
    private $bannerId;

    public function __construct(HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room, $bedLetter = NULL, $bedroomLabel = NULL, $phoneNumber = NULL, $bannerId = NULL)
    {
        $this->hall		= $hall;
        $this->floor	= $floor;
        $this->room		= $room;

        $this->bedLetter	= $bedLetter;
        $this->bedroomLabel	= $bedroomLabel;
        $this->phoneNumber  = $phoneNumber;
        $this->bannerId		= $bannerId;
    }

    public function show()
    {
        $tpl = array();

        $tpl['TITLE']               = 'Add New Bed';
        $tpl['HALL_NAME']           = $this->hall->getLink();
        $tpl['FLOOR_NUMBER']        = $this->floor->getLink();
        $tpl['ROOM_NUMBER']         = $this->room->getLink();

        $tpl['ASSIGNED_TO'] = '&lt;unassigned&gt;';

        $submitCmd = CommandFactory::getCommand('AddBed');
        $submitCmd->setRoomId($this->room->id);

        $form = new PHPWS_Form();
        $submitCmd->initForm($form);

        if(isset($this->bedLetter)){
            $form->addText('bed_letter', $this->bedLetter);
        }else{
            $form->addText('bed_letter', chr($this->room->get_number_of_beds() + 97));
        }

        if(isset($this->bedroomLabel)){
            $form->addText('bedroom_label', $this->bedroomLabel);
        }else{
            $form->addText('bedroom_label', 'a');
        }
         
        if(isset($this->phoneNumber)){
            $form->addText('phone_number', $this->phoneNumber);
        }else{
            // Try to guess at the phone number
            $beds = $this->room->get_beds();
            if(sizeof($beds) > 0){
                $form->addText('phone_number', $beds[0]->phone_number);
            }else{
                $form->addText('phone_number');
            }
        }
        $form->setMaxSize('phone_number', 4);
        $form->setSize('phone_number', 5);
         
        if(isset($this->bannerId)){
            $form->addText('banner_id', $this->bannerId);
        }else{
            // try to guess a the banner ID

            // Strip any text out of the room number, just get the numbers
            $match = null;
            preg_match("/[0-9]*/", $this->room->room_number, $match);
            $roomNumber = $match[0];
             
            $form->addText('banner_id', '0' . $roomNumber . ($this->room->get_number_of_beds()+1));
        }

        $form->addCheckBox('ra_roommate', 1);
        $form->addCheckBox('international_reserved',1);

        $form->addSubmit('submit', 'Submit');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Add Bed");

        # Reusing the edit bed template here
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
    }
}

?>