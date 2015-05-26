0<?php

/**
 * Contains administrative public functionality
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Admin
{
    public function show_username_change()
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'username_change')){
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
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'username_change')){
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
