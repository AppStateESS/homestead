<?php

class LotteryConfirmRoommateRequestView extends View {
    
    private $request;
    private $term;
    private $mealPlan;
    
    public function __construct($request, $term, $mealPlan)
    {
        $this->request = $request;
        $this->term = $term;
        $this->mealPlan = $mealPlan;
    }
    
    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        # Get the roommate request record from the database
        $bed = new HMS_Bed($this->request['bed_id']);
        $room = $bed->get_parent();

        $tpl = array();

        $requestor = StudentFactory::getStudentByUsername($this->request['requestor'], $this->term);
        
        $tpl['REQUESTOR']       = $requestor->getName();
        $tpl['HALL_ROOM']       = $bed->where_am_i();

        # List all the students which will be assigned and their beds
        $beds = $room->get_beds();
        
        foreach($beds as $bed){
            $bed_row = array();

            # Check for an assignment
            $bed->loadAssignment();
            # Check for a reservation
            $reservation = $bed->get_lottery_reservation_info();
            
            $bed_row['BEDROOM_LETTER']  = $bed->bedroom_label;

            if($bed->_curr_assignment != NULL){
                # Bed is assigned
                $roommate = StudentFactory::getStudentByUsername($bed->_curr_assignment->asu_username, $this->term);
                $bed_row['TEXT'] = $roommate->getName();
            }else if($reservation != NULL){
                # Bed is reserved
                $roommate = StudentFactory::getStudentByUsername($reservation['asu_username'], $this->term);
                $bed_row['TEXT'] = $roommate->getName(). ' (reserved)';
            }else{
                $bed_row['TEXT'] = 'Empty';
            }

            $tpl['beds'][] = $bed_row;
        }

        $tpl['MEAL_PLAN'] = HMS_Util::formatMealOption($this->mealPlan);
        
        PHPWS_Core::initCoreClass('Captcha.php');
        $tpl['CAPTCHA'] = Captcha::get();

        $submitCmd = CommandFactory::getCommand('LotteryConfirmRoommateRequest');
        $submitCmd->setRequestId($this->request['id']);
        $submitCmd->setMealPlan($this->mealPlan);
        
        $form = new PHPWS_Form();
        $submitCmd->initForm($form);

        $form->addSubmit('confirm', 'Confirm Roommate');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Lottery Confirm Roommate");

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_confirm_roommate_request.tpl');
    }
}

?>