<?php

namespace Homestead;

class LotteryRoommateRequestView extends View {

    private $request;
    private $term;
    private $housingApplication;
    private $rlcAssignment;

    public function __construct($request, $term, HousingApplication $app = null, HMS_RLC_Assignment $rlcAssignment = null){
        $this->request = $request;
        $this->term = $term;
        $this->housingApplication = $app;
        $this->rlcAssignment = $rlcAssignment;
    }

    public function show()
    {
        # Get the roommate request record from the database
        $bed = new Bed($this->request['bed_id']);
        $room = $bed->get_parent();

        $tpl = array();

        $requestor = StudentFactory::getStudentByUsername($this->request['requestor'], $this->term);

        $tpl['REQUESTOR']      = $requestor->getName();
        $tpl['HALL_ROOM']      = $bed->where_am_i();

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

        // Check if the student has already completed a housing application
        if($this->housingApplication != null) {
            // Housing application complete, go straight to confirming roommate
            $submitCmd = CommandFactory::getCommand('LotteryShowConfirmRoommateRequest');
            $submitCmd->setRoommateRequestId($this->request['id']);
        } else {
        	// No housing application, goto self-select start page for contract and emg contact
            $submitCmd = CommandFactory::getCommand('RlcSelfAssignStart');
            $submitCmd->setTerm($this->term);
            $submitCmd->setRoommateRequestId($this->request['id']);
        }

        $denyCmd = CommandFactory::getCommand('LotteryShowDenyRoommateRequest');
        $denyCmd->setRequestId($this->request['id']);

        $form = new \PHPWS_Form();
        $submitCmd->initForm($form);

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
        }else{
            $form->addDropBox('meal_plan', array(MealPlan::BANNER_MEAL_LOW    =>_('Low'),
                                                 MealPlan::BANNER_MEAL_STD    =>_('Standard'),
                                                 MealPlan::BANNER_MEAL_HIGH   =>_('High'),
                                                 MealPlan::BANNER_MEAL_SUPER  =>_('Super')));
            $form->setMatch('meal_plan', MealPlan::BANNER_MEAL_STD);
        }

        // Set meal plan drop down default to what the student selected on the housing re-application.
        if($this->housingApplication != null) {
            $form->setMatch('meal_plan', $this->housingApplication->getMealPlan());
        }

        $form->addSubmit('accept', 'Accept Roommate');
        $form->addCssClass('accept', 'btn btn-success btn-md');

        $form->addButton('reject', 'Deny Roommate');
        $form->addCssClass('reject', 'btn btn-default btn-md pull-right');

        javascript('modules/hms/buttonAction', array('ID'=>'phpws_form_reject', 'URI'=>$denyCmd->getURI()));

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        \Layout::addPageTitle("Lottery Request Roommate");

        return \PHPWS_Template::process($tpl, 'hms', 'student/lottery_roommate_request.tpl');
    }
}
