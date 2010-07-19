<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');

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
        $term = $this->term;

        $query = "select DISTINCT * FROM (select hms_new_application.username from hms_new_application WHERE term=$term AND withdrawn != 1 UNION select hms_assignment.asu_username from hms_assignment WHERE term=$term) as foo";
        $result = PHPWS_DB::getCol($query);

        if(PHPWS_Error::logIfError($result)){
            //TODO
        }

        foreach($result as $user){
            $student = null;

            try{
                $student = StudentFactory::getStudentByUsername($user, $term);
            }catch(Exception $e){
                //TODO
            }

            if($student->getType != TYPE_WITHDRAWN){
                continue;
            }

            $this->actions[] = 'Found withdrawn student: ' . $student->getUsername . ' ' . $student->getBannerId();
            $this->withdrawnCount++;

            $this->handleApplication($user);
            $this->handleAssignment($user);
            $this->handleRoommate($user);
            $this->handleRlcAssignment($user);
            $this->handleRlcApplication($user);
        }

        $this->sendReport();
    }

    private function handleApplication($user)
    {
        // Get the application and mark it withdrawn
        $app = HousingApplication::getApplicationByUser($user, $this->term);
        if(!is_null($app)){
            $app->setWithdrawn(1);
            $app->setStudentType(TYPE_WITHDRAWN);
            try{
                $app->save();
            }catch(Exception $e){
                // TODO
            }

            HMS_Activity_Log::log_activity($user, ACTIVITY_WITHDRAWN_APP, UserStatus::getUsername(), 'Withdrawn search');
        }
    }

    private function handleAssignment($user)
    {
        // Look for an assignment and delete it
        $assignment = HMS_Assignment::getAssignment($user, $this->term);
        if(!is_null($assignment)){
            $location = $asignment->where_am_i();
            $this->actions[] = "Removed assignment: " . $location;

            try{
                HMS_Assignment::unassignStudent($student, $this->term);
            }catch(Exception $e){
                //TODO
            }

            HMS_Activity_Log::log_activity($user, ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED, UserStatus::getUsername(), 'Withdrawn search: ' . $location);
        }
    }

    private function handleRoommate($user)
    {
        # check for and delete any roommate requests, perhaps let the other roommate know?
        $roommates = HMS_Roommate::get_all_roommates($user, $this->term);
        if(sizeof($roommates) > 0){
            # Delete each roommate request
            foreach($roommates as $rm){
                try{
                    $rm->delete();
                }catch(Exception $e){
                    //TODO
                }

                $this->actions[] = array('USERNAME'    => $user,
                                             'MESSAGE'     => "Roommate request removed. {$rm->requestor} -> {$rm->requestee}");
                HMS_Activity_Log::log_activity($rm->requestor, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, UserStatus::getUsername(), "Withdarwn search; {$rm->requestor}->{$rm->requestee}");
                HMS_Activity_Log::log_activity($rm->requestee, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, UserStatus::getUsername(), "withdrawn search; {$rm->requestor}->{$rm->requestee}");
                # TODO: notify the other roommate, perhaps?
            }
        }
    }

    private function handleRlcAssignment($user)
    {
        # Check for and delete any learning community assignments
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($user, $this->term);

        if(!is_null($rlcAssignment)){
            //TODO catch/handle exceptions
            $rlcAssignment->delete();
            $this->actions[] = 'Removed RLC assignment.';
            HMS_Activity_Log::log_activity($asu_username, ACTIVITY_WITHDRAWN_RLC_APP_DENIED, UserStatus::getUsername(), 'Withdrawn search');
        }
    }

    private function handleRlcApplication($user)
    {
        # Mark any RLC applications as denied
        $rlcApp = HMS_RLC_Application::getApplicationByUsername($user, $this->term);

        if(!is_null($rlcApp)){
            # TODO catch/handle exceptions
            $rlcApp->delete();
            $this->action[] = 'Marked RLC application as denied.';
            HMS_Activity_Log::log_activity($asu_username, ACTIVITY_WITHDRAWN_RLC_APP_DENIED, UserStatus::getUsername(), 'Withdrawn search');

        }
    }

    private function sendReport()
    {
        #TODO
    }

    public function getHTMLView()
    {
        if(sizeof($this->actions) < 1){
            $output = 'No withdrawn students found.';
        }

        foreach($this->actions as $result){
            //TODO
        }

        return $output;
    }
}
?>