<?php

PHPWS_Core::initModClass('hms', 'View.php');

class BedView extends View {

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
		PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
		PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
		PHPWS_Core::initModClass('hms', 'HMS_Room.php');
		PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
		PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
		PHPWS_Core::initModClass('hms', 'HMS_Pricing_Tier.php');
		PHPWS_Core::initModClass('hms', 'HMS_Util.php');

		$tpl['TITLE'] = $this->room->room_number . ' - ' . $this->bed->bedroom_label . $this->bed->bed_letter . ' - ' . $this->hall->hall_name;

		$tpl['HALL_NAME']           = $this->hall->getLink();
		$tpl['FLOOR_NUMBER']        = $this->floor->getLink();
		$tpl['ROOM_NUMBER']         = $this->room->getLink();
		$tpl['BED_LETTER']          = $this->bed->bed_letter;

		$tpl['ASSIGNED_TO'] = $this->bed->get_assigned_to_link();

		$submitCmd = CommandFactory::getCommand('EditBed');
		$submitCmd->setBedId($this->bed->id);
		
		$form = new PHPWS_Form();
		$submitCmd->initForm($form);

		$form->addText('bedroom_label', $this->bed->bedroom_label);

		$form->addText('phone_number', $this->bed->phone_number);
		$form->setMaxSize('phone_number', 4);
		$form->setSize('phone_number', 5);

		$form->addText('banner_id', $this->bed->banner_id);

		$form->addCheckBox('ra_bed', 1);

		if($this->bed->ra_bed == 1){
			$form->setExtra('ra_bed', 'checked');
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

		return PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
	}
}

?>