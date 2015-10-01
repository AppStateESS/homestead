<?php

/**
 * View for showing the Assign Student interface.
 *
 * @author jbooker
 * @package hms
 */
class AssignStudentView extends hms\View {

    private $student;
    private $bed;
    private $application;

    /**
     *
     * @param Student $student
     * @param HMS_Bed $bed
     * @param HousingApplication $application
     */
    public function __construct(Student $student = null, HMS_Bed $bed = null, HousingApplication $application = null){

        $this->student     = $student;
        $this->bed         = $bed;
        $this->application = $application;
    }

    /**
     * (non-PHPdoc)
     * @see View::show()
     */
    public function show()
    {
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        if (!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        javascript('jquery');
        javascript('modules/hms/assign_student');
        javascriptMod('hms', 'student_assign');

        $tpl = array();
        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        $form = new PHPWS_Form();

        $assignCmd = CommandFactory::getCommand('AssignStudent');
        $assignCmd->initForm($form);

        $form->addHidden('term', Term::getSelectedTerm());

        $form->addText('username');
        $form->setLabel('username', 'ASU Username: ');
        if (isset($this->student)) {
            $form->setValue('username', $this->student->getUsername());
        }
        $form->addCssClass('username', 'form-control');
        $form->setExtra('username', 'autofocus');

        // Check to see if a bed_id was passed in, this means
        // the user clicked an 'unassigned' link. We need to pre-populate
        // the drop downs.
        unset($pre_populate);

        if (isset($this->bed)) {
            $pre_populate = true;

            $room = $this->bed->get_parent();
            $floor = $room->get_parent();
            $hall = $floor->get_parent();
        } else {
            $pre_populate = false;
        }

        $hallList = HMS_Residence_Hall::getHallsWithVacanciesArray(Term::getSelectedTerm());


        $prepop = array();
        if($pre_populate)
        {
          $prepop = array('hall_id'=> $hall->id, 'floor_id' => $floor->id, 'room_id' => $room->id, 'bed_id' => $this->bed->id);
        }

        if (isset($this->application)) {
            $meal_plan = array('meal_plan' => $this->application->getMealPlan());
        } else {
            // Otherwise, select 'standard' meal plan
            $meal_plan = array('meal_plan' => BANNER_MEAL_STD);
        }

        $tpl['PREPOPULATE'] = json_encode($prepop);
        $tpl['MEAL_PLAN'] = json_encode($meal_plan);

        $form->addTextarea('note');
        $form->setLabel('note', 'Note: ');
        $form->addCssClass('note', 'form-control');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Assign Student");

        return PHPWS_Template::process($tpl, 'hms', 'admin/assignStudent.tpl');
    }
}
