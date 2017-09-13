<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

class WithdrawnSearch {

    private $term;

    private $withdrawnCount;
    private $actions;

    /**
     * Constructor
     * @param Integer $term The term to run the search on.
     */
    public function __construct($term)
    {
        $this->term = $term;

        $this->withdrawnCount   = 0;
        $this->actions          = array();
    }

    /**
     * Main searching function. Does the database lookup and then checks each student though the various functions.
     */
    public function doSearch()
    {
        // Clear all the caches
        StudentDataProvider::clearAllCache();

        $term = $this->term;

        $query = "select DISTINCT * FROM (select hms_new_application.username from hms_new_application WHERE term=$term AND cancelled != 1 UNION select hms_assignment.asu_username from hms_assignment WHERE term=$term) as foo";
        $result = PHPWS_DB::getCol($query);

        if(PHPWS_Error::logIfError($result)){
            throw new Exception($result->toString());
        }

        foreach($result as $username){
            $student = null;

            try{
                $student = StudentFactory::getStudentByUsername($username, $term);
            }catch(Exception $e){
                $this->actions[$username][] = 'WARNING!! Unknown student!';
                // Commenting out the NQ line, since this doesn't work when the search is run from cron/Pulse
                //NQ::simple('hms', hms\NotificationView::WARNING, 'Unknown student: ' . $username);
                continue;
            }

            if($student->getType() != TYPE_WITHDRAWN && $student->getAdmissionDecisionCode() != ADMISSION_WITHDRAWN_PAID && $student->getAdmissionDecisionCode() != ADMISSION_RESCIND){
                continue;
            }

            $this->actions[$username][] = $student->getBannerId() . ' (' . $student->getUsername() . ')';
            $this->withdrawnCount++;

            $this->handleApplication($student);
            $this->handleAssignment($student);
            $this->handleRoommate($student);
            $this->handleRlcAssignment($student);
            $this->handleRlcApplication($student);
        }

    }

