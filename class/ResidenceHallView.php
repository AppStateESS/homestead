<?php

PHPWS_Core::initModClass('hms', 'View.php');

class ResidenceHallView extends View {
	
	private $hall;
	
	public function __construct(HMS_Residence_Hall $hall){
		$this->hall = $hall;
	}
	
	public function show()
	{
        if(!UserStatus::isAdmin()){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You are not allowed to view residence halls');
        }

		PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        javascript('/jquery_ui/');

        # Setup the title and color of the title bar
        $tpl['TITLE'] = $this->hall->getHallName() . ' - ' . Term::getPrintableSelectedTerm();
        
        $submitCmd = CommandFactory::getCommand('EditResidenceHall');
        $submitCmd->setHallId($this->hall->getId());
        
        $form = new PHPWS_Form;
        $submitCmd->initForm($form);

        $form->addHidden('beds_per_room', $this->hall->count_beds_per_room()); // add a hidden field for beds per room
        
        $form->addText('hall_name', $this->hall->hall_name);
  
        $tpl['NUMBER_OF_FLOORS']        = $this->hall->get_number_of_floors();
        $tpl['NUMBER_OF_ROOMS']         = $this->hall->get_number_of_rooms();
        $tpl['NUMBER_OF_BEDS']          = $this->hall->get_number_of_beds();
        $tpl['NUMBER_OF_BEDS_ONLINE']   = $this->hall->get_number_of_online_nonoverflow_beds();
        $tpl['NUMBER_OF_ASSIGNEES']     = $this->hall->get_number_of_assignees();

        $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('gender_type', $this->hall->gender_type);

        $form->addText('rooms_for_lottery', $this->hall->rooms_for_lottery);
        $form->setSize('rooms_for_lottery', 3, 3);
        
        $form->addCheckBox('air_conditioned', 1);
        $form->setMatch('air_conditioned', $this->hall->air_conditioned);
      
        $form->addCheckBox('is_online', 1);
        $form->setMatch('is_online', $this->hall->is_online);

        $form->addCheckBox('meal_plan_required', 1);
        $form->setMatch('meal_plan_required', $this->hall->meal_plan_required);

        $form->addCheckBox('assignment_notifications', 1);
        $form->setMatch('assignment_notifications', $this->hall->assignment_notifications);

        // Images
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        if(isset($this->hall->exterior_image_id)){
            $manager = Cabinet::fileManager('exterior_image_id', $this->hall->exterior_image_id);
        }else{
            $manager = Cabinet::fileManager('exterior_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('EXTERIOR_IMG', $manager->get());


        if(isset($this->hall->other_image_id)){
            $manager = Cabinet::fileManager('other_image_id', $this->hall->other_image_id);
        }else{
            $manager = Cabinet::fileManager('other_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('OTHER_IMG', $manager->get());

        if(isset($this->hall->map_image_id)){
            $manager = Cabinet::fileManager('map_image_id', $this->hall->map_image_id);
        }else{
            $manager = Cabinet::fileManager('map_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('MAP_IMG', $manager->get());

        if(isset($this->hall->room_plan_image_id)){
            $manager = Cabinet::fileManager('room_plan_image_id', $this->hall->room_plan_image_id);
        }else{
            $manager = Cabinet::fileManager('room_plan_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('ROOM_PLAN_IMG', $manager->get());

        $form->addSubmit('submit', _('Save Hall'));
     
        # if the user has permission to view the form but not edit it then
        # disable it
        if(    Current_User::allow('hms', 'hall_view') 
           && !Current_User::allow('hms', 'hall_attributes')
           && !Current_User::allow('hms', 'hall_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }
   
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        //$tpl['FLOOR_PAGER'] = HMS_Floor::get_pager_by_hall($this->hall->getId());
		javascript('modules/hms/role_editor');
        $tpl['ROLE_EDITOR'] = PHPWS_Template::process(array('CLASS_NAME'=>"'HMS_Residence_Hall'", 'ID'=>$this->hall->id), 'hms', 'admin/role_editor.tpl');

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_residence_hall.tpl');
	}
}

?>
