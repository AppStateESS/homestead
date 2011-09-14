<?php

/**
 * Pending Assignments
 *
 * These guys have been auto-paired and will be auto-assigned, but haven't been yet.
 *
 * This class also takes care of all of the above automation.
 *
 * I'm wearing a suit today and it feels PIMP!  And it's almost time for Subway!!!
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class HMS_Pending_Assignment
{
    var $id;
    var $gender;
    var $lifestyle_option;
    var $chosen;
    var $roommate_zero;
    var $roommate_one;
    var $meal_zero;
    var $meal_one;

    public function HMS_Pending_Assignment($id = NULL)
    {
        if(!isset($id)) {
            return;
        }

        $db = &new PHPWS_DB('hms_pending_assignment');
        $db->addWhere('id');
        $result = $db->select('row');
        if(isset($result) && !empty($result)) {
            if(PHPWS_Error::isError($result)) {
                test($result,1);
            }
            $this->id               = $id;
            $this->gender           = $result['gender'];
            $this->lifestyle_option = $result['lifestyle_option'];
            $this->chosen           = $result['chosen'];
            $this->roommate_zero    = $result['roommate_zero'];
            $this->roommate_one     = $result['roommate_one'];
            $this->meal_zero        = $result['meal_zero'];
            $this->meal_one         = $result['meal_one'];
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_pending_assignment');
        $result = $db->saveObject($this);
        if(PEAR::isError($result)) {
            test($result,1);
        }
    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->addWhere('id',$this->id);
        $result = $db->delete();
        if(PEAR::isError($result)) {
            test($result,1);
        }
    }

    public function eligible_for_queue($ass)
    {
        if(empty($ass) || is_null($ass) || !isset($ass)) return FALSE;

        if(HMS_Assignment::check_for_assignment($ass)) return FALSE;

        if(HMS_Pending_Assignment::is_pending($ass)) return FALSE;

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        if(!HMS_SOAP::is_valid_student($ass)) return FALSE;

        return TRUE;
    }

    public function is_pending($ass)
    {
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->addWhere('roommate_zero', $ass, NULL, 'OR');
        $db->addWhere('roommate_one', $ass, NULL, 'OR');

        return !is_null($db->select('row'));
    }

    public function main()
    {
        switch($_REQUEST['op']) {
            case 'fill':
                return HMS_Pending_Assignment::auto_pair();
            case 'view':
                return HMS_Pending_Assignment::view();
            case 'clear':
                return HMS_Pending_Assignment::clear();
            case 'assign':
                PHPWS_Core::initModClass('hms', 'HMS_Autoassigner.php');
                return HMS_Autoassigner::auto_assign();
                #return HMS_Pending_Assignment::doIt();
            default:
                    return $_REQUEST['op'];
        }
    }

    /**
     * Automatically pairs up unassigned roommates based on their applications
     */
    public function auto_pair()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        $beds = HMS_Bed::get_all_empty_beds();
        test(count($beds[0]));
        test(count($beds[1]));
        test(count($beds[2]));
        test($beds,1);
        exit();

        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $gender_no_roommate[0] = 0;
        $gender_no_roommate[1] = 0;
        $lifestyle_unmatched = 0;
        $bedtime_unmatched = 0;
        $condition_unmatched = 0;

        $issues = array();

        // Requested Roommates
        /*
        $sql = "
        SELECT roommate_zero,
        roommate_one,
        zero.gender           AS zero_gender,
        one.gender            AS one_gender,
        zero.lifestyle_option AS zero_lifestyle_option,
        one.lifestyle_option  AS one_lifestyle_option,
        zero.meal_option      AS zero_meal_option,
        one.meal_option       AS one_meal_option,
        zero.student_status   AS zero_student_status,
        one.student_status    AS one_student_status
        FROM hms_roommates,
        hms_application AS zero,
        hms_application AS one
        WHERE hms_roommates.roommate_zero = zero.hms_student_id AND
        hms_roommates.roommate_one  = one.hms_student_id
        ";
        $results = PHPWS_DB::getAll($sql);

        if(PHPWS_Error::isError($results)) {
        test($results,1);
        }

        foreach($results as $result) {
        $zero['hms_student_id']   = $result['roommate_zero'];
        $zero['gender']           = $result['zero_gender'];
        $zero['lifestyle_option'] = $result['zero_lifestyle_option'];
        $zero['meal_option']      = $result['zero_meal_option'];
        $zero['student_status']   = $result['zero_student_status'];
        $one['hms_student_id']    = $result['roommate_one'];
        $one['gender']            = $result['one_gender'];
        $one['lifestyle_option']  = $result['one_lifestyle_option'];
        $one['meal_option']       = $result['one_meal_option'];
        $one['student_status']    = $result['one_student_status'];

        if(!HMS_Pending_Assignment::eligible_for_queue($zero['hms_student_id'])) {
        continue;
        }

        if(!HMS_Pending_Assignment::eligible_for_queue($one['hms_student_id'])) {
        continue;
        }

        if($zero['student_status'] != 1 && $one['student_status'] != 1) {
        continue;
        }

        if($zero['student_status'] != 1 || $one['student_status'] != 1) {
        $issues[] = '(' . $zero['hms_student_id'] . ') ' .
        HMS_SOAP::get_name($zero['hms_student_id']) .
        ' and ' .
        '(' . $one['hms_student_id'] . ') ' .
        HMS_SOAP::get_name($one['hms_student_id']) .
        ' are requested roommates, but one is a transfer student.  Skipping assignment.';
        continue;
        }

        if($zero['gender'] != $one['gender']) {
        $issues[] = '(' . $zero['hms_student_id'] . ') ' .
        HMS_SOAP::get_name($zero['hms_student_id']) .
        ' and ' .
        '(' . $one['hms_student_id'] . ') ' .
        HMS_SOAP::get_name($one['hms_student_id']) .
        ' are requested roommates, but their genders are different.  Skipping assignment.';
        continue;
        }

        HMS_Pending_Assignment::add($zero,$one,TRUE);
        }*/

        // We were going to disallow roommate selection for spring... and of course,
        // software requirements are about as static as Wikipedia's page on George W.
        // Bush... so here's a hack.  Delete all this shit ASAP.
        PHPWS_Core::initCoreClass("Database.php");
        $db = new PHPWS_DB('hms_roommate_hack');
        $db->addTable('hms_new_application', 'zero');
        $db->addTable('hms_new_application', 'one');
        $db->addColumn('hms_roommate_hack.requestor');
        $db->addColumn('hms_roommate_hack.requestee_username');
        $db->addColumn('zero.gender',           NULL, 'zero_gender');
        $db->addColumn('one.gender',            NULL, 'one_gender');
        $db->addColumn('zero.lifestyle_option', NULL, 'zero_lifestyle');
        $db->addColumn('one.lifestyle_option',  NULL, 'one_lifestyle');
        $db->addColumn('zero.meal_option',      NULL, 'zero_meal');
        $db->addColumn('one.meal_option',       NULL, 'one_meal');
        $db->addColumn('zero.student_type',     NULL, 'zero_status');
        $db->addColumn('one.student_type',      NULL, 'one_status');
        $db->addJoin('left', 'hms_roommate_hack', 'zero', 'requestor', 'hms_student_id');
        $db->addJoin('left', 'hms_roommate_hack', 'one',  'requestor', 'hms_student_id');
        $results = $db->select('all');

        // Error checking; TODO: this should be done better
        if(PHPWS_Error::isError($row)) {
            test($row, 1);
        }

        foreach($results as $result)
        {
            $zero['hms_student_id']   = $result['roommate_zero'];
            $zero['gender']           = $result['zero_gender'];
            $zero['lifestyle_option'] = $result['zero_lifestyle_option'];
            $zero['meal_option']      = $result['zero_meal_option'];
            $zero['student_type']     = $result['zero_student_type'];
            $one['hms_student_id']    = $result['roommate_one'];
            $one['gender']            = $result['one_gender'];
            $one['lifestyle_option']  = $result['one_lifestyle_option'];
            $one['meal_option']       = $result['one_meal_option'];
            $one['student_type']      = $result['one_student_type'];

            if(!HMS_Pending_Assignment::eligible_for_queue($zero['hms_student_id'])) {
                continue;
            }

            if(!HMS_Pending_Assignment::eligible_for_queue($one['hms_student_id'])) {
                continue;
            }

            HMS_Pending_Assignment::add($zero,$one,TRUE);
        }

        // Singletons
        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('id');
        $db->addColumn('gender');
        $db->addColumn('lifestyle_option');
        $db->addColumn('preferred_bedtime');
        $db->addColumn('room_condition');
        $db->addColumn('hms_student_id');
        $db->addColumn('meal_option');
        $db->addWhere('student_type',1);
        $db->addWhere('term', Term::getCurrentTerm());
        $db->addOrder('gender');
        $db->addOrder('lifestyle_option');
        $db->addOrder('preferred_bedtime');
        $db->addOrder('room_condition');
        $db->addOrder('random()');
        $results = $db->select();

        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $i = 0; $j = 1;
        while($i < count($results)) {
            $zero = $results[$i];
            $one  = $results[$j];

            $zero_eligible = HMS_Pending_Assignment::eligible_for_queue($zero['hms_student_id']);
            $one_eligible = HMS_Pending_Assignment::eligible_for_queue($one['hms_student_id']);

            // Both Already Assigned
            if(!$zero_eligible && !$one_eligible) {
                $i += 2; $j += 2;
                continue;
            }

            if(!$zero_eligible || !$one_eligible) {
                if(!$zero_eligible) {
                    $i++;
                    $zero = $results[$i];
                }

                do {
                    $j++;
                    if($j > count($results)) {
                        $one = NULL;
                        $i = $j + 1;
                        break;
                    }
                    $one = $results[$j];
                } while(!HMS_Pending_Assignment::eligible_for_queue($one['hms_student_id']));
                if($zero['gender'] != $one['gender']) {
                    HMS_Pending_Assignment::add($zero,NULL,FALSE);
                    $gender_no_roommate[$zero['gender']]++;
                    $issues[] = $zero['hms_student_id'] . ' has no roommate';

                    $i = $j;
                    $j = $i + 1;
                } else {
                    HMS_Pending_Assignment::add($zero,$one,FALSE);
                    $i = $j + 1;
                    $j = $i + 1;
                }
                continue;
            }

            // Neither Assigned....... more work!

            if($zero['gender'] != $one['gender']) {
                HMS_Pending_Assignment::add($zero,NULL,FALSE);

                $gender_no_roommate[$zero['gender']]++;
                $issues[] = $zero['hms_student_id'] . ' has no roommate';

                $i++; $j++;
                continue;
            }

            if($zero['lifestyle_option'] != $one['lifestyle_option']) {
                $lifestyle_unmatched++;
                $issues[] = $zero['hms_student_id'] . ' and ' . $one['hms_student_id'] . ' do not have the same lifestyle option.';
            }

            if($zero['preferred_bedtime'] != $one['preferred_bedtime']) {
                $bedtime_unmatched++;
                $issues[] = $zero['hms_student_id'] . ' and ' . $one['hms_student_id'] . ' do not have the same bedtime option.';
            }

            if($zero['room_condition'] != $one['room_condition']) {
                $condition_unmatched++;
                $issues[] = $zero['hms_student_id'] . ' and ' . $one['hms_student_id'] . ' do not have the same room condition option.';
            }


            HMS_Pending_Assignment::add($zero,$one,FALSE);
            $i+=2; $j+=2;
        }

        $content  = "<h2>Summary</h2>";
        $content .= "Females Without Roommate: " . $gender_no_roommate[0] . "<br />";
        $content .= "Males Without Roommate: " . $gender_no_roommate[1] . "<br />";
        $content .= "Lifestyle Option Unmatched: " . $lifestyle_unmatched . "<br />";
        $content .= "Bedtime Option Unmatched: " . $bedtime_unmatched . "<br />";
        $content .= "Room Condition Unmatched: " . $condition_unmatched . "<br />";
        $content .= "<br />";
        $content .= "<h2>Specific</h2>";
        foreach($issues as $issue) $content .= $issue . '<br />';
        return $content;
    }

    public function add($zero, $one, $chosen)
    {
        $ass                = &new HMS_Pending_Assignment();
        $ass->gender        = $zero['gender'];
        $ass->chosen        = $chosen?1:0;
        $ass->roommate_zero = $zero['hms_student_id'];
        $ass->meal_zero     = $zero['meal_option'];

        if(!is_null($one)) {
            if($zero['gender'] != $one['gender']) {
                test("Unforseen Gender Error ({$zero['hms_student_id']}, {$one['hms_student_id']}).  Please contact ESS immediately.",1);
            }
            $ass->roommate_one  = $one['hms_student_id'];
            $ass->meal_one      = $one['meal_option'];
            if($zero['lifestyle_option'] == 1 ||
            $one['lifestyle_option'] == 1) {
                $ass->lifestyle_option = 1;
            } else {
                $ass->lifestyle_option = 2;
            }
        } else {
            $ass->lifestyle_option = $zero['lifestyle_option'];
        }

        $ass->save();
    }

    public function view()
    {
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->addOrder('roommate_zero');
        $db->addOrder('roommate_one');
        $results = $db->select();

        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $content = '<h2>Pending Assignment Queue</h2><table>';

        $content .= '<tr><th colspan="2">Roommates</th>';
        $content .= '<th>Gender</th>';
        $content .= '<th>Dorm Type</th>';
        $content .= '<th>Roommate</th>';

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        foreach($results as $result) {
            $content .= '<tr><td>';
            $content .= '(' . $result['roommate_zero'] . ') ' .
            HMS_SOAP::get_name($result['roommate_zero']) .
                '</td>';
            $content .= '<td>';
            if(!empty($result['roommate_one'])) {
                $content .=
                    '(' . $result['roommate_one'] . ') ' .
                HMS_SOAP::get_name($result['roommate_one']);
            }
            $content .= '</td>';
            $content .= '<td>' .
            ($result['gender'] == 0 ? 'Female' : 'Male') .
                '</td>';
            $content .= '<td>' .
            ($result['lifestyle_option'] == 1 ? 'Single Gender' :
                'Co-Ed') . '</td>';
            $content .= '<td>' .
            ($result['chosen'] == 1 ? 'Requested' : 'Assigned') .
                '</td>';
            $content .= '</tr>';
        }
        $content .= '</table>';

        return $content;
    }

    public function clear()
    {
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->delete();
        return "Assignment Queue Cleared";
    }

    public function doIt()
    {
        $FEMALE = 0;
        $MALE   = 1;
        $SINGLE = 1;
        $COED   = 2;
        // Single Gender Females
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->addWhere('gender',$FEMALE);
        $db->addWhere('lifestyle_option',$SINGLE);
        $db->addOrder('chosen desc');
        $db->addOrder('random');
        $pending_sg_f = $db->select();
        if(PHPWS_Error::isError($pending_sg_f)) {
            test($pending_sg_f,1);
        }

        // Co-ed Females
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->addWhere('gender', $FEMALE);
        $db->addWhere('lifestyle_option',$COED);
        $db->addOrder('chosen desc');
        $db->addOrder('random');
        $pending_ce_f = $db->select();
        if(PHPWS_Error::isError($pending_ce_f)) {
            test($pending_ce_f,1);
        }

        // Single Gender Males
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->addWhere('gender', $MALE);
        $db->addWhere('lifestyle_option',$SINGLE);
        $db->addOrder('chosen desc');
        $db->addOrder('random');
        $pending_sg_m = $db->select();
        if(PHPWS_Error::isError($pending_sg_m)) {
            test($pending_sg_m,1);
        }

        // Co-ed Males
        $db = new PHPWS_DB('hms_pending_assignment');
        $db->addWhere('gender',$MALE);
        $db->addWhere('lifestyle_option',$COED);
        $db->addWhere('chosen desc');
        $db->addOrder('random');
        $pending_ce_m = $db->select();
        if(PHPWS_Error::isError($pending_ce_m)) {
            test($pending_ce_m,1);
        }

        // Beds for Butts
        $db = new PHPWS_DB('hms_beds');
        $db->addColumn('hms_beds.id');
        $db->addColumn('hms_beds.bedroom_id');
        $db->addWhere('hms_beds.bedroom_id','hms_bedrooms.id');
        $db->addWhere('hms_bedrooms.room_id','hms_room.id');
        $db->addWhere('hms_room.floor_id','hms_floor.id');
        $db->addWhere('hms_floor.building','hms_residence_hall.id');
        $db->addWhere('hms_beds.deleted',0);
        $db->addWhere('hms_bedrooms.deleted',0);
        $db->addWhere('hms_bedrooms.is_online',1);
        $db->addWhere('hms_bedrooms.is_medical',0);
        $db->addWhere('hms_bedrooms.is_reserved',0);
        $db->addWhere('hms_room.deleted',0);
        $db->addWhere('hms_room.is_online',1);
        $db->addWhere('hms_room.is_medical',0);
        $db->addWhere('hms_room.is_reserved',0);
        $db->addWhere('hms_floor.deleted',0);
        $db->addWhere('hms_floor.is_online',1);
        $db->addWhere('hms_residence_hall.deleted',0);
        $db->addWhere('hms_residence_hall.is_online',1);
        $beds = $db->select();
        if(PHPWS_Error::isError($beds)) {
            test($beds,1);
        }

        $index_sg_f = 0;
        $index_ce_f = 0;
        $index_sg_m = 0;
        $index_ce_m = 0;

        $badbeds = array();

        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bedroom.php');
        foreach($beds as $bed) {
            $bed_id  = $bed['id'];
            $bedroom = $bed['bedroom_id'];
            if(HMS_Assignment::is_bed_assigned($bed_id)) continue;
            if(!HMS_Bedroom::is_bedroom_empty($bedroom)) continue;
             
            if(HMS_Bedroom::static_get_number_beds($bedroom) != 2) continue;
            if(HMS_Bed::associated_objects_deleted($bed_id)) continue;
            if(!HMS_Bed::associated_objects_online($bed_id)) continue;

            $other_bed_id = HMS_Bedroom::get_other_bed($bedroom, $bed_id);
            if(HMS_Assignment::is_bed_assigned($other_bed_id)) continue;

            $type   = HMS_Bedroom::determine_lifestyle_type($bedroom);
            $gender = HMS_Bedroom::determine_gender($bedroom);

            $pair = NULL;

            $blah['type'] = $type;
            $blah['gender'] = $gender;

            if($type == $SINGLE) {
                if($gender == $FEMALE) {
                    $pair = $pending_sg_f[$index_sg_f++];
                    if($pair == NULL) {
                        $pair = $pending_ce_f[$index_ce_f++];
                    }
                } else if($gender == $MALE) {
                    $pair = $pending_sg_m[$index_sg_m++];
                    if($pair == NULL) {
                        $pair = $pending_ce_m[$index_ce_m++];
                    }
                }
            } else if($type == $COED) {
                if($gender == $FEMALE) {
                    $pair = $pending_ce_f[$index_ce_f++];
                    if($pair == NULL) {
                        $pair = $pending_sg_f[$index_sg_f++];
                    }
                } else if($gender == $MALE) {
                    $pair = $pending_ce_m[$index_ce_m++];
                    if($pair == NULL) {
                        $pair = $pending_sg_m[$index_sg_m++];
                    }
                }
            }

            if($pair == NULL) {
                $sql = "
                    SELECT hms_residence_hall.hall_name,
                           hms_room.room_number,
                           hms_assignment.id
                    FROM hms_residence_hall,
                         hms_floor,
                         hms_room,
                         hms_bedrooms,
                         hms_beds
                    LEFT OUTER JOIN hms_assignment ON
                         hms_assignment.bed_id = hms_beds.id AND
                         hms_assignment.deleted = 0
                    WHERE hms_beds.id          = $bed_id         AND
                          hms_beds.bedroom_id  = hms_bedrooms.id AND
                          hms_bedrooms.room_id = hms_room.id     AND
                          hms_room.floor_id    = hms_floor.id    AND
                          hms_floor.building   = hms_residence_hall.id AND
                          hms_beds.deleted     = 0 AND
                          hms_bedrooms.deleted = 0 AND
                          hms_room.deleted     = 0 AND
                          hms_floor.deleted    = 0 AND
                          hms_residence_hall.deleted = 0
                          ";
                $results = PHPWS_DB::getAll($sql);
                if(PHPWS_Error::isError($results)) {
                    test($results,1);
                }
                $results = $results[0];
                $reason = "Could not fill {$results['hall_name']} {$results['room_number']} because ";
                if(!empty($results['id'])) {
                    $reason .= "room has already been assigned.";
                } else if($gender == 2) {
                    $reason .= "room gender has not been specified.";
                } else {
                    $reason .= "no more " . ($gender == $FEMALE ? "female" : "male") . " students are available.";
                }
                $badbeds[] = $reason;
            } else {
                $ass = new HMS_Assignment();
                $ass->set_asu_username($pair['roommate_zero']);
                $ass->set_bed_id($bed_id);
                $ass->set_timestamp(mktime());
                $ass->set_meal_option($pair['meal_zero']);
                $ass->save_assignment();

                $ass = new HMS_Assignment();
                $ass->set_asu_username($pair['roommate_one']);
                $ass->set_bed_id($other_bed_id);
                $ass->set_timestamp(mktime());
                $ass->set_meal_option($pair['meal_one']);
                $ass->save_assignment();

                $db = new PHPWS_DB('hms_pending_assignment');
                $db->addWhere('id',$pair['id']);
                $db->delete();
            }

        }

        $content = '<br /><h2>Could Not Be Auto-Assigned</h2>';

        $content .= '<br /><h3>Female, Single-Gender</h3>';
        while($index_sg_f < count($pending_sg_f))
        $content .= ($pending_sg_f[$index_sg_f]['chosen'] ? '(Requested) ' : '(Assigned) ') .
        $pending_sg_f[$index_sg_f]['roommate_zero'] . ', ' .
        $pending_sg_f[$index_sg_f++]['roommate_one'] . '<br />';

        $content .= '<br /><h3>Female, Co-Ed</h3>';
        while($index_ce_f < count($pending_ce_f))
        $content .= ($pending_ce_f[$index_ce_f]['chosen'] ? '(Requested) ' : '(Assigned) ') .
        $pending_ce_f[$index_ce_f]['roommate_zero'] . ', ' .
        $pending_ce_f[$index_ce_f++]['roommate_one'] . '<br />';

        $content .= '<br /><h3>Male, Single-Gender</h3>';
        while($index_sg_m < count($pending_sg_m))
        $content .= ($pending_sg_m[$index_sg_m]['chosen'] ? '(Requested) ' : '(Assigned) ') .
        $pending_sg_m[$index_sg_m]['roommate_zero'] . ', ' .
        $pending_sg_m[$index_sg_m++]['roommate_one'] . '<br />';

        $content .= '<br /><h3>Male, Co-Ed</h3>';
        while($index_ce_m < count($pending_ce_m))
        $content .= ($pending_ce_m[$index_ce_m]['chosen'] ? '(Requested) ' : '(Assigned) ') .
        $pending_ce_m[$index_ce_m]['roommate_zero'] . ', ' .
        $pending_ce_m[$index_ce_m++]['roommate_one'] . '<br />';

        $content .= '<br /><h2>Unclassified (Co-Ed) Rooms</h2>';
        foreach($badbeds as $bed) {
            $content .= $bed . '<br />';
        }

        return $content;
    }
}
