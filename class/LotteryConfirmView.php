<?php

namespace Homestead;

class LotteryConfirmView extends View {

    private $roomId;
    private $mealPlan;
    private $roommates;
    private $term;

    public function __construct($roomId, $mealPlan, $roommates, $term)
    {
        $this->roomId       = $roomId;
        $this->mealPlan     = $mealPlan;
        $this->roommates    = $roommates;
        $this->term         = $term;
    }

    public function show()
    {
        $tpl = array();

        $submitCmd = CommandFactory::getCommand('LotteryConfirm');
        $submitCmd->setRoomId($this->roomId);
        $submitCmd->setMealPlan($this->mealPlan);

        $form = new \PHPWS_Form;
        $submitCmd->initForm($form);

        # Add the beds and user names back to the form so they end up in the request in a pretty way
        foreach($this->roommates as $key => $value){
            if(isset($value) && $value != ''){
                $form->addHidden("roommates[$key]", $value);
            }
        }

        # List the student's room
        $room = new HMS_Room($this->roomId);
        $tpl['ROOM'] = $room->where_am_i();

        # List all the students which will be assigned and their beds
        $beds = $room->get_beds();

        foreach($beds as $bed){
            $bed_row = array();

            # Check for an assignment
            $bed->loadAssignment();
            # Check for a reservation
            $reservation = $bed->get_lottery_reservation_info();

            $bedLabel = $room->getRoomNumber();
            if($room->get_number_of_beds() == 4)
            {
              $bedLabel = $bedLabel . $bed->getBedroomLabel();
            }
            $bedLabel = $bedLabel . $bed->getLetter();
            $bed_row['BED_LABEL'] = $bedLabel;

            $roommate = null;

            if($bed->_curr_assignment != NULL){
                # Bed is assigned
                $roommate = StudentFactory::getStudentByUsername($bed->_curr_assignment->asu_username, $this->term);
                $bed_row['TEXT'] = $roommate->getName();
            }else if($reservation != NULL){
                # Bed is reserved
                $roommate = StudentFactory::getStudentByUsername($reservation['asu_username'], $this->term);
                $bed_row['TEXT'] = $roommate->getName() . ' (reserved)';
            }else{
                # Get the new roommate name out of the request
                if(!isset($this->roommates[$bed->id]) || $this->roommates[$bed->id] == ''){
                    $bed_row['TEXT'] = 'Empty';
                }else{
                    $roommate = StudentFactory::getStudentByUsername($this->roommates[$bed->id], $this->term);
                    $bed_row['TEXT'] = $roommate->getName() . ' ' . $this->roommates[$bed->id];
                }
            }

            $tpl['beds'][] = $bed_row;
        }

        # Show the meal plan
        $tpl['MEAL_PLAN'] = HMS_Util::formatMealOption($this->mealPlan);
        $form->addHidden('meal_plan', $this->mealPlan);

        \PHPWS_Core::initCoreClass('Captcha.php');
        $form->addTplTag('CAPTCHA_IMAGE', \Captcha::get());

        $form->addSubmit('submit_form', 'Confirm room & roommates');
        $form->mergeTemplate($tpl);

        \Layout::addPageTitle("Confirm Re-Application");

        return \PHPWS_Template::process($form->getTemplate(), 'hms', 'student/lottery_confirm.tpl');
    }
}
