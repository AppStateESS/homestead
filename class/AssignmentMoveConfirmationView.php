<?php

PHPWS_Core::initModClass('hms', 'View.php');

class AssignmentMoveConfirmationView extends View {
	
	private $student;
	private $assignment;
	private $residenceHall;
	private $room;
	private $bed;
	private $mealPlan;
	
	public function __construct(Student $student, HMS_Assignment $assignment, $residenceHall, $room, $bed, $mealPlan)
	{
		$this->student			= $student;
		$this->assignment		= $assignment;
		$this->residenceHall	= $residenceHall;
		$this->room 			= $room;
		$this->bed 				= $bed;
		$this->mealPlan 		= $mealPlan;
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

		$form = new PHPWS_Form();
		$submitCmd->initForm($form);
		
		$form->addButton('cancel', 'Cancel');
		$form->addSubmit('submit', 'Confirm Move');
		
		$form->mergeTemplate($tpl);
		$tpl = $form->getTemplate();

		return PHPWS_Template::process($tpl, 'hms', 'admin/assign_student_move_confirm.tpl');
	}
}

?>