<?php

/**
 * Contains administrative public functionality
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Admin 
{
    /**
     * Shows the page where the user can start the withdrawn student search
     */
    public function withdrawn_search_start($success_msg = NULL, $error_msg = NULL)
    {
        if(!Current_User::allow('hms', 'withdrawn_search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $form = &new PHPWS_Form();
        $form->addSubmit('start', 'Begin Withdrawn Student Search');

        $form->addHidden('mod', 'hms');
        $form->addHidden('type', 'admin');
        $form->addHidden('op', 'withdrawn_search');

        $tpl = $form->getTemplate();

        $tpl['TERM'] = HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);

        if(isset($success_msg)){
            $tpl['SUCCESS_MSG'] = $success_msg;
        }

        if(isset($error_msg)){
            $tpl['ERROR_MSG'] = $error_msg;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/withdrawn_search_start.tpl');
    }

    /**
     * Performs the withdrawn student search
     */
    public function withdrawn_search()
    {
        if(!Current_User::allow('hms', 'withdrawn_search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $db = new PHPWS_DB('hms_new_application');
        $term = HMS_Term::get_selected_term();
       
        // This is ugly, but it does what we need it to do...
        // (necessary since not everyone who is assigned will have an application) 
        $db->setSQLQuery("select DISTINCT * FROM (select hms_new_application.username from hms_new_application WHERE term=$term AND withdrawn != 1 UNION select hms_assignment.asu_username from hms_assignment WHERE term=$term) as foo");
        $result = $db->select('col');

        //test($result);

        if(PEAR::isError($result)){
            PHPWS_Error::logIfError($result);
            return HMS_Admin::withdrawn_search_start(NULL, 'An error occured while working with the HMS database.');
        }

        $form = &new PHPWS_Form('withdrawn_form');
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'admin');
        $form->addHidden('op', 'withdrawn_search_process');
        $form->addSubmit('submit', 'Remove Withdrawn Students');

        # Lookup each student
        $i = 0;
        foreach($result as $asu_username){
            # Query SOAP, skiping students who are not withdrawn
            if(HMS_SOAP::get_student_type($asu_username, $term) != TYPE_WITHDRAWN){
                continue;
            }

            $assignment = HMS_Assignment::get_assignment($asu_username, $term);

            $tpl['withdrawn_students'][] = array(
                                    'NAME'              => HMS_Student::get_link($asu_username, TRUE),
                                    'BANNER_ID'         => HMS_SOAP::get_banner_id($asu_username),
                                    'REMOVE_CHECKBOX'   => '<input type="checkbox" name="remove_checkbox' . "[{$i}]" . '" value="' . $asu_username . '" checked>',
                                    'ASSIGNMENT'        => is_null($assignment)?'None':PHPWS_Text::secureLink($assignment->where_am_i(), 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$assignment->get_room_id()))
                                    );
            $i++;
        }

        $tpl['TITLE'] = 'Withdrawn Search - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        $tpl['COUNT'] = sizeof($tpl['withdrawn_students']);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/withdrawn_search.tpl');
    }

    public function withdrawn_search_process()
    {
        if(!Current_User::allow('hms', 'withdrawn_search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        
        #test($_REQUEST['remove_checkbox']);

        $tpl['status']      = array();
        $tpl['warnings']    = array();
        $tpl['rooms']       = array();
        $tpl['TITLE']       = 'Withdrawn Removal Results';

        $term = HMS_Term::get_selected_term();

        # Process each of the selected students
        foreach($_REQUEST['remove_checkbox'] as $asu_username){
            
            # Check for and mark as withdrawn any application
            $app = HousingApplication::checkForApplication($asu_username, $term, TRUE);
            if($app != FALSE){
                $application = new FallApplication($app['id']);
                $application->setWithdrawn(true);
                $application->setStudentType(TYPE_WITHDRAWN);
                $app_result = $application->save();
                if(PEAR::isError($app_result)){
                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                               'MESSAGE'    => 'Saving application failed (database error).');
                }else{
                    $tpl['status'][] = array('USERNAME'    => $asu_username,
                                             'MESSAGE'     => "Application removed.");
                    HMS_Activity_Log::log_activity($asu_username, ACTIVITY_WITHDRAWN_APP, Current_User::getUsername());
                }
            }

            # Check for and delete any assignments
            if(HMS_Assignment::check_for_assignment($asu_username, $term)){
                $assignment             = HMS_Assignment::get_assignment($asu_username, $term);
                $assignment_location    = $assignment->where_am_i();
                $room_id                = $assignment->get_room_id();
                if($assignment == NULL || $assignment == FALSE){
                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                               'MESSAGE'    => 'Error loading assignment.');
                }else{
                    # TODO: call the 'un-assign' public function here instead
                    $unassign_result = HMS_Assignment::unassign_student($asu_username, $term);
                    if($unassign_result != E_SUCCESS){
                        $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                   'MESSAGE'    => HMS_Assignment::get_assignment_error_msg($unassign_result));
                    }else{
                        $tpl['status'][] = array('USERNAME'    => $asu_username,
                                                 'MESSAGE'     => "Assignment removed.");
                        $room = array('ROOM' => PHPWS_Text::secureLink($assignment_location, 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$room_id)));
                        $key = array_search($room, $tpl['rooms']);
                        if($key === FALSE){
                            $tpl['rooms'][] = $room;
                        }else{
                            $tpl['rooms'][$key]['ROOM'] .= 'x2';
                        }
                        HMS_Activity_Log::log_activity($asu_username, ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED, Current_User::getUsername(), $assinment_location);
                    }
                }
            }

            # check for and delete any roommate requests, perhaps let the other roommate know?
            $roommates = HMS_Roommate::get_all_roommates($asu_username, $term);
            if($roommates === FALSE){
                $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                           'MESSAGE'    => 'Error checking for roommate requests.');
            }else if(sizeof($roommates) > 0){
                # Delete each roommate request
                foreach($roommates as $rm){
                    if($rm->delete() == TRUE){
                        $tpl['status'][] = array('USERNAME'    => $asu_username,
                                                 'MESSAGE'     => "Roommate request removed. {$rm->requestor} -> {$rm->requestee}");
                        HMS_Activity_Log::log_activity($rm->requestor, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, Current_User::getUsername(), "{$rm->requestor}->{$rm->requestee}");
                        HMS_Activity_Log::log_activity($rm->requestee, ACTIVITY_WITHDRAWN_ROOMMATE_DELETED, Current_User::getUsername(), "{$rm->requestor}->{$rm->requestee}");
                        # TODO: notify the other roommate, perhaps?
                    }else{
                        $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                   'MESSAGE'    => "Error deleting roommate request. {$rm->requestor} -> {$rm->requestee}");
                    }
                }
            }

            # Check for and delete any learning community assignments
            $rlc_app = HMS_RLC_Application::check_for_application($asu_username, $term);
            if(PEAR::isError($rlc_app)){
                $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                           'MESSAGE'    => 'Error looking for RLC application.');
            }else if($rlc_app != FALSE){
                # Get their rlc app
                $rlc_app = &new HMS_RLC_Application($asu_username, $term);
                if(PEAR::isError($rlc_app)){
                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                               'MESSAGE'    => 'Error loading RLC application.');
                }else{
                    # See if they're assigned anywhere
                    $assignment_id = $rlc_app->hms_assignment_id;
                    if($assignment_id != NULL){
                        //test($assignment_id);
                        # Delete the assignment id from the application
                        $rlc_app->hms_assignment_id = NULL;
                        $rlc_app->denied = 1; // Mark as denied so it won't bother anyone
                        if(PEAR::isError($rlc_app->save())){
                            $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                       'MESSAGE'    => 'Error saving rlc application.');
                        }else{
                            $rlc_assignment = &new HMS_RLC_Assignment($assignment_id);
                            if(PEAR::isError($rlc_assignment)){
                                $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                           'MESSAGE'    => 'Error loading RLC assignment.');
                            }else{
                                
                                $del = $rlc_assignment->delete();
                                if($del != TRUE){
                                //if($rlc_assignment->delete() != TRUE){
                                    test($del);
                                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                               'MESSAGE'    => 'Error deleting RLC assignment.');
                                }else{
                                    $tpl['status'][] = array('USERNAME'    => $asu_username,
                                                             'MESSAGE'     => 'Marked application denied, deleted RLC assignment.');
                                    HMS_Activity_Log::log_activity($asu_username, ACTIVITY_WITHDRAWN_RLC_APP_DENIED, Current_User::getUsername());
                                    HMS_Activity_Log::log_activity($asu_username, ACTIVITY_WITHDRAWN_RLC_ASSIGN_DELETED, Current_User::getUsername());
                                }
                            }
                        }
                    }else{
                        # They didn't get assigned, but we can still mark their application denied
                        $rlc_app->denied = 1;
                        if(PEAR::isError($rlc_app->save())){
                            $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                       'MESSAGE'    => 'Error saving rlc application.');
                        }else{
                            $tpl['status'][] = array('USERNAME'    => $asu_username,
                                                     'MESSAGE'     => 'Marked RLC application as denied.');
                            HMS_Activity_Log::log_activity($asu_username, ACTIVITY_WITHDRAWN_RLC_APP_DENIED, Current_User::getUsername());
                        }
                    }
                }
                                
            }
        }

        //test($tpl['rooms'], FALSE, TRUE);

        return PHPWS_Template::process($tpl, 'hms', 'admin/withdrawn_search_process.tpl');
    }

    public function show_username_change()
    {
        if(!Current_User::allow('hms', 'username_change')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initCoreClass('Form.php');

        $form = &new PHPWS_Form();
        $form->addTextarea('usernames');
        $form->addSubmit('submit', 'Submit');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'admin');
        $form->addHidden('op', 'process_username_change');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/username_change.tpl');
    }

    public function process_username_change()
    {
        if(!Current_User::allow('hms', 'username_change')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        require_once('mod/hms/inc/defines.php');

        $tpl = array();

        $tpl['status'] = array();
        $tpl['errors'] = array();
        
        # break input down line by line
        $lines = split("\n", $_REQUEST['usernames']);

        # For each set of user names on a line...
        foreach($lines as $line){
            # Split the names (from: "old,new")
            $names = split(',', $line);
            $names[0] = trim($names[0]);
            $names[1] = trim($names[1]);

            # Start logging activities
            $notes = "Attempted to update username: ".trim($names[0])."=>".trim($names[1]);
            HMS_Activity_Log::log_activity(trim($names[0]), ACTIVITY_USERNAME_UPDATED, Current_User::getUsername(), $notes);
            HMS_Activity_Log::log_activity(trim($names[1]), ACTIVITY_USERNAME_UPDATED, Current_User::getUsername(), $notes);

            # Open a DB connection and try to update applications
            $db = &new PHPWS_DB('hms_new_application');
            $db->addValue('username', $names[1]);
            $db->addValue('created_by',     $names[1]);
            $db->addWhere('username', $names[0]);
            $result = $db->update();

            if(PEAR::isError($result)){
                PHPWS_Error::logIfError($result);
                $tpl['errors'][] = array('USERNAME'=>$names[0], 'MESSAGE' => 'DB error trying to update application.');
            }else{
                # Check to see if something happened
                $rows_affected = $db->affectedRows();
                if($rows_affected > 0){
                    $tpl['status'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "$rows_affected applications records updated.");

                    # Log Successful application update
                    $notes = "Application Updated";
                    HMS_Activity_Log::log_activity(trim($names[1]), ACTIVITY_APPLICATION_UPDATED, Current_User::getUsername(), $notes);
                }
            }

            # Update assignments
            $db = &new PHPWS_DB('hms_assignment');
            $db->addValue('asu_username', $names[1]);
            $db->addWhere('asu_username', $names[0]);
            $result = $db->update();
            if(PEAR::isError($result)){
                PHPWS_Error::logIfError($result);
                $tpl['errors'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "DB error trying to update assignment.");
            }else{
                # Check to see if something happened
                $rows_affected = $db->affectedRows();
                if($rows_affected > 0){
                    $tpl['status'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "$rows_affected assignment records updated.");
                    
                    $notes = "Assignments Updated";
                    HMS_Activity_Log::log_activity(trim($names[1]), ACTIVITY_ASSIGNMENTS_UPDATED, Current_User::getUsername(), $notes);
                }
            }

            # Update the banner queue
            $db = &new PHPWS_DB('hms_banner_queue');
            $db->addValue('asu_username', $names[1]);
            $db->addWhere('asu_username', $names[0]);
            $result = $db->update();
            if(PEAR::isError($result)){
                PHPWS_Error::logIfError($result);
                $tpl['errors'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "DB error trying to update banner queue.");
            }else{
                # Check to see if something happened
                $rows_affected = $db->affectedRows();
                if($rows_affected > 0){
                    $tpl['status'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "$rows_affected banner queue records updated.");
                    
                    $notes = "Banner Queue Updated";
                    HMS_Activity_Log::log_activity(trim($names[1]), ACTIVITY_BANNER_QUEUE_UPDATED, Current_User::getUsername(), $notes);
                }
            }
            
            # Update roommates
            $db = &new PHPWS_DB('hms_roommate');
            $db->addValue('requestor', $names[1]);
            $db->addWhere('requestor', $names[0]);
            $result = $db->update();
            if(PEAR::isError($result)){
                PHPWS_Error::logIfError($result);
                $tpl['errors'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "DB error trying to update roommate requestor");
            }else{
                # Check to see if something happened
                $rows_affected = $db->affectedRows();
                if($rows_affected > 0){
                    $tpl['status'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "$rows_affected roommate requestor records updated.");
                    
                    $notes = "Roommates Updated";
                    HMS_Activity_Log::log_activity(trim($names[1]), ACTIVITY_ROOMMATES_UPDATED, Current_User::getUsername(), $notes);
                }
            }
            
            $db = &new PHPWS_DB('hms_roommate');
            $db->addValue('requestee', $names[1]);
            $db->addWhere('requestee', $names[0]);
            $result = $db->update();
            if(PEAR::isError($result)){
                PHPWS_Error::logIfError($result);
                $tpl['errors'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "DB error trying to update roommate requestee.");
            }else{
                # Check to see if something happened
                $rows_affected = $db->affectedRows();
                if($rows_affected > 0){
                    $tpl['status'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "$rows_affected roommate requestee records updated.");

                    $notes = "Roommate Requests Updated";
                    HMS_Activity_Log::log_activity(trim($names[1]), ACTIVITY_ROOMMATE_REQUESTS_UPDATED, Current_User::getUsername(), $notes);
                }
            }

            # Update RLCs
            $db = &new PHPWS_DB('hms_learning_community_applications');
            $db->addValue('user_id', $names[1]);
            $db->addWhere('user_id', $names[0]);
            $result = $db->update();
            if(PEAR::isError($result)){
                PHPWS_Error::logIfError($result);
                $tpl['errors'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "DB error trying to update RLCs.");
            }else{
                # Check to see if something happened
                $rows_affected = $db->affectedRows();
                if($rows_affected > 0){
                    $tpl['status'][] = array('USERNAME'=>$names[0], 'MESSAGE' => "$rows_affected RLC records updated.");
                    
                    $notes = "RLCs Updated";
                    HMS_Activity_Log::log_activity(trim($names[1]), ACTIVITY_RLC_APPLICATION_UPDATED, Current_User::getUsername(), $notes);
                }
            }
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/process_username_change.tpl');
    }
}

?>