    /**
     * Handles looking up and withdrawing housing applications.
     * @param Student $student
     */
    private function handleApplication(Student $student)
    {
        // Get the application and mark it withdrawn
        $app = HousingApplicationFactory::getAppByStudent($student, $this->term);
        if(!is_null($app)) {
            //$app->setWithdrawn(1);
            //$app->setStudentType(TYPE_WITHDRAWN);
            $app->cancel(CANCEL_WITHDRAWN);
            try{
                $app->save();
            }catch(Exception $e){
                // TODO
            }

            $this->actions[$student->getUsername()][] = 'Found Housing Application; Student Type: ' . $app->getStudentType() . ' App Term: ' . $app->getApplicationTerm();
            $this->actions[$student->getUsername()][] = 'Marked application as cancelled (reason: withdrawn)';
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CANCEL_HOUSING_APPLICATION, UserStatus::getUsername(), 'Application automatically cancelled by Withdrawn Search');
        }
    }

    /**
     * Handles looking up and removing assignments
     * @param Student $student
     */
    private function handleAssignment(Student $student)
    {
        // Look for an assignment and delete it
        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);
        if(!is_null($assignment)) {
            $location = $assignment->where_am_i();

            try{
                //TODO Don't hard-code refund percentage
                HMS_Assignment::unassignStudent($student, $this->term, 'Automatically removed by Withdrawn Search', UNASSIGN_CANCEL, 100);
            }catch(Exception $e){
                //TODO
            }

            $this->actions[$student->getUsername()][] = 'Removed assignment: ' . $location;
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED, UserStatus::getUsername(), 'Withdrawn search: ' . $location);

            // Check for a meal plan, and remove it if it hasn't been sent yet
            $mealPlan = MealPlanFactory::getMealByBannerIdTerm($student->getBannerId(), $this->term);

            if($mealPlan !== null){
                if($mealPlan->getStatus() === MealPlan::STATUS_NEW){
                    MealPlanFactory::removeMealPlan($mealPlan);
                    $this->actions[$student->getUsername()][] = 'Removed meal plan.';
                } else {
                    // Show a warning that we couldn't remove this meal plan
                    $this->actions[$student->getUsername()][] = 'Warning: Could not remove meal plan because it has been sent to Banner.';
                }
            }
        }
    }

    /**
     * Handles removing roommate requests
     * @param Student $student
     */
    private function handleRoommate(Student $student)
    {
        # check for and delete any roommate requests, perhaps let the other roommate know?
        $roommates = HMS_Roommate::get_all_roommates($student->getUsername(), $this->term);
        if(sizeof($roommates) > 0) {
            # Delete each roommate request
            foreach($roommates as $rm) {
                try {
                    $rm->delete();
                } catch(Exception $e) {
                    //TODO
                }

                $this->actions[$student->getUsername()][] = "Roommate request removed. {$rm->requestor} -> {$rm->requestee}";
                HMS_Activity_Log::log_activity($rm->requestor, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, UserStatus::getUsername(), "Withdarwn search; {$rm->requestor}->{$rm->requestee}");
                HMS_Activity_Log::log_activity($rm->requestee, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, UserStatus::getUsername(), "withdrawn search; {$rm->requestor}->{$rm->requestee}");
                # TODO: notify the other roommate, perhaps?
            }
        }
    }

    /**
     * Handles removing RLC assignments.
     * @param Student $student
     */
    private function handleRlcAssignment(Student $student)
    {
        # Check for and delete any learning community assignments
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $this->term);

        if(!is_null($rlcAssignment)) {
            $rlc = new HMS_Learning_Community($rlcAssignment->getRlcId());

            //TODO catch/handle exceptions
            $rlcAssignment->delete();
            $this->actions[$student->getUsername()][] = 'Removed RLC assignment: ' . $rlc->get_community_name();
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_WITHDRAWN_RLC_APP_DENIED, UserStatus::getUsername(), 'Withdrawn search');
        }
    }

    /**
     * Handles removing RLC applications.
     * @param Student $student
     */
    private function handleRlcApplication(Student $student)
    {
        // Mark any RLC applications as denied
        $rlcApp = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $this->term);

        if(!is_null($rlcApp)) {
            // TODO catch/handle exceptions
            $rlcApp->delete();
            $this->actions[$student->getUsername()][] = 'Marked RLC application as denied.';
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_WITHDRAWN_RLC_APP_DENIED, UserStatus::getUsername(), 'Withdrawn search');

        }
    }

    /**
     * Returns an array of template tags for a text view of the output.
     * @return Array An array of template tages for a text view of the output.
     */
    public function getTextView()
    {
        $tpl = new PHPWS_Template('hms');

        if(!$tpl->setFile('admin/withdrawnSearchTextOutput.tpl')) {
            return 'Template error...';
        }

        return $this->doTemplateStuff($tpl);
    }

    /**
     * Returns an array of template tags for a HTML view of the output.
     * @array Array array of template tags for a HTML view of the output.
     */
    public function getHTMLView()
    {
        $tpl = new PHPWS_Template('hms');

        if(!$tpl->setFile('admin/withdrawnSearchOutput.tpl')) {
            return 'Template error...';
        }

        return $this->doTemplateStuff($tpl);
    }

    /**
     * Takes a PHPWS_Template object and plugs the various variables into it
     * @param PHPWS_Template $tpl
     *
     * @return Array template tags
     */
    public function doTemplateStuff($tpl)
    {

        $tpl->setData(array('DATE'=>date('F j, Y g:ia'), 'TERM'=>Term::toString($this->term)));

        if(sizeof($this->actions) < 1) {
            $tpl->setData(array('NORESULTS'=>'No withdrawn students found.'));
            return $tpl->get();
        } else {
            $tpl->setData(array('COUNT'=>sizeof($this->actions)));
        }

        foreach($this->actions as $username=>$actions){
            foreach($actions as $action){
                $tpl->setCurrentBlock('action_repeat');
                $tpl->setData(array('ACTION'=>$action));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('user_repeat');
            $tpl->setData(array('USERNAME'=>$username));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }
}
