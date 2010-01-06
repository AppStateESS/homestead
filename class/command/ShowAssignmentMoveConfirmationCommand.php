<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class ShowAssignmentMoveConfirmationCommand extends Command {

    private $username;
    private $room;
    private $bed;
    private $mealPlan;

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setRoom($room){
        $this->room = $room;
    }

    public function setBed($bed){
        $this->bed = $bed;
    }

    public function setMealPlan($plan){
        $this->mealPlan = $plan;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowAssignmentMoveConfirmation');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        if(isset($this->room)){
            $vars['room'] = $this->room;
        }

        if(isset($this->bed)){
            $vars['bed'] = $this->bed;
        }

        if(isset($this->mealPlan)){
            $vars['meal_plan'] = $this->mealPlan;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to assign students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'AssignmentMoveConfirmationView.php');
        	
        $student = StudentFactory::getStudentByUsername($context->get('username'), Term::getSelectedTerm());
        $assignment = HMS_Assignment::get_assignment($student->getUsername(), Term::getSelectedTerm());

        $moveConfirmView = new AssignmentMoveConfirmationView($student,
        $assignment,
        $context->get('residence_hall'),
        $context->get('room'),
        $context->get('bed'),
        $context->get('meal_plan'));

        $context->setContent($moveConfirmView->show());
    }
}