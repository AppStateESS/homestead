<?php

namespace Homestead;

use \Homestead\Exception\PermissionException;

class ResidenceHallView extends View {

    private $hall;

    public function __construct(ResidenceHall $hall){
        $this->hall = $hall;
    }

    public function show()
    {
        if(!UserStatus::isAdmin()){
            throw new PermissionException('You are not allowed to view residence halls');
        }

        javascript('jquery_ui');

        $tpl = array();

        # Setup the title and color of the title bar
        $tpl['TITLE']   = $this->hall->getHallName();
        $tpl['TERM']    = Term::getPrintableSelectedTerm();

        if (!$this->hall->isOnline()) {
            $tpl['OFFLINE'] = '';
        }

        $submitCmd = CommandFactory::getCommand('EditResidenceHall');
        $submitCmd->setHallId($this->hall->getId());

        $form = new \PHPWS_Form;
        $submitCmd->initForm($form);

        // This is unused, as far as I can tell, so comment it out for now.
        //$form->addHidden('beds_per_room', $this->hall->count_beds_per_room()); // add a hidden field for beds per room

        $form->addText('hall_name', $this->hall->hall_name);
        $form->addCssClass('hall_name', 'form-control');

        $tpl['NUMBER_OF_FLOORS']        = $this->hall->get_number_of_floors();
        $tpl['NUMBER_OF_ROOMS']         = $this->hall->get_number_of_rooms();
        $tpl['NUMBER_OF_BEDS']          = $this->hall->get_number_of_beds();
        $tpl['NUMBER_OF_BEDS_ONLINE']   = $this->hall->get_number_of_online_nonoverflow_beds();
        $tpl['NUMBER_OF_ASSIGNEES']     = $this->hall->get_number_of_assignees();

        $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('gender_type', $this->hall->gender_type);
        $form->addCssClass('gender_type', 'form-control');

        $form->addCheckBox('air_conditioned', 1);
        $form->setMatch('air_conditioned', $this->hall->air_conditioned);

        $form->addCheckBox('is_online', 1);
        $form->setMatch('is_online', $this->hall->is_online);

        $form->addCheckBox('meal_plan_required', 1);
        $form->setMatch('meal_plan_required', $this->hall->meal_plan_required);

        $form->addCheckBox('assignment_notifications', 1);
        $form->setMatch('assignment_notifications', $this->hall->assignment_notifications);

        //Package Desks

        $packageDesks = PackageDeskFactory::getPackageDesksAssoc();
        $packageDesks = array('-1' => 'None') + $packageDesks;
        $form->addDropBox('package_desk', $packageDesks);
        $form->setMatch('package_desk', $this->hall->getPackageDeskId());
        $form->addCssClass('package_desk', 'form-control');

        // Images
        \PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        if(isset($this->hall->exterior_image_id)){
            $manager = \Cabinet::fileManager('exterior_image_id', $this->hall->exterior_image_id);
        }else{
            $manager = \Cabinet::fileManager('exterior_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('EXTERIOR_IMG', $manager->get());


        if(isset($this->hall->other_image_id)){
            $manager = \Cabinet::fileManager('other_image_id', $this->hall->other_image_id);
        }else{
            $manager = \Cabinet::fileManager('other_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('OTHER_IMG', $manager->get());

        if(isset($this->hall->map_image_id)){
            $manager = \Cabinet::fileManager('map_image_id', $this->hall->map_image_id);
        }else{
            $manager = \Cabinet::fileManager('map_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('MAP_IMG', $manager->get());

        if(isset($this->hall->room_plan_image_id)){
            $manager = \Cabinet::fileManager('room_plan_image_id', $this->hall->room_plan_image_id);
        }else{
            $manager = \Cabinet::fileManager('room_plan_image_id');
        }

        $manager->maxImageWidth(300);
        $manager->MaxImageHeight(300);
        $manager->imageOnly(false,false);
        $form->addTplTag('ROOM_PLAN_IMG', $manager->get());

        # if the user has permission to view the form but not edit it then
        # disable it
        if(    \Current_User::allow('hms', 'hall_view')
        && !\Current_User::allow('hms', 'hall_attributes')
        && !\Current_User::allow('hms', 'hall_structure'))
        {
            $elements = $form->getAllElements();
            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        $tpl['FLOOR_PAGER'] = Floor::get_pager_by_hall($this->hall->getId());
        javascript('modules/hms/role_editor');
        $tpl['ROLE_EDITOR'] = \PHPWS_Template::process(array('CLASS_NAME'=>"'ResidenceHall'", 'ID'=>$this->hall->id), 'hms', 'admin/role_editor.tpl');

        \Layout::addPageTitle("Edit Residence Hall");

        return \PHPWS_Template::process($tpl, 'hms', 'admin/edit_residence_hall.tpl');
    }
}
