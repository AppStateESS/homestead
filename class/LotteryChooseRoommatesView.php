<?php

class LotteryChooseRoommatesView extends View {

    private $student;
    private $term;
    private $roomId;

    public function __construct(Student $student, $term, $roomId)
    {
        $this->student  = $student;
        $this->term     = $term;
        $this->roomId   = $roomId;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');

        javascript('jquery');

        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $tpl = array();

        #TODO: place a temporary reservation on the entire room

        # Grab all of their preferred roommates
        $lotteryApplication = HousingApplication::getApplicationByUser($this->student->getUsername(), $this->term);

        # List each bed in the room and if it's available, assigned, or reserved
        $room = new HMS_Room($this->roomId);
        $beds = $room->get_beds();

        $tpl['ROOM'] = $room->where_am_i();

        $form = new PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('LotteryChooseRoommates');
        $submitCmd->setRoomId($this->roomId);
        $submitCmd->initForm($form);

        $assigned_self = FALSE; // Whether or not we've placed *this* student in a bed yet

        // Search the request to see if the student has already assigned themselves previously (this is only used if the user is being
        // set back from a subsequent page after an error).
        if(isset($_REQUEST['roommates']) && !(array_search($this->student->getUsername(), $_REQUEST['roommates']) === FALSE)){
            $assigned_self = TRUE;
        }

        for($i = 0; $i < count($beds); $i++){
            $bed = $beds[$i];
            $bed_row = array();

            $bed_row['BEDROOM_LETTER']  = $bed->bedroom_label;

            # Check for an assignment
            $bed->loadAssignment();
            # Check for a reservation
            $reservation = $bed->get_lottery_reservation_info();

            if($bed->_curr_assignment != NULL){
                # Bed is assigned, so show who's in it
                $assignedStudent = StudentFactory::getStudentByUsername($bed->_curr_assignment->asu_username, $this->term);
                $bed_row['TEXT'] = $assignedStudent->getName() . ' (assigned)';
            }else if($reservation != NULL){
                # Bed is reserved
                $reservedStudent = StudentFactory::getStudentByUsername($reservation['asu_username'], $this->term);
                $bed_row['TEXT'] = $reservedStudent->getName() . ' (unconfirmed invitation)';
            }else{
                # Bed is empty, so decide what we should do with it
                if(isset($_REQUEST['roommates'][$bed->id])){
                    # The user already submitted the form once, put the value in the request in the text box by default
                    $bed_row['TEXT'] = "<input type=\"text\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\" value=\"{$_REQUEST['roommates'][$bed->id]}\">@appstate.edu";
                }else if(!$assigned_self){
                    # No value in the request, this bed is empty, and this user hasn't been assigned anywhere yet
                    # So put their user name in this field by default
                    $bed_row['TEXT'] = "<input type=\"text\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\" value=\"{$this->student->getUsername()}\">@appstate.edu";
                    $assigned_self = TRUE;
                }else{
                    $bed_row['TEXT'] = "<input type=\"text\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\">@appstate.edu";
                }
            }

            $tpl['beds'][] = $bed_row;
        }

        # Decide which meal plan drop box to show based on whether or not the chosen room
        # is in a hall which requires a meal plan
        $floor  = $room->get_parent();
        $hall   = $floor->get_parent();
        if($hall->meal_plan_required == 0){
            $form->addDropBox('meal_plan', array(BANNER_MEAL_NONE   =>_('None'),
            BANNER_MEAL_LOW    =>_('Low'),
            BANNER_MEAL_STD    =>_('Standard'),
            BANNER_MEAL_HIGH   =>_('High'),
            BANNER_MEAL_SUPER  =>_('Super')));
        }else{
            $form->addDropBox('meal_plan', array(BANNER_MEAL_LOW    =>_('Low'),
            BANNER_MEAL_STD    =>_('Standard'),
            BANNER_MEAL_HIGH   =>_('High'),
            BANNER_MEAL_SUPER  =>_('Super')));
        }

        $form->setMatch('meal_plan', $lotteryApplication->getMealPlan());

        $form->addSubmit('submit_form', 'Review Roommate & Room Selection');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Lottery Choose Roommate");

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_select_roommate.tpl');
    }
}