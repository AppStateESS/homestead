<?php

class AssignStudentCommand extends Command {

    private $username;
    private $room;
    private $bed;
    private $mealPlan;
    private $moveConfirmed;

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

    public function setMoveConfirmed($move){
        $this->moveConfirmed = $move;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'AssignStudent');

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

        if(isset($this->moveConfirmed)){
            $vars['moveConfirmed'] = $this->moveConfirmed;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to assign students.');
        }


        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        PHPWS_Core::initModClass('hms', 'BannerQueue.php');

        $username = strtolower(trim($context->get('username')));
        $term = Term::getSelectedTerm();

        $errorCmd = CommandFactory::getCommand('ShowAssignStudent');
        $errorCmd->setUsername($username);

        if(is_null($username)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid or missing username.');
            $errorCmd->redirect();
        }

        $roomId = $context->get('room');

        if(is_null($roomId) || $roomId == 0){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must select a room.');
            $errorCmd->redirect();
        }

        # Check to make sure the student has an application on file
        $applicationStatus = HousingApplication::checkForApplication($username, $term);

        if($applicationStatus == FALSE){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Warning: No housing application found for this student in this term.');
        }else{
            # Create application object (it doesn't really matter that we always create a fall application)
            $application = new HousingApplication($applicationStatus['id']);
        }

        # If the student is already assigned, redirect to the confirmation screen. If the student is already assigned
        # and the confirmation flag is true, then set a flag and proceed.
        $moveNeeded = FALSE;
        if(HMS_Assignment::checkForAssignment($username, $term)){
            if($context->get('moveConfirmed') == 'true'){
                # Move has been confirmed
                $moveNeeded = true;
            }else{
                # Redirect to the move confirmation interface
                $moveConfirmCmd = CommandFactory::getCommand('ShowAssignmentMoveConfirmation');
                $moveConfirmCmd->setUsername($username);
                $moveConfirmCmd->setRoom($context->get('room'));
                $moveConfirmCmd->setBed($context->get('bed'));
                $moveConfirmCmd->setMealPlan($context->get('meal_plan'));
                $moveConfirmCmd->redirect();
            }
        }

        try{
            $student = StudentFactory::getStudentByUsername($username, $term);
        }catch(StudentNotFoundException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid user name, no such student found.');
            $errorCmd->redirect();
        }

        # Check age, issue a warning for over 25
        if(strtotime($student->getDOB()) < strtotime("-25 years")){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Student is 25 years old or older!');
        }

        $gender = $student->getGender();

        if(!isset($gender) || is_null($gender)){
            throw new InvalidArgumentException('Missing student gender.');
        }

        # Create the room object so we can check gender
        $room = new HMS_Room($roomId);
        if(!$room){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error creating the room object.');
            $errorCmd->redirect();
        }

        # Create the hall object for later
        $floor  = $room->get_parent();
        $hall   = $floor->get_parent();

        # Make sure the student's gender matches the gender of the room.
        if($room->gender_type != $gender){
            // Room gender does not match student's gender, so check if we can change it
            if($room->can_change_gender($gender) && Current_User::allow('hms', 'room_attributes')){
                $room->gender_type = $gender;
                $room->save();
                NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Warning: Changing room gender.');
            }else{
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: The student\'s gender and the room\'s gender do not match and the room could not be changed.');
                $errorCmd->redirect();
            }
        }

        # If the user is attempting to re-assign and has confirmed the move,
        # then unassign the student first.
        if($moveNeeded){
            try{
                HMS_Assignment::unassignStudent($student, $term, '(re-assign)');
            }catch(Exception $e){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Error deleting current assignment. {$username} was not removed.");
                $errorCmd->redirect();
            }
        }

        # Actually try to make the assignment, decide whether to use the room id or the bed id
        $bed = $context->get('bed');
        try{
            if(isset($bed) && $bed != 0){
                $assign_result = HMS_Assignment::assignStudent($student, $term, NULL, $bed, $context->get('meal_plan'), $context->get('note'), false, $context->get('assignment_type'));
            }else{
                $assign_result = HMS_Assignment::assignStudent($student, $term, $context->get('room'), NULL, $context->get('meal_plan'), $context->get('note'), false, $context->get('assignment_type'));
            }
        }catch(AssignmentException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Assignment error: ' . $e->getMessage());
            $errorCmd->redirect();
        }

        # Show a success message
        if($context->get('moveConfirmed') == 'true'){
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Successfully moved ' . $username . ' to ' . $hall->hall_name . ' room ' . $room->room_number);
        }else{
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Successfully assigned ' . $username . ' to ' . $hall->hall_name . ' room ' . $room->room_number);
        }

        $successCmd = CommandFactory::getCommand('ShowAssignStudent');
        $successCmd->redirect();
    }
}
