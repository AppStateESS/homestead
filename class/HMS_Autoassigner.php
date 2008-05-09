<?php

/**
 * Auto Assigner
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class HMS_Autoassigner
{
    function auto_assign($test = 0)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        if(!$test) {
            PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
            PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        }

        $beds = HMS_Bed::get_all_empty_beds();
        $applicants = HMS_Application::get_unassigned_applicants('student_status', 'random');

        $problems = array();
        $successes = array();

        reset($beds[0]);
        reset($beds[1]);
        foreach($applicants as $applicant)
        {
            $bed = next($beds[$applicant->gender]);
            if($bed === false) {
                $problems[] = "Could not assign <strong>{$applicant->hms_student_id}</strong>; out of empty ".($applicant->gender?'male':'female').' beds.';
                continue;
            }

            if(!$test) {
                // Get Meal Plan
                $meal_plan = HMS_SOAP::get_plan_meal_codes($applicant->hms_student_id, $bed['hall'], $applicant->meal_option);

                $banner_success = HMS_Banner_Queue::queue_create_assignment(
                    $applicant->hms_student_id,
                    HMS_Term::get_selected_term(),
                    $bed['hall'],
                    $bed['bed']->banner_id,
                    $meal_plan['plan'],
                    $meal_plan['meal']
                );

                if($banner_success) {
                    $problems[] = "Skipped bed {$bed['bed']->id} username {$applicant->hms_student_id} due to banner error code $banner_success";
                    continue;
                }

                $assignment = new HMS_Assignment();

                $assignment->asu_username = $applicant->hms_student_id;
                $assignment->bed_id       = $bed['bed']->id;
                $assignment->term         = HMS_Term::get_selected_term();

                $result = $assignment->save();

                HMS_Activity_Log::log_activity($applicant->hms_student_id, ACTIVITY_ASSIGNED, Current_User::getUsername(), HMS_Term::get_selected_term() . ' ' . $bed['hall'] . ' ' . $bed['bed']->banner_id);

                $successes[] = "Assigned <strong>{$applicant->hms_student_id}</strong> to <strong>{$bed['hall']} {$bed['bed']->banner_id}</strong>";
            } else {
                $successes[] = "Would have assigned {$applicant->hms_student_id} to bed {$bed['bed']->id}";
            }
        }

        return '<h2>Problems:' . count($problems) . "</h2>\n" . implode("<br />\n", $problems) . '<br /><br />' .
               '<h2>Successes: ' . count($successes) . "</h2>\n" . implode("<br />\n", $successes);
    }

/**
 * These don't work because of the deleted flags...
 */

    /**
     * Returns the ID of an empty room (which can be auto-assigned)
     * Returns FALSE if there are no more free rooms
     */
     /*
    function get_free_room($term, $gender, $randomize = FALSE)
    {
        $db = &new PHPWS_DB('hms_room');

        // Only get free rooms
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_bed', 'id', 'room_id');
        $db->addJOIN('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');

    }
    */

    /**
     * Returns the ID of a free bed (which can be auto-assigned)
     * Returns FALSE if there are no more free beds
     */
     /*
    function get_free_bed($term, $gender, $randomize = FALSE)
    {
        $db = &new PHPWS_DB('hms_bed');

        // Only get free beds
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_assignment', 'id', 'bed_id');
        $db->addWhere('hms_assignment.asu_username', NULL);

        // Join other tables so we can do the other 'assignable' checks
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        // Term
        $db->addWhere('hms_bed.term', $term);

        // Gender
        $db->addWhere('hms_room.gender_type', $gender);

        // Make sure nothing is deleted
        $db->addWhere('hms_bed.deleted', 0);
        $db->addWhere('hms_room.deleted', 0);
        $db->addWhere('hms_floor.deleted', 0);
        $db->addWhere('hms_residence_hall.deleted', 0);

        // Make sure everything is online
        $db->addWhere('hms_room.is_online', 1);
        $db->addWhere('hms_floor.is_online', 1);
        $db->addWhere('hms_residence_hall.is_online', 1);

        // Make sure nothing is reserved
        $db->addWhere('hms_room.is_reserved', 0);
        $db->addWhere('hms_room.is_medical', 0);

        // Don't get RA beds
        $db->addWhere('hms_room.ra_room', 0);

        // Don't get the lobbies
        $db->addWhere('hms_room.is_lobby', 0);

        // Don't get the private rooms
        $db->addWhere('hms_room.private_room', 0);

        // Don't get rooms on floors reserved for an RLC
        $db->addWhere('hms_floor.rlc_id', NULL);

        $result = $db->select();

        // In case of an error, log it and return it
        if(PHPWS_Error::logIfError($result)){
            return $result;
        }

        // Return FALSE if there were no results
        if(sizeof($result) <= 0){
            return FALSE;
        }

        if($randomize){
            // Get a random index between 0 and the max array index (size - 1)
            $random_index = mt_rand(0, sizeof($result)-1);
            return $result[$random_index]['id'];
        }else{
            return $result[0]['id'];
        }
    }

    */
}

?>
