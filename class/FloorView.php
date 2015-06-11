<?php

class FloorView extends hms\View{

    private $hall;
    private $floor;

    public function __construct(HMS_Residence_Hall $hall, HMS_Floor $floor){
        $this->hall		= $hall;
        $this->floor	= $floor;
    }

    public function show()
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'floor_view')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You are not allowed to edit or view floors.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        javascript('jquery_ui');

        $floor_num = $this->floor->getFloorNumber();

        // Setup the title and color of the title bar
        $tpl['FLOOR_NUMBER'] = HMS_Util::ordinal($floor_num);
        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        $submitCmd = CommandFactory::getCommand('EditFloor');
        $submitCmd->setFloorId($this->floor->getId());

        $form = new PHPWS_Form;
        $submitCmd->initForm($form);

        $tpl['HALL_NAME']           = $this->hall->getLink();
        $tpl['NUMBER_OF_ROOMS']     = $this->floor->get_number_of_rooms();
        $tpl['NUMBER_OF_BEDS']      = $this->floor->get_number_of_beds();
        $tpl['NOMINAL_BEDS']        = $this->floor->countNominalBeds();
        $tpl['NUMBER_OF_ASSIGNEES'] = $this->floor->get_number_of_assignees();

        if(!$this->floor->isOnline()){
            $tpl['OFFLINE_ATTRIB'] = 'Offline';
        }

        $form->addDropBox('gender_type', array(FEMALE => FEMALE_DESC, MALE => MALE_DESC, COED => COED_DESC));
        $form->setMatch('gender_type', $this->floor->gender_type);
        $form->addCssClass('gender_type', 'form-control');

        $form->addCheck('is_online', 1);
        $form->setMatch('is_online', $this->floor->is_online);

        $movein_times = HMS_Movein_Time::get_movein_times_array();

        $form->addDropBox('f_movein_time', $movein_times);
        $form->addCssClass('f_movein_time', 'form-control');
        if(!isset($this->floor->f_movein_time_id)){
            $form->setMatch('f_movein_time', 0);
        }else{
            $form->setMatch('f_movein_time', $this->floor->f_movein_time_id);
        }

        $form->addDropBox('t_movein_time', $movein_times);
        $form->addCssClass('t_movein_time', 'form-control');
        if(!isset($this->floor->t_movein_time_id)){
            $form->setMatch('t_movein_time', 0);
        }else{
            $form->setMatch('t_movein_time', $this->floor->t_movein_time_id);
        }

        $form->addDropBox('rt_movein_time', $movein_times);
        $form->addCssClass('rt_movein_time', 'form-control');
        if(!isset($this->floor->rt_movein_time_id)){
            $form->setMatch('rt_movein_time', 0);
        }else{
            $form->setMatch('rt_movein_time', $this->floor->rt_movein_time_id);
        }

        // Get a list of the RLCs indexed by id
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $learning_communities = HMS_Learning_Community::getRlcList();
        $learning_communities[0] = 'None';

        $form->addDropBox('floor_rlc_id', $learning_communities);
        $form->addCssClass('floor_rlc_id', 'form-control');
        if(isset($this->floor->rlc_id)){
            $tpl['RLC_NAME'] = $learning_communities[$this->floor->rlc_id];
            $form->setMatch('floor_rlc_id', $this->floor->rlc_id);
        }else{
            $form->setMatch('floor_rlc_id', 0);
        }

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        if(isset($this->floor->floor_plan_image_id)){
            $manager = Cabinet::fileManager('floor_plan_image_id', $this->floor->floor_plan_image_id);
        }else{
            $manager = Cabinet::fileManager('floor_plan_image_id');
        }
        $manager->maxImageWidth(300);
        $manager->maxImageHeight(300);
        $manager->imageOnly(false, false);
        $form->addTplTag('FILE_MANAGER', $manager->get());

        $form->addHidden('type', 'floor');
        $form->addHidden('op', 'edit_floor');

        $tpl['STATIC_ROOM_PAGER'] = HMS_Room::room_pager_by_floor($this->floor->id);
        $tpl['DYNAMIC_ROOM_PAGER'] = HMS_Room::room_pager_by_floor($this->floor->id, true);

        // if the user has permission to view the form but not edit it then
        // disable it
        if( Current_User::allow('hms', 'floor_view')
        && !Current_User::allow('hms', 'floor_attributes')
        && !Current_User::allow('hms', 'floor_structure'))
        {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach($elements as $element => $value){
                $form->setDisabled($element);
            }
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(Current_User::allow('hms', 'edit_role_members')){
            javascript('modules/hms/role_editor');
            $tpl['ROLE_EDITOR'] = PHPWS_Template::process(array('CLASS_NAME'=>"'HMS_Floor'", 'ID'=>$this->floor->id), 'hms', 'admin/role_editor.tpl');
        }

        Layout::addPageTitle("Edit Floor");

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_floor.tpl');
    }
}

//
