<?php

PHPWS_Core::initModClass('hms', 'View.php');

class AssignStudentView extends View {
    private $student;
    private $bed;

    public function __construct(Student $student = NULL, HMS_Bed $bed = NULL){

        $this->student	= $student;
        $this->bed		= $bed;
    }

    public function show()
    {
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        javascript('jquery');
        javascript('modules/hms/assign_student');
        Layout::addStyle('hms', 'css/autosuggest2.css');

        $tpl = array();
        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        $form = new PHPWS_Form();

        $assignCmd = CommandFactory::getCommand('AssignStudent');
        $assignCmd->initForm($form);

        $form->addText('username');
        $form->setLabel('username', 'ASU Username: ');
        if(isset($this->student)){
            $form->setValue('username', $this->student->getUsername());
        }

        javascript('modules/hms/autoFocus', array('ELEMENT' => $form->getId('username')));

        $form->addTextarea('note');
        $form->setLabel('note', 'Note: ');

        $form->addHidden('term', Term::getSelectedTerm());

        # Check to see if a bed_id was passed in, this means
        # the user clicked an 'unassigned' link. We need to pre-populate
        # the drop downs.
        unset($pre_populate);

        if(isset($this->bed)){
            $pre_populate = true;

            $room = $this->bed->get_parent();
            $floor = $room->get_parent();
            $hall = $floor->get_parent();
        }else{
            $pre_populate = false;
        }

        $hallList = HMS_Residence_Hall::getHallsWithVacanciesArray(Term::getSelectedTerm());

        $form->addDropBox('residence_hall', $hallList);

        if($pre_populate){
            $form->setMatch('residence_hall', $hall->id);
        }else{
            $form->setMatch('residence_hall', 0);
        }
        $form->setLabel('residence_hall', 'Residence hall: ');

        if($pre_populate){
            $form->addDropBox('floor', $hall->get_floors_array());
            $form->setMatch('floor', $floor->id);
        }else{
            $form->addDropBox('floor', array(0 => ''));
        }
        $form->setLabel('floor', 'Floor: ');

        if($pre_populate){
            $form->addDropBox('room', $floor->get_rooms_array());
            $form->setMatch('room', $room->id);
        }else{
            $form->addDropBox('room', array(0 => ''));
        }
        $form->setLabel('room', 'Room: ');

        if($pre_populate){
            $form->addDropBox('bed', $room->get_beds_array());
            $form->setMatch('bed', $this->bed->id);
            $show_bed_drop = true;
        }else{
            $form->addDropBox('bed', array(0 => ''));
            $show_bed_drop = false;
        }
        $form->setLabel('bed', 'Bed: ');

        if($show_bed_drop){
            $tpl['BED_STYLE'] = '';
            $tpl['LINK_STYLE'] = 'display: none';
        }else{
            $tpl['BED_STYLE'] = 'display: none';
            $tpl['LINK_STYLE'] = '';
        }

        $form->addDropBox('meal_plan', array(BANNER_MEAL_LOW   => 'Low',
        BANNER_MEAL_STD   => 'Standard',
        BANNER_MEAL_HIGH  => 'High',
        BANNER_MEAL_SUPER => 'Super',
        BANNER_MEAL_NONE  => 'None',
        // 4 Week Meal Plan Removed according to ticket #709
        // BANNER_MEAL_4WEEK => 'Summer (4 weeks)',
        BANNER_MEAL_5WEEK => 'Summer (5 weeks)'));
        $form->setMatch('meal_plan', BANNER_MEAL_STD);
        $form->setLabel('meal_plan', 'Meal plan: ');

        $form->addSubmit('submit', 'Assign Student');

        if($pre_populate){
            $form->addHidden('use_bed', 'true');
        }else{
            $form->addHidden('use_bed', 'false');
        }

        $form->mergeTemplate($tpl);

        $tpl = $form->getTemplate();

        Layout::addPageTitle("Assign Student");

        return PHPWS_Template::process($tpl, 'hms', 'admin/assign_student.tpl');
    }
}
