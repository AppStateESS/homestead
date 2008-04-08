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
}

?>
