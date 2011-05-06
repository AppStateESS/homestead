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

    public function __construct($term)
    {
        $this->term = $term;

        $this->withdrawnCount   = 0;
        $this->actions          = array();
    }

    public function doSearch()
    {
        # Clear all the caches
        StudentDataProvider::clearAllCache();

        $term = $this->term;

        $query = "select DISTINCT * FROM (select hms_new_application.username from hms_new_application WHERE term=$term AND withdrawn != 1 UNION select hms_assignment.asu_username from hms_assignment WHERE term=$term) as foo";
        $result = PHPWS_DB::getCol($query);

        if(PHPWS_Error::logIfError($result)){
            //TODO
        }

        foreach($result as $username){
            $student = null;

            try{
                $student = StudentFactory::getStudentByUsername($username, $term);
            }catch(Exception $e){
                $this->actions[$username][] = 'WARNING!! Unknown student!';
                NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Unknown student: ' . $username);
                continue;
            }

            if($student->getType() != TYPE_WITHDRAWN){
                continue;
            }

            $this->actions[$username][] = 'Found withdrawn student: ' . $student->getUsername() . ' ' . $student->getBannerId();
            $this->withdrawnCount++;

            $this->handleApplication($student);
            $this->handleAssignment($student);
            $this->handleRoommate($student);
            $this->handleRlcAssignment($student);
            $this->handleRlcApplication($student);
        }

    }

    private function handleApplication(Student $student)
    {
        // Get the application and mark it withdrawn
        $app = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);
        if(!is_null($app)){
            $app->setWithdrawn(1);
            $app->setStudentType(TYPE_WITHDRAWN);
            try{
                $app->save();
            }catch(Exception $e){
                // TODO
            }

            $this->actions[$student->getUsername()][] = 'Marked application as withdrawn, updated student type to W.';
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_WITHDRAWN_APP, UserStatus::getUsername(), 'Withdrawn search');
        }
    }

    private function handleAssignment(Student $student)
    {
        // Look for an assignment and delete it
        $assignment = HMS_Assignment::getAssignment($student->getUsername(), $this->term);
        if(!is_null($assignment)){
            $location = $assignment->where_am_i();

            try{
                HMS_Assignment::unassignStudent($student, $this->term);
            }catch(Exception $e){
                //TODO
            }

            $this->actions[$student->getUsername()][] = 'Removed assignment: ' . $location;
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED, UserStatus::getUsername(), 'Withdrawn search: ' . $location);
        }
    }

    private function handleRoommate(Student $student)
    {
        # check for and delete any roommate requests, perhaps let the other roommate know?
        $roommates = HMS_Roommate::get_all_roommates($student->getUsername(), $this->term);
        if(sizeof($roommates) > 0){
            # Delete each roommate request
            foreach($roommates as $rm){
                try{
                    $rm->delete();
                }catch(Exception $e){
                    //TODO
                }

                $this->actions[$student->getUsername()][] = "Roommate request removed. {$rm->requestor} -> {$rm->requestee}";
                HMS_Activity_Log::log_activity($rm->requestor, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, UserStatus::getUsername(), "Withdarwn search; {$rm->requestor}->{$rm->requestee}");
                HMS_Activity_Log::log_activity($rm->requestee, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, UserStatus::getUsername(), "withdrawn search; {$rm->requestor}->{$rm->requestee}");
                # TODO: notify the other roommate, perhaps?
            }
        }
    }

    private function handleRlcAssignment(Student $student)
    {
        # Check for and delete any learning community assignments
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $this->term);

        if(!is_null($rlcAssignment)){
            $rlc = new HMS_Learning_Community($rlcAssignment->getRlcId());

            //TODO catch/handle exceptions
            $rlcAssignment->delete();
            $this->actions[$student->getUsername()][] = 'Removed RLC assignment: ' . $rlc->get_community_name();
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_WITHDRAWN_RLC_APP_DENIED, UserStatus::getUsername(), 'Withdrawn search');
        }
    }

    private function handleRlcApplication(Student $student)
    {
        # Mark any RLC applications as denied
        $rlcApp = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $this->term);

        if(!is_null($rlcApp)){
            # TODO catch/handle exceptions
            $rlcApp->delete();
            $this->actions[$student->getUsername()][] = 'Marked RLC application as denied.';
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_WITHDRAWN_RLC_APP_DENIED, UserStatus::getUsername(), 'Withdrawn search');

        }
    }

    public function getTextView()
    {
        $tpl = new PHPWS_Template('hms');

        if(!$tpl->setFile('admin/withdrawnSearchTextOutput.tpl')){
            return 'Template error...';
        }

        return $this->doTemplateStuff($tpl);
    }

    public function getHTMLView()
    {
        $tpl = new PHPWS_Template('hms');

        if(!$tpl->setFile('admin/withdrawnSearchOutput.tpl')){
            return 'Template error...';
        }

        return $this->doTemplateStuff($tpl);
    }

    public function doTemplateStuff($tpl)
    {

        $tpl->setData(array('DATE'=>date('F j, Y g:ia'), 'TERM'=>Term::toString($this->term)));

        if(sizeof($this->actions) < 1){
            $tpl->setData(array('NORESULTS'=>'No withdrawn students found.'));
            return $tpl->get();
        }else{
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
?>