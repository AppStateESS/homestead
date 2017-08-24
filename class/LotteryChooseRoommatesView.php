<?php

namespace Homestead;

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
        javascript('jquery');

        $tpl = array();

        #TODO: place a temporary reservation on the entire room

        # Grab all of their preferred roommates
        $lotteryApplication = HousingApplication::getApplicationByUser($this->student->getUsername(), $this->term);

        # List each bed in the room and if it's available, assigned, or reserved
        $room = new HMS_Room($this->roomId);
        $beds = $room->get_beds();

        $tpl['ROOM'] = $room->where_am_i();

        $form = new \PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('LotteryChooseRoommates');
        $submitCmd->setRoomId($this->roomId);
        $submitCmd->initForm($form);

        $assigned_self = FALSE; // Whether or not we've placed *this* student in a bed yet

        // Search the request to see if the student has already assigned themselves previously (this is only used if the user is being
        // set back from a subsequent page after an error).
        if(isset($_REQUEST['roommates']) && !(array_search($this->student->getUsername(), $_REQUEST['roommates']) === FALSE)){
            $assigned_self = TRUE;
        }

        $bedCount = count($beds);

        for($i = 0; $i < $bedCount; $i++){
            $bed = $beds[$i];
            $bed_row = array();

            $bedLabel = $room->getRoomNumber();
            if($room->get_number_of_beds() == 4)
            {
              $bedLabel = $bedLabel . $bed->getBedroomLabel();
            }
            $bedLabel = $bedLabel . $bed->getLetter();


            $bed_row['BED_LABEL']  = $bedLabel;

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
            }else if($bed->isInternationalReserved() || $bed->isRaRoommateReserved() || $bed->isRa()){
                $bed_row['TEXT'] = "<input type=\"text\" class=\"form-control\" value=\"Reserved\" disabled>";
            }else{
                # Bed is empty, so decide what we should do with it
                if(isset($_REQUEST['roommates'][$bed->id])){
                    # The user already submitted the form once, put the value in the request in the text box by default
                    $bed_row['TEXT'] = "<input type=\"text\" class=\"form-control\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\" value=\"{$_REQUEST['roommates'][$bed->id]}\">";
                }else if(!$assigned_self){
                    # No value in the request, this bed is empty, and this user hasn't been assigned anywhere yet
                    # So put their user name in this field by default
                    $bed_row['TEXT'] = "<input type=\"text\" class=\"form-control\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\" value=\"{$this->student->getUsername()}\">";
                    $assigned_self = TRUE;
                }else{
                    $bed_row['TEXT'] = "<input type=\"text\" class=\"form-control\" name=\"roommates[{$bed->id}]\" class=\"roommate_entry\">";
                }
            }

            $tpl['beds'][] = $bed_row;
        }

        # Decide which meal plan drop box to show based on whether or not the chosen room
        # is in a hall which requires a meal plan
        $floor  = $room->get_parent();
        $hall   = $floor->get_parent();
        if($hall->meal_plan_required == 0){
            $form->addDropBox('meal_plan', array(MealPlan::BANNER_MEAL_NONE   =>_('None'),
            MealPlan::BANNER_MEAL_LOW    =>_('Low'),
            MealPlan::BANNER_MEAL_STD    =>_('Standard'),
            MealPlan::BANNER_MEAL_HIGH   =>_('High'),
            MealPlan::BANNER_MEAL_SUPER  =>_('Super')));
            $form->addCssClass('meal_plan', 'form-control');
        }else{
            $form->addDropBox('meal_plan', array(MealPlan::BANNER_MEAL_LOW    =>_('Low'),
            MealPlan::BANNER_MEAL_STD    =>_('Standard'),
            MealPlan::BANNER_MEAL_HIGH   =>_('High'),
            MealPlan::BANNER_MEAL_SUPER  =>_('Super')));
            $form->addCssClass('meal_plan', 'form-control');
        }

        $form->setMatch('meal_plan', $lotteryApplication->getMealPlan());

        $form->addSubmit('submit_form', 'Review Roommate & Room Selection');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Lottery Choose Roommate");

        return \PHPWS_Template::process($tpl, 'hms', 'student/lottery_select_roommate.tpl');
    }
}
