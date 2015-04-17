<?php

PHPWS_Core::initModClass('hms', 'View.php');

class AssignmentMoveConfirmationView extends hms\View {

    private $student;
    private $assignment;
    private $residenceHall;
    private $room;
    private $bed;
    private $mealPlan;
    private $assignmentType;
    private $notes;

    public function __construct(Student $student, HMS_Assignment $assignment, $residenceHall, $room, $bed, $mealPlan, $assignmentType, $notes)
    {
        $this->student			= $student;
        $this->assignment		= $assignment;
        $this->residenceHall	= $residenceHall;
        $this->room 			= $room;
        $this->bed 				= $bed;
        $this->mealPlan 		= $mealPlan;
        $this->assignmentType   = $assignmentType;
        $this->notes            = $notes;
    }

    public function show()
    {
        $tpl = array();

        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        $tpl['NAME'] = $this->student->getFullName();
        $tpl['LOCATION'] = $this->assignment->where_am_i();

        $submitCmd = CommandFactory::getCommand('AssignStudent');
        $submitCmd->setUsername($this->student->getUsername());
        $submitCmd->setRoom($this->room);
        $submitCmd->setBed($this->bed);
        $submitCmd->setMealPlan($this->mealPlan);
        $submitCmd->setMoveConfirmed("true");
        $submitCmd->setAssignmentType($this->assignmentType);
        $submitCmd->setNotes($this->notes);

        $form = new PHPWS_Form();
        $submitCmd->initForm($form);

        $form->addButton('cancel', 'Cancel');
        $form->addSubmit('submit', 'Confirm Move');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Assignment Move Confirmation");

        return PHPWS_Template::process($tpl, 'hms', 'admin/assign_student_move_confirm.tpl');
    }
}

?>