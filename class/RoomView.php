<?php

PHPWS_Core::initModClass('hms', 'View.php');

class RoomView extends View {

    private $hall;
    private $floor;
    private $room;

    public function __construct(HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room){
        $this->hall		= $hall;
        $this->floor	= $floor;
        $this->room		= $room;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl['TITLE'] = $this->room->room_number . ' ' . $this->hall->hall_name . ' - ' . Term::getPrintableSelectedTerm();

        $number_of_assignees    = $this->room->get_number_of_assignees();

        $tpl['HALL_NAME']           = $this->hall->getLink();
        $tpl['FLOOR_NUMBER']        = $this->floor->getLink();
        $tpl['NUMBER_OF_BEDS']      = $this->room->get_number_of_beds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $number_of_assignees;

        $form = new PHPWS_Form;

        $submitCmd = CommandFactory::getCommand('EditRoom');
        $submitCmd->setRoomId($this->room->id);
        $submitCmd->initForm($form);

        $form->addText('room_number', $this->room->room_number);


        if($number_of_assignees == 0){
            # Room is empty, show the drop down so the user can change the gender
            $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED=>COED_DESC));
            $form->setMatch('gender_type', $this->room->gender_type);
        }else{
            # Room is not empty so just show the gender (no drop down)
            if($this->room->gender_type == FEMALE){
                $tpl['GENDER_MESSAGE'] = "Female";
            }else if($this->room->gender_type == MALE){
                $tpl['GENDER_MESSAGE'] = "Male";
            }else if($this->room->gender_type == COED){
                $tpl['GENDER_MESSAGE'] = "Coed";
            }else{
                $tpl['GENDER_MESSAGE'] = "Error: Undefined gender";
            }
            # Add a hidden variable for 'gender_type' so it will be defined upon submission
            $form->addHidden('gender_type', $this->room->gender_type);
            # Show the reason the gender could not be changed.
            if($number_of_assignees != 0){
                $tpl['GENDER_REASON'] = 'Remove occupants to change room gender.';
            }
        }

        //Always show the option to set the default gender
        $form->addDropBox('default_gender', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('default_gender', $this->room->default_gender);

        $form->addCheck('is_online', 1);
        $form->setMatch('is_online', $this->room->is_online);

        $form->addCheck('is_reserved', 1);
        $form->setMatch('is_reserved', $this->room->is_reserved);

        $form->addCheck('ra_room', 1);
        $form->setMatch('ra_room', $this->room->ra_room);

        $form->addCheck('private_room', 1);
        $form->setMatch('private_room', $this->room->private_room);

        $form->addCheck('is_medical', 1);
        $form->setMatch('is_medical', $this->room->is_medical);

        $form->addCheck('is_overflow', 1);
        $form->setMatch('is_overflow', $this->room->is_overflow);

        $form->addSubmit('submit', 'Submit');

        # TODO: add an assignment pager here
        $tpl['BED_PAGER'] = HMS_Bed::bed_pager_by_room($this->room->id);

        # if the user has permission to view the form but not edit it then
        # disable it
        if(    Current_User::allow('hms', 'room_view')
        && !Current_User::allow('hms', 'room_attributes')
        && !Current_User::allow('hms', 'room_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Edit Room");

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_room.tpl');
    }
}

?>