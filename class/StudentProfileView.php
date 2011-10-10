<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Util.php');
PHPWS_Core::initModClass('hms', 'ActivityLogView.php');

class StudentProfileView extends View {

    private $student;
    private $applications;
    private $assignment;
    private $roommates;

    public function __construct(Student $student, $applications = NULL, HMS_Assignment $assignment = NULL, Array $roommates){
        $this->student		= $student;
        $this->applications = $applications;
        $this->assignment	= $assignment;
        $this->roommates	= $roommates;
    }

    public function show()
    {
        javascript('jquery');
        javascript('jquery_ui');
        javascript('modules/hms/student_info/');

        $tpl = array();

        $tpl['TITLE'] = "Search Results - " . Term::getPrintableSelectedTerm();
        $tpl['USERNAME'] = $this->student->getUsername();

        if( Current_User::allow('hms', 'login_as_student') ) {
            $loginAsStudent = CommandFactory::getCommand('LoginAsStudent');
            $loginAsStudent->setUsername($this->student->getUsername());

            $tpl['LOGIN_AS_STUDENT'] = $loginAsStudent->getLink('Login as student');
        }

        $tpl['BANNER_ID']   = $this->student->getBannerId();
        $tpl['NAME']  = $this->student->getFullName();

        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        $tpl['GENDER'] = $this->student->getPrintableGender();

        $tpl['DOB'] = $this->student->getDOB();

        if(strtotime($this->student->getDOB()) < strtotime("-25 years")){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Student is 25 years old or older!');
        }

        $tpl['CLASS'] = $this->student->getPrintableClass();

        $tpl['TYPE'] = $this->student->getPrintableType();

        $tpl['STUDENT_LEVEL'] = $this->student->getPrintableLevel();

        $tpl['INTERNATIONAL'] = $this->student->isInternational() ? 'Yes' : 'No';

        $tpl['HONORS'] = $this->student->isHonors() ? 'Yes' : 'No';

        $tpl['TEACHING_FELLOW'] = $this->student->isTeachingFellow() ? 'Yes' : 'No';

        $tpl['WATAUGA'] = $this->student->isWataugaMember() ? 'Yes' : 'No';

        if($this->student->pinDisabled()){
        	NQ::simple('hms', HMS_NOTIFICATION_WARNING, "This student's PIN is disabled.");
        }
        
        try {
            $tpl['APPLICATION_TERM'] = Term::toString($this->student->getApplicationTerm());
        } catch(InvalidTermException $e) {
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Application term is bad or missing.');
            $tpl['APPLICATION_TERM'] = 'WARNING: Application Term is bad or missing: "'.$this->student->getApplicationTerm().'"';
        }

        /*****************
         * Phone Numbers *
         *****************/
        $phoneNumberList = $this->student->getPhoneNumberList();
        if(isset($phoneNumberList) && !is_null($phoneNumberList)){
            foreach($this->student->getPhoneNumberList() as $phone_number){
                $tpl['phone_number'][] = array('NUMBER' =>$phone_number);
            }
        }

        /*************
         * Addresses *
         *************/
        foreach($this->student->getAddressList() as $address){
            //If it's not a PS or PR address, skip it
            if($address->atyp_code != 'PR' && $address->atyp_code != 'PS'){
                continue;
            }

            switch ($address->atyp_code){
                case 'PS':
                    $addr_type = 'Student Address';
                    break;
                case 'PR':
                    $addr_type = 'Permanent Residence Address';
                    break;
                default:
                    $addr_type = 'Unknown-type address';
            }

            $addr_array = array();
            $addr_array['ADDR_TYPE']	= $addr_type;
            $addr_array['ADDRESS_L1']	= $address->line1;
            if(isset($address->line2))
            $addr_array['ADDRESS_L2']	= $address->line2;
            if(isset($address->line3))
            $addr_array['ADDRESS_L3']	= $address->line3;
            $addr_array['CITY']			= $address->city;
            $addr_array['STATE']		= $address->state;
            $addr_array['ZIP']			= $address->zip;

            $tpl['addresses'][] = $addr_array;
        }

        /**************
         * Assignment *
         **************/
        if(!is_null($this->assignment)){
            $reassignCmd = CommandFactory::getCommand('ShowAssignStudent');
            $reassignCmd->setUsername($this->student->getUsername());

            $unassignCmd = CommandFactory::getCommand('ShowUnassignStudent');
            $unassignCmd->setUsername($this->student->getUsername());
            $tpl['ASSIGNMENT'] = $this->assignment->where_am_i(true) . ' ' . $reassignCmd->getLink('Reassign') . ' ' . $unassignCmd->getLink('Unassign');
        }else{
            $assignCmd = CommandFactory::getCommand('ShowAssignStudent');
            $assignCmd->setUsername($this->student->getUsername());
            $tpl['ASSIGNMENT'] = 'No [' . $assignCmd->getLink('Assign Student') . ']';
            //$tpl['ASSIGNMENT'] = 'No';
        }

        /*************
         * Roommates
         *************/
        if(isset($this->roommates) && !empty($this->roommates)){
            // Remember, student can only have one confirmed or pending request
            // but multiple assigned roommates
            if(isset($this->roommates['PENDING'])){
                $tpl['pending'][]['ROOMMATE'] = $this->roommates['PENDING'];
            }
            else if(isset($this->roommates['CONFIRMED'])){
                $tpl['confirmed'][]['ROOMMATE'] = $this->roommates['CONFIRMED'];
            }
            // semi-error states
            else if(isset($this->roommates['NO_BED_AVAILABLE'])){
                $tpl['error_status'][]['ROOMMATE'] = $this->roommates['NO_BED_AVAILABLE'];
            }
            else if(isset($this->roommates['MISMATCHED_ROOMS'])){
                $tpl['error_status'][]['ROOMMATE'] = $this->roommates['MISMATCHED_ROOMS'];
            }

            if(isset($this->roommates['ASSIGNED'])){
                foreach($this->roommates['ASSIGNED'] as $roommate){
                    $tpl['assigned'][]['ROOMMATE'] = $roommate;
                }
            }
        }

        /**************
         * RLC Status *
         *************/
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $rlc_names = HMS_Learning_Community::getRLCList();

        $rlc_assignment     = HMS_RLC_Assignment::getAssignmentByUsername($this->student->getUsername(), Term::getSelectedTerm());
        $rlc_application    = HMS_RLC_Application::getApplicationByUsername($this->student->getUsername(), Term::getSelectedTerm(), FALSE);

        if(!is_null($rlc_assignment)){
            $tpl['RLC_STATUS'] = "This student is assigned to: " . $rlc_names[$rlc_assignment->rlc_id];
        }else if (!is_null($rlc_application)){
            $rlcViewCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
            $rlcViewCmd->setAppId($rlc_application->getId());
            $tpl['RLC_STATUS'] = "This student has a " . $rlcViewCmd->getLink('pending RLC application') . ".";
        }else{
            $tpl['RLC_STATUS'] = "This student is not in a Learning Community and has no pending application.";
        }

        /*************************
         * Re-application status *
         *************************/
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        $reapplication = HousingApplication::getApplicationByUser($this->student->getUsername(), Term::getSelectedTerm());

        # If this is a re-application, then check the special interest group status
        # TODO: incorporate all this into the LotteryApplication class
        if($reapplication !== FALSE && ($reapplication instanceof LotteryApplication)){
            if(isset($reapplication->special_interest) && !is_null($reapplication->special_interest) && !empty($reapplication->special_interest)){
                # Student has been approved for a special group
                # TODO: format the name according to the specific group (sororities, etc)
                $tpl['SPECIAL_INTEREST'] = $reapplication->special_interest . '(confirmed)';
            }else{
                # Check if the student selected a group on the application, but hasn't been approved
                if(!is_null($reapplication->sorority_pref)){
                    $tpl['SPECIAL_INTEREST'] = $reapplication->sorority_pref . ' (pending)';
                }else if($reapplication->tf_pref == 1){
                    $tpl['SPECIAL_INTEREST'] = 'Teaching Fellow (pending)';
                }else if($reapplication->wg_pref == 1){
                    $tpl['SPECIAL_INTEREST'] = 'Watauga Global (pending)';
                }else if($reapplication->honors_pref == 1){
                    $tpl['SPECIAL_INTEREST'] = 'Honors (pending)';
                }else if($reapplication->rlc_interest == 1){
                    $tpl['SPECIAL_INTEREST'] = 'RLC (pending)';
                }else{
                    # Student didn't select anything
                    $tpl['SPECIAL_INTEREST'] = 'No';
                }
            }
        }else{
            # Not a re-application, so can't have a special group
            $tpl['SPECIAL_INTEREST'] = 'No';
        }
        
        /******************
         * Housing Waiver *
         *************/

       	$tpl['HOUSING_WAIVER'] = $this->student->housingApplicationWaived() ? 'Yes' : 'No';
       	
       	if($this->student->housingApplicationWaived()){
       		NQ::simple('hms', HMS_NOTIFICATION_WARNING, "This student's housing application has been waived for this term.");
       	}
        
        /****************
         * Applications *
         *************/
        # Show a row for each application
        if(isset($this->applications)){
            $app_rows = "";
            foreach($this->applications as $app){
                $term = Term::toString($app->getTerm());
                $meal_plan = HMS_Util::formatMealOption($app->getMealPlan());
                $phone = HMS_Util::formatCellPhone($app->getCellPhone());

                $type = $app->getPrintableAppType();

                if(isset($app->room_condition)){
                    $clean = $app->room_condition == 1 ? 'Neat' : 'Cluttered';
                }else{
                    $clean = '';
                }

                if(isset($app->preferred_bedtime)){
                    $bedtime = $app->preferred_bedtime == 1 ? 'Early' : 'Late';
                }else{
                    $bedtime = '';
                }

                $viewCmd = CommandFactory::getCommand('ShowApplicationView');
                $viewCmd->setAppId($app->getId());

                if($app->getWithdrawn() == 0){
                    $withdrawCmd = CommandFactory::getCommand('MarkApplicationWithdrawn');
                    $withdrawCmd->setAppId($app->getId());
                    $withdrawn = '[' . $withdrawCmd->getLink('Withdraw') . ']';
                }else{
                    $withdrawn = '(widthdrawn)';
                }

                $actions = '[' . $viewCmd->getLink('View') . '] ' . $withdrawn;

                $app_rows[] = array('term'=>$term, 'type'=>$type, 'meal_plan'=>$meal_plan, 'cell_phone'=>$phone, 'clean'=>$clean, 'bedtime'=>$bedtime, 'actions'=>$actions);
            }

            $tpl['APPLICATIONS'] = $app_rows;
        }else{
            $tpl['APPLICATIONS_EMPTY'] = 'No applications found.';
        }

        /*********
         * Assignment History *
         *********/        
        
        PHPWS_Core::initModClass('hms', 'StudentAssignmentHistory.php');
        PHPWS_Core::initModClass('hms', 'StudentAssignmentHistoryView.php');
        
        $historyArray = StudentAssignmentHistory::getAssignments($this->student->getBannerId());
        $historyView = new StudentAssignmentHistoryView($historyArray);
        $tpl['HISTORY'] = $historyView->show();
        
        /*********
         * Notes *
         *********/
        $addNoteCmd = CommandFactory::getCommand('AddNote');
        $addNoteCmd->setUsername($this->student->getUsername());

        $form = new PHPWS_Form('add_note_dialog');
        $addNoteCmd->initForm($form);

        $form->addTextarea('note');
        $form->addSubmit('Add Note');

        /********
         * Logs *
         ********/
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        $everything_but_notes = HMS_Activity_Log::get_activity_list();
        unset($everything_but_notes[array_search(ACTIVITY_ADD_NOTE, $everything_but_notes)]);

        if( Current_User::allow('hms', 'view_activity_log') && Current_User::allow('hms', 'view_student_log') ){
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            $activityLogPager = new ActivityLogPager($this->student->getUsername(), null, null, true, null, null, $everything_but_notes, true, 10);
            $activityNotePager = new ActivityLogPager($this->student->getUsername(), null, null, true, null, null, array(0 => ACTIVITY_ADD_NOTE), true, 10);

            $tpl['LOG_PAGER'] = $activityLogPager->show();
            $tpl['NOTE_PAGER'] = $activityNotePager->show();

            $logsCmd = CommandFactory::getCommand('ShowActivityLog');
            $logsCmd->setActeeUsername($this->student->getUsername());
            $tpl['LOG_PAGER'] .= $logsCmd->getLink('View more');

            $notesCmd = CommandFactory::getCommand('ShowActivityLog');
            $notesCmd->setActeeUsername($this->student->getUsername());
            $notesCmd->setActivity(array(0 =>ACTIVITY_ADD_NOTE));
            $tpl['NOTE_PAGER'] .= $notesCmd->getLink('View more');
        }

        $tpl = array_merge($tpl, $form->getTemplate());

        // TODO logs

        // TODO tabs

        Layout::addPageTitle("Student Profile");
		Layout::addStyle('hms', 'css/studentInfo.css');
        return PHPWS_Template::process($tpl, 'hms', 'admin/fancy_student_info.tpl');
    }
}

?>
