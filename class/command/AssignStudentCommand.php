<?php

/**
 * Controller responsible for handling a request to assign a student
 *
 * @author jbooker
 * @package HMS
 */
class AssignStudentCommand extends Command {

    private $username;
    private $room;
    private $bed;
    private $moveConfirmed;
    private $assignmentType;
    private $notes;

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

    public function setMoveConfirmed($move){
        $this->moveConfirmed = $move;
    }

    public function setAssignmentType($type){
        $this->assignmentType = $type;
    }

    public function setNotes($notes){
        $this->notes = $notes;
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

        if(isset($this->moveConfirmed)){
            $vars['moveConfirmed'] = $this->moveConfirmed;
        }

        if(isset($this->assignmentType)){
            $vars['assignment_type'] = $this->assignmentType;
        }

        if(isset($this->notes)){
            $vars['note'] = $this->notes;
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
        PHPWS_Core::initModClass('hms', 'ContractFactory.php');
        PHPWS_Core::initModClass('hms', 'Contract.php');

        // NB: Username must be all lowercase
        $username = strtolower(trim($context->get('username')));
        $term = Term::getSelectedTerm();

        // Setup command to redirect to in case of error
        $errorCmd = CommandFactory::getCommand('ShowAssignStudent');
        $errorCmd->setUsername($username);

        /***
         * Input Sanity Checking
         */

        // Must supply a user name
        if(is_null($username)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Invalid or missing username.');
            $errorCmd->redirect();
        }

        // Must supply at least a room ID
        $roomId = $context->get('room');
        if(is_null($roomId) || $roomId == 0){
            NQ::simple('hms', hms\NotificationView::ERROR, 'You must select a room.');
            $errorCmd->redirect();
        }

        // Must choose an assignment type
        $assignmentType = $context->get('assignment_type');
        if(!isset($assignmentType) || is_null($assignmentType) || $assignmentType < 0){
            NQ::simple('hms', hms\NotificationView::ERROR, 'You must choose an assignment type.');
            $errorCmd->redirect();
        }

        // Lookup the student in Banner
        try{
            $student = StudentFactory::getStudentByUsername($username, $term);
        }catch(StudentNotFoundException $e){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Invalid user name, no such student found.');
            $errorCmd->redirect();
        }

        // Check to make sure the student has an application on file
        //$applicationStatus = HousingApplication::checkForApplication($username, $term);
        $housingApplication = HousingApplicationFactory::getAppByStudent($student, $term);

        if($housingApplication === null){
            NQ::simple('hms', hms\NotificationView::WARNING, 'Warning: No housing application found for this student in this term.');
        }

        // If the student is already assigned, redirect to the confirmation screen. If the student is already assigned
        // and the confirmation flag is true, then set a flag and proceed.
        $moveNeeded = FALSE;
        if(HMS_Assignment::checkForAssignment($username, $term)){
            if($context->get('moveConfirmed') == 'true'){
                // Move has been confirmed
                $moveNeeded = true;
            }else{
                // Redirect to the move confirmation interface
                $moveConfirmCmd = CommandFactory::getCommand('ShowAssignmentMoveConfirmation');
                $moveConfirmCmd->setUsername($username);
                $moveConfirmCmd->setRoom($context->get('room'));
                $moveConfirmCmd->setBed($context->get('bed'));
                $moveConfirmCmd->setAssignmentType($assignmentType);
                $moveConfirmCmd->setNotes($context->get('note'));
                $moveConfirmCmd->redirect();
            }
        }


        // Check age, issue a warning for over 25
        if(strtotime($student->getDOB()) < strtotime("-25 years")){
            NQ::simple('hms', hms\NotificationView::WARNING, 'Student is 25 years old or older!');
        }

        $gender = $student->getGender();

        if(!isset($gender) || is_null($gender)){
            throw new InvalidArgumentException('Missing student gender.');
        }

        // Create the room object so we can check gender
        $room = new HMS_Room($roomId);
        if(!$room){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Error creating the room object.');
            $errorCmd->redirect();
        }

        // Create the hall object for later
        $floor  = $room->get_parent();
        $hall   = $floor->get_parent();

        // If the room is Co-ed, make sure the user has permission to assign to co-ed rooms
        if($room->getGender() == COED && !Current_User::allow('hms', 'coed_assignment')){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Error: You do not have permission to assign students to co-ed rooms.');
            $errorCmd->redirect();
        }

        // Make sure the student's gender matches the gender of the room, unless the room is co-ed.
        if($room->getGender() != $gender && $room->getGender() != COED){
            // Room gender does not match student's gender, so check if we can change it
            if($room->can_change_gender($gender) && Current_User::allow('hms', 'room_attributes')){
                $room->setGender($gender);
                $room->save();
                NQ::simple('hms', hms\NotificationView::WARNING, 'Warning: Changing room gender.');
            }else{
                NQ::simple('hms', hms\NotificationView::ERROR, 'Error: The student\'s gender and the room\'s gender do not match and the room could not be changed.');
                $errorCmd->redirect();
            }
        }

        // If the user is attempting to re-assign and has confirmed the move,
        // then unassign the student first.
        if($moveNeeded){
            try{
                //TODO don't hard-code refund percentage to 100%
                HMS_Assignment::unassignStudent($student, $term, '(re-assign)', UNASSIGN_REASSIGN, 100);
            }catch(Exception $e){
                NQ::simple('hms', hms\NotificationView::ERROR, "Error deleting current assignment. {$username} was not removed.");
                $errorCmd->redirect();
            }
        }

        // Actually try to make the assignment, decide whether to use the room id or the bed id
        $bed = $context->get('bed');
        try {
            if(isset($bed) && $bed != 0){
                HMS_Assignment::assignStudent($student, $term, NULL, $bed, $context->get('note'), false, $context->get('assignment_type'));
            }else{
                HMS_Assignment::assignStudent($student, $term, $context->get('room'), NULL, $context->get('note'), false, $context->get('assignment_type'));
            }
        } catch(AssignmentException $e) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Assignment error: ' . $e->getMessage());
            $errorCmd->redirect();
        }



        /*******************************************
         * Check for a valid and complete contract *
         *******************************************/
        $contract = ContractFactory::getContractByStudentTerm($student, $term);

        if($contract === false){
            // No contract exists. Create a new one and send it via email to the student
            if($this->sendContract($student, $housingApplication, $term)){
                HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CONTRACT_SENT_EMAIL, UserStatus::getUsername(), "Sent new contract via email for $term");
                NQ::simple('hms', hms\NotificationView::WARNING, 'No contract found for this semester. A new contract signing request was sent to the student via email.');
            }else{
                NQ::simple('hms', hms\NotificationView::ERROR, 'Could not send contract. Student is under 18, but we don\'t know who the parent/guardian is.');
            }

        } else {
            // Contract exists. Refresh its status and send a new one if necessary
            $contract->updateEnvelope();
            $envStatus = $contract->getEnvelopeStatus(); // Refresh status

            if ($envStatus === Contract::STATUS_COMPLETED){
                // Contract is complete already. We're good to go.
                NQ::simple('hms', hms\NotificationView::SUCCESS, 'This student has a valid contract for this semester.');

            } else if($envStatus === Contract::STATUS_VOIDED || $envStatus === Contract::STATUS_DECLINED){
                // Contract is voided. Ignore the current contract and send them another.
                // Delete the current contract
                ContractFactory::deleteContract($contract);
                HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CONTRACT_REMOVED_VOIDED, UserStatus::getUsername(), "Removed student's existing voided contract for $term, so a new contract can be sent.");

                // Send a new contract
                $this->sendContract($student, $housingApplication, $term);
                NQ::simple('hms', hms\NotificationView::WARNING, 'A voided contract was found for this semester. A new contract signing request was sent to the student via email.');
                HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CONTRACT_SENT_EMAIL, UserStatus::getUsername(), "Sent new contract via email for $term");
            } else {
                // Contract exists, but is in some other pending status (sent, delivered). We don't need to do anything, hopefully student will complete the existing contract.
                NQ::simple('hms', hms\NotificationView::INFO, 'This student has a pending contract for this semester. A new contract was not needed.');
            }
        }


        /*************
         * Meal Plan *
         *************/
        // Check for a meal plan and create one based on the application, if needed
        $this->setupMealPlan($student, $term, $housingApplication);


        // Show a success message
        if($context->get('moveConfirmed') == 'true'){
            NQ::simple('hms', hms\NotificationView::SUCCESS, 'Successfully moved ' . $username . ' to ' . $hall->hall_name . ' room ' . $room->room_number);
        }else{
            NQ::simple('hms', hms\NotificationView::SUCCESS, 'Successfully assigned ' . $username . ' to ' . $hall->hall_name . ' room ' . $room->room_number);
        }

        $successCmd = CommandFactory::getCommand('ShowAssignStudent');
        $successCmd->redirect();
    }

    /**
     * Sends a contract based on student, HousingApplication, and term. Returns false if the contact could not be sent.
     */
    private function sendContract(Student $student, HousingApplication $housingApplication = null, $term)
    {
        // Decide which envelope template to use based on student's age
        if($student->isUnder18()){
            // Look up the student's application (if any) and try to pull parent/guardian info from the application before failing to send
            if($housingApplication === null){
                return false; // We don't know who the parent/guardian is
            }

            $parentName = $housingApplication->getEmergencyContactName();
            $parentEmail = $housingApplication->getEmergencyContactEmail();

            if($parentName === '' || $parentEmail === '' || $parentName === null || $parentEmail === null){
                return false; // We don't know who the parent/guardian is
            }

            ContractFactory::sendContractUnder18($student, $term, $parentName, $parentEmail);
        } else {
            // Over 18, use the regular template
            ContractFactory::sendContractOver18($student, $term);
        }

        return true;
    }

    private function setupMealPlan(Student $student, $term, HousingApplication $housingApplication = null)
    {
        // Check for a meal plan, if one exists, don't do anything
        $mealPlan = MealPlanFactory::getMealByBannerIdTerm($student->getBannerId(), $term);

        if($mealPlan !== null){
            // Meal plan exists, so we're done here
            return;
        }

        // If the student doesn't have a housing Application, then they're getting the standard plan
        if($housingApplication === null){
            $planCode = MealPlan::BANNER_MEAL_STD;
        } else {
            $planCode = $housingApplication->getMealPlan();
        }

        // If the term is summer 1 or summer 2, then we always use the summer plan
        $semester = Term::getTermSem($term);
        if($semester == TERM_SUMMER1 || $semester == TERM_SUMMER2){
            $planCode = MealPlan::BANNER_MEAL_SUMMER;
        }

        // If the student selected the 'none' plan, then we're done here
        if($planCode === MealPlan::BANNER_MEAL_NONE){
            return;
        }

        // Make a new MealPlan object
        $mealPlan = new MealPlan($student->getBannerId(), $term, $planCode);

        // Process the meal plan, if needed
        $soap = SOAP::getInstance(UserStatus::getUsername(), SOAP::ADMIN_USER);
        MealPlanProcessor::queueMealPlan($mealPlan, $soap);
    }
}
