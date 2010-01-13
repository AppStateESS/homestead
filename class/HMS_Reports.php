<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php'); // Just go ahead and do this here, since a lot of reports use it

class HMS_Reports{

    /**
     * Returns an array listing all the possible reports
     */
    public static function getReports()
    {
        $reports = array(
                        'housing_apps'          => 'Housing Applications Received',
                        'housing_asss'          => 'Assignment Demographics',
                        'assigned_f'            => 'Assigned Type F Students',
                        'special_needs'         => 'Special Needs Applicants',
                        'unassd_apps'           => 'Unassigned Applicants',
                        'movein_times'          => 'Move-in Times',
                        'unassd_beds'           => 'Currently Unassigned Beds',
                        'no_ban_data'           => 'Students Without Banner Data',
                        'vacancy_report'        => 'Hall Occupancy Report',
                        'roster_report'         => 'Floor Roster Report',
                        'applied_data_export'   => 'Applied Student Data Export',
                        'assigned_data_export'  => 'Assigned Student Data Export',
                        'full_roster_report'    => 'Package Desk Roster',
                        'over_twenty_five'      => 'Over 25 report'
                        );
                        /*                        'housing_asss' => 'Housing Assignments Made',*/
                        /*                        'unassd_rooms' => 'Currently Unassigned Rooms',*/
                        /*                        'unassd_beds'  => 'Currently Unassigned Beds',*/
                        /*                        'reqd_roommate'=> 'Unconfirmed Roommates',*/
                        /*                        'assd_alpha'   => 'Assigned Students',*/
                        /*                        'special'      => 'Special Circumstances',*/
                        /*                        'hall_structs' => 'Hall Structures');*/
                        /*                        'no_ban_data'  => 'Students Without Banner Data',*/
                        /*                        'no_deposit'   => 'Assigned Students with No Deposit',*/
                        /*                        'bad_type'     => 'Assigned Students Withdrawn or with Bad Type',*/
                        /*                        'gender'       => 'Gender Mismatches');*/

                        return $reports;
    }

    public static function runReport($reportName)
    {
        if(!Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do no have permission to run reports.');
        }

        $content = '<p><a href="javascript:window.print()">Print Report</a></p>';

        // Go ahead an initalize the Term class, since it's going to be needed by all reports
        PHPWS_Core::initModClass('hms', 'Term.php');

        switch($reportName)
        {
            case 'housing_apps':
                $content .= HMS_Reports::run_applicant_demographics_report();
                break;
            case 'housing_asss':
                $content .= HMS_Reports::run_assignment_demographics_report();
                break;
            case 'assigned_f':
                $content .= HMS_Reports::run_assigned_type_f();
                break;
            case 'special_needs':
                $content .= HMS_Reports::special_needs();
                break;
            case 'unassd_apps':
                return HMS_Reports::unassigned_applicants_report();
            case 'movein_times':
                return HMS_Reports::run_move_in_times_report();
            case 'unassd_beds':
                return HMS_Reports::run_unassigned_beds_report();
            case 'no_ban_data':
                return HMS_Reports::run_no_banner_data_report();
            case 'vacancy_report':
                return HMS_Reports::run_hall_occupancy_report();
            case 'roster_report':
                return HMS_Reports::assignment_roster_report();
            case 'applied_data_export':
                return HMS_Reports::applied_student_data_export();
            case 'assigned_data_export':
                return HMS_Reports::assigned_student_data_export();
            case 'full_roster_report':
                return HMS_Reports::roster_report();
            case 'over_twenty_five':
                return HMS_Reports::over_twenty_five_report();
           default:
                $content .= "ugh";
                break;
        }

        return $content;
    }

    /**
     * Assignment demographics report
     *
     * A report which breaks down current assignments by student type, class, gender, and the hall to which they're assigned.
     * Also gives a totals by student type, class, and gender for all halls.
     */
    public static function run_assignment_demographics_report()
    {
        # Start the timer
        $start_time = microtime();

        $total_other = 0; #Count all students with invalid data and lump
        #them into their own column

        $term = Term::getSelectedTerm();

        $building = array(); // Define an array to hold each building's summary

        # Get a list of hall ID's and names
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addWhere('term', $term);
        $db->addWhere('is_online', 1); // only get halls that are online
        $db->addOrder('hall_name', 'asc');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        # For each hall, get every assignment in that hall and tally it up
        foreach($result as $hall_row) {
            $db = &new PHPWS_DB('hms_assignment');
            $db->addColumn('hms_assignment.asu_username');

            # Just get "this" hall
            $db->addWhere('hms_residence_hall.id', $hall_row['id']);

            # Join the assignment all the way up to the hall
            $db->addJoin('LEFT OUTER', 'hms_assignment',    'hms_bed',              'bed_id',               'id');
            $db->addJoin('LEFT OUTER', 'hms_bed',           'hms_room',             'room_id',              'id');
            $db->addJoin('LEFT OUTER', 'hms_room',          'hms_floor',            'floor_id',             'id');
            $db->addJoin('LEFT OUTER', 'hms_floor',         'hms_residence_hall',   'residence_hall_id',    'id');

            # Don't report on anything that's not online
            $db->addWhere('hms_room.is_online',             1);
            $db->addWhere('hms_floor.is_online',            1);
            $db->addWhere('hms_residence_hall.is_online',   1);

            $assignments = $db->select();

            if(PHPWS_Error::logIfError($assignments)) {
                PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
                throw new DatabaseException($assignments->toString());
            }

            # Initalize this hall's summary
            foreach(array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, TYPE_READMIT) as $init_type){
                foreach(array(CLASS_FRESHMEN, CLASS_SOPHOMORE, CLASS_JUNIOR, CLASS_SENIOR) as $init_class){
                    foreach(array(MALE, FEMALE) as $init_gender){
                        $building[$hall_row['hall_name']][$init_type][$init_class][$init_gender] = 0;
                    }
                }
            }

            $otherByHall[$hall_row['hall_name']] = 0;

            # For each assignment we found in this hall...
            foreach($assignments as $assignment) {

                # Create the student object
                $student = StudentFactory::getStudentByUsername($assignment['asu_username'], $term);

                # Get the gender (in numeric form) of the student for this assignment
                $gender = $student->getGender();

                # Check the gender for bad data
                if(!isset($gender) || $gender === NULL || ($gender != MALE && $gender != FEMALE)) {
                    $problems[] = $assignment['asu_username'] .': Gender is unrecognized ('. $gender .')';
                    $otherByHall[$hall_row['hall_name']]++;
                    $total_other++;
                    continue;
                }

                # Get the class of the student for this assignment
                $class = $student->getClass();

                # Check the class for bad data
                if(!isset($class) || $class === NULL ||
                ($class != CLASS_FRESHMEN && $class != CLASS_SOPHOMORE &&
                $class != CLASS_JUNIOR && $class != CLASS_SENIOR)) {
                    $problems[] = $assignment['asu_username'] . ': Class is unrecognized ('. $class .')';
                    $otherByHall[$hall_row['hall_name']]++;
                    $total_other++;
                    continue;
                }

                # Get the type of the student for this assignment
                $type = $student->getType();

                # Check the type for bad data
                if(!isset($type) || $type === NULL ||
                ($type != TYPE_FRESHMEN && $type != TYPE_TRANSFER && $type != TYPE_CONTINUING && $type != TYPE_READMIT)) {
                    $problems[] = $assignment['asu_username'] . ': Type is unrecognized ('. $type .')';
                    $otherByHall[$hall_row['hall_name']]++;
                    $total_other++;
                    continue;
                }

                $credit_hours = $student->getCreditHours();

                # Check for a freshmen type, but a mis-matched class
                if(($type == TYPE_FRESHMEN && $class != CLASS_FRESHMEN)){
                    $problems[] = $assignment['asu_username'] . ": Type is $type, class is $class, credit hours is $credit_hours";
                    //$otherByHall[$hall_row['hall_name']]++;
                    //$total_other++;
                    //continue;
                }

                # Check for a mis-matched type/class/hours situation
                if( $type == TYPE_CONTINUING && $class == CLASS_FRESHMEN && $credit_hours == 0){
                    $problems[] = $assignment['asu_username'] . ": Type is $type, class is $class, credit hours is $credit_hours";
                    //$otherByHall[$hall_row['hall_name']]++;
                    //$total_other++;
                    //continue;
                }

                $building[$hall_row['hall_name']][$type][$class][$gender]++;
            }
        }

        # Initalize a 3 dimensional table for summing up the totals
        foreach(array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, TYPE_READMIT) as $init_type){
            foreach(array(CLASS_FRESHMEN, CLASS_SOPHOMORE, CLASS_JUNIOR, CLASS_SENIOR) as $init_class){
                foreach(array(MALE, FEMALE) as $init_gender){
                    $total[$init_type][$init_class][$init_gender] = 0;
                }
            }
        }

        $grandTotalMales = 0;
        $grandTotalFemales = 0;

        $content = '';

        # Print out the header
        $termPrint = Term::getPrintableSelectedTerm();
        $content .= '<br /><br />';
        $content .= "$termPrint - Housing assignments by building, student type, class, and gender";
        $content .= '<br /><br />';

        # Show any problems that occured
        if(isset($problems) && count($problems) > 0) {
            $content .= '<font color="red"><b>Some problems were found while retrieving data from Banner:</b></font><br />';
            foreach($problems as $problem) {
                $content .= $problem . '<br />';
            }
            $content .= '<br /><br />';
        }

        foreach($building as $name=>$hall) {
            ksort($hall);
            # Generate our totals for later in the form
            $total_males = 0;
            $total_females = 0;

            foreach($building[$name] as $type){
                foreach($type as $year){
                    $total_males   += $year[MALE];
                    $total_females += $year[FEMALE];
                }
            }

            $content .= '<table border="1">';
            $content .= '<tr><th colspan="19"><h2 style="text-align: center">' . $name . '</h2></th></tr>';
            $content .= '<tr>';
            $content .= '<td rowspan="2"></td>';
            $content .= '<th colspan="4">Freshmen (F)</th>';
            $content .= '<th colspan="4">Continuing (C)</th>';
            $content .= '<th colspan="4">Transfer (T)</th>';
            $content .= '<th colspan="4">Readmit (Z)</th>';
            $content .= '<th rowspan="2">Other (O)</th>';
            $content .= '<th rowspan="2">Totals </th>';
            $content .= '</tr><tr>';
            $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
            $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
            $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
            $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
            $content .= '</tr><tr>';
            $content .= '<th>Male</th>';
            $content .= '<td>' . $building[$name]['F']['FR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['F']['SO'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['F']['JR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['F']['SR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['FR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['SO'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['JR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['SR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['FR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['SO'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['JR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['SR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['FR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['SO'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['JR'][MALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['SR'][MALE]   . '</td>';
            $content .= '<td rowspan="2">' . $otherByHall[$name]     . '</td>';
            $content .= '<td>' . $total_males                        . '</td>';
            $content .= '</tr><tr>';
            $content .= '<th>Female</th>';
            $content .= '<td>' . $building[$name]['F']['FR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['F']['SO'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['F']['JR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['F']['SR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['FR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['SO'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['JR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['C']['SR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['FR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['SO'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['JR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['T']['SR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['FR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['SO'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['JR'][FEMALE]   . '</td>';
            $content .= '<td>' . $building[$name]['Z']['SR'][FEMALE]   . '</td>';
            $content .= '<td>' . $total_females                        . '</td>';
            $content .= '</tr><tr>';
            $content .= '<th>Total</th>';
            $content .= '<td colspan="17">  </td>';
            $content .= '<td>' . ($total_males+$total_females+$otherByHall[$name] >= 0 ?
            $total_males+$total_females+$otherByHall[$name] : '0') . '</td>';
            $content .= '</tr></table><br /><br />';

            $total['F']['FR'][MALE]  += $building[$name]['F']['FR'][MALE];
            $total['F']['SO'][MALE]  += $building[$name]['F']['SO'][MALE];
            $total['F']['JR'][MALE]  += $building[$name]['F']['JR'][MALE];
            $total['F']['SR'][MALE]  += $building[$name]['F']['SR'][MALE];
            $total['C']['FR'][MALE]  += $building[$name]['C']['FR'][MALE];
            $total['C']['SO'][MALE]  += $building[$name]['C']['SO'][MALE];
            $total['C']['JR'][MALE]  += $building[$name]['C']['JR'][MALE];
            $total['C']['SR'][MALE]  += $building[$name]['C']['SR'][MALE];
            $total['T']['FR'][MALE]  += $building[$name]['T']['FR'][MALE];
            $total['T']['SO'][MALE]  += $building[$name]['T']['SO'][MALE];
            $total['T']['JR'][MALE]  += $building[$name]['T']['JR'][MALE];
            $total['T']['SR'][MALE]  += $building[$name]['T']['SR'][MALE];
            $total['Z']['FR'][MALE]  += $building[$name]['Z']['FR'][MALE];
            $total['Z']['SO'][MALE]  += $building[$name]['Z']['SO'][MALE];
            $total['Z']['JR'][MALE]  += $building[$name]['Z']['JR'][MALE];
            $total['Z']['SR'][MALE]  += $building[$name]['Z']['SR'][MALE];
            $grandTotalMales         += $total_males;

            $total['F']['FR'][FEMALE]  += $building[$name]['F']['FR'][FEMALE];
            $total['F']['SO'][FEMALE]  += $building[$name]['F']['SO'][FEMALE];
            $total['F']['JR'][FEMALE]  += $building[$name]['F']['JR'][FEMALE];
            $total['F']['SR'][FEMALE]  += $building[$name]['F']['SR'][FEMALE];
            $total['C']['FR'][FEMALE]  += $building[$name]['C']['FR'][FEMALE];
            $total['C']['SO'][FEMALE]  += $building[$name]['C']['SO'][FEMALE];
            $total['C']['JR'][FEMALE]  += $building[$name]['C']['JR'][FEMALE];
            $total['C']['SR'][FEMALE]  += $building[$name]['C']['SR'][FEMALE];
            $total['T']['FR'][FEMALE]  += $building[$name]['T']['FR'][FEMALE];
            $total['T']['SO'][FEMALE]  += $building[$name]['T']['SO'][FEMALE];
            $total['T']['JR'][FEMALE]  += $building[$name]['T']['JR'][FEMALE];
            $total['T']['SR'][FEMALE]  += $building[$name]['T']['SR'][FEMALE];
            $total['Z']['FR'][FEMALE]  += $building[$name]['Z']['FR'][FEMALE];
            $total['Z']['SO'][FEMALE]  += $building[$name]['Z']['SO'][FEMALE];
            $total['Z']['JR'][FEMALE]  += $building[$name]['Z']['JR'][FEMALE];
            $total['Z']['SR'][FEMALE]  += $building[$name]['Z']['SR'][FEMALE];
            $grandTotalFemales         += $total_females;

            next($building);
        }
        $content .= '======================================================';

        $content .= '<table border="1">';
        $content .= '<tr><th colspan="19" style="text-align: center"><h2>TOTALS</h2></th></tr>';
        $content .= '<tr>';
        $content .= '<td rowspan="2"></td>';
        $content .= '<th colspan="4">Freshmen (F)</th>';
        $content .= '<th colspan="4">Continuing (C)</th>';
        $content .= '<th colspan="4">Transfer (T)</th>';
        $content .= '<th colspan="4">Readmit (Z)</th>';
        $content .= '<th rowspan="2">Other (O)</th>';
        $content .= '<th rowspan="2">Totals</th>';
        $content .= '</tr><tr>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '</tr><tr>';
        $content .= '<th>Male</th>';
        $content .= '<td>' . $total['F']['FR'][MALE]   . '</td>';
        $content .= '<td>' . $total['F']['SO'][MALE]   . '</td>';
        $content .= '<td>' . $total['F']['JR'][MALE]   . '</td>';
        $content .= '<td>' . $total['F']['SR'][MALE]   . '</td>';
        $content .= '<td>' . $total['C']['FR'][MALE]   . '</td>';
        $content .= '<td>' . $total['C']['SO'][MALE]   . '</td>';
        $content .= '<td>' . $total['C']['JR'][MALE]   . '</td>';
        $content .= '<td>' . $total['C']['SR'][MALE]   . '</td>';
        $content .= '<td>' . $total['T']['FR'][MALE]   . '</td>';
        $content .= '<td>' . $total['T']['SO'][MALE]   . '</td>';
        $content .= '<td>' . $total['T']['JR'][MALE]   . '</td>';
        $content .= '<td>' . $total['T']['SR'][MALE]   . '</td>';
        $content .= '<td>' . $total['Z']['FR'][MALE]   . '</td>';
        $content .= '<td>' . $total['Z']['SO'][MALE]   . '</td>';
        $content .= '<td>' . $total['Z']['JR'][MALE]   . '</td>';
        $content .= '<td>' . $total['Z']['SR'][MALE]   . '</td>';
        $content .= '<td rowspan="2">' . $total_other  . '</td>';
        $content .= '<td>' . $grandTotalMales          . '</td>';
        $content .= '</tr><tr>';
        $content .= '<th>Female</th>';
        $content .= '<td>' . $total['F']['FR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['F']['SO'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['F']['JR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['F']['SR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['C']['FR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['C']['SO'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['C']['JR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['C']['SR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['T']['FR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['T']['SO'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['T']['JR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['T']['SR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['Z']['FR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['Z']['SO'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['Z']['JR'][FEMALE]   . '</td>';
        $content .= '<td>' . $total['Z']['SR'][FEMALE]   . '</td>';
        $content .= '<td>' . $grandTotalFemales          . '</td>';
        $content .= '</tr><tr>';
        $content .= '<th>Total</th>';
        $content .= '<td colspan="17"></td>';
        $content .= '<td>'. ($grandTotalMales+$grandTotalFemales+$total_other >= 0 ?
        $grandTotalMales+$grandTotalFemales+$total_other : 0). '</td>';
        $content .= '</tr></table><br /><br />';
        $content .=  "<br /> ";

        if(isset($problems) && count($problems) > 0) {
            $content .= '<h2 style="color: red;">Errors:</h2>';
            $content .=  '<span style="color: red; font-weight: bold;">Unknown Gender, Type, or Class: ' . count($problems) . '</span><br /> ';
        }
        $content .=  "<br /><br /> ";

        # Stop the timer and compute elapsed time
        $elapsed_time = microtime() - $start_time;

        $content .= "Elapsed time: $elapsed_time seconds<br /><br />";

        return $content;
    }

    public static function run_applicant_demographics_report(){
        # Note the start time
        $start_time = microtime();

        $term           = Term::getSelectedTerm();
        $tpl['TERM']    = Term::getPrintableSelectedTerm();

        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('term', $term);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($results->toString());
        }

        $types      = array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, TYPE_READMIT, TYPE_RETURNING, TYPE_NONDEGREE, TYPE_WITHDRAWN);
        $genders    = array(MALE, FEMALE);

        # Initalize the array for totals
        foreach($types as $init_type){
            foreach($genders as $init_gender){
                $application_totals[$init_type][$init_gender] = 0;
            }
        }

        # Calculate the totals
        foreach($results as $application){
            $application_totals[$application['student_type']][$application['gender']]++;
        }

        # Populate the template vars
        $male_sum = 0;
        foreach($types as $type){
            $tpl['male_totals'][] = array('COUNT'=>$application_totals[$type][MALE]);
            $male_sum += $application_totals[$type][MALE];
        }
        $tpl['MALE_SUM'] = $male_sum;

        $female_sum = 0;
        foreach($types as $type){
            $tpl['female_totals'][] = array('COUNT'=>$application_totals[$type][FEMALE]);
            $female_sum += $application_totals[$type][FEMALE];
        }
        $tpl['FEMALE_SUM'] = $female_sum;

        $tpl['ALL_TOTAL'] = $female_sum + $male_sum;

        $type_totals = array();
        foreach($types as $type){
            $tpl['type_totals'][] = array('COUNT'=>array_sum($application_totals[$type]));
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/application_demographics.tpl');
    }

    /**
     * Finds and lists all currently assigned students who have a banner type of F
     */
    public static function run_assigned_type_f(){

        $term = Term::getSelectedTerm();

        $db = &new PHPWS_DB('hms_assignment');
        $db->addWhere('term', $term);

        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $content = '';

        $content = '<table>
                     <tr>
                        <th>User name</th>
                        <th>Banner ID</th>
                        <th>Entry Term</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Credit Hours</th>
                        <th>DOB</th>
                     </tr>
                    ';

        foreach($result as $assignment){
            $student = StudentFactory::getStudentByUsername($assignment['asu_username'], $term);
            if($student->getType() == TYPE_FRESHMEN){
                $content .= '<tr>';
                $content .= '<td>' . $student->getUsername() . '</td>';
                $content .= '<td>' . $student->getBannerId() . '</td>';
                $content .= '<td>' . $student->getApplicationTerm() . '</td>';
                $content .= '<td>' . $student->getClass() . '</td>';
                $content .= '<td>' . $student->getType() . '</td>';
                $content .= '<td>' . $student->getCreditHours() . '</td>';
                $content .= '<td>' . $student->getDob() . '</td>';
                $content .= '</tr>';
            }
        }

        $content .= '</table';

        return $content;
    }

    public static function run_move_in_times_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $term = Term::getSelectedTerm();

        $halls = HMS_Residence_Hall::get_halls($term);

        $tpl = new PHPWS_Template('hms');
        if(!$tpl->setFile('admin/reports/move_in_times.tpl')){
            return 'Template error...';
        }

        foreach($halls as $hall){

            $floors = $hall->get_floors();

            foreach($floors as $floor){
                $tpl->setCurrentBlock('floor_repeat');

                if(is_null($floor->f_movein_time_id)){
                    $f_time = 'None';
                }else{
                    $f_movein  = new HMS_Movein_Time($floor->f_movein_time_id);
                    $f_time    = $f_movein->get_formatted_begin_end();
                }

                if(is_null($floor->t_movein_time_id)){
                    $t_time = 'None';
                }else{
                    $t_movein  = new HMS_Movein_Time($floor->t_movein_time_id);
                    $t_time    = $t_movein->get_formatted_begin_end();
                }

                if(is_null($floor->rt_movein_time_id)){
                    $rt_time = 'None';
                }else{
                    $rt_movein  = new HMS_Movein_Time($floor->rt_movein_time_id);
                    $rt_time    = $rt_movein->get_formatted_begin_end();
                }

                $tpl->setData(array('FLOOR_NUM' => $floor->floor_number,
                                    'F_TIME'    => $f_time,
                                    'T_TIME'    => $t_time,
                                    'RT_TIME'   => $rt_time));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('hall_repeat');
            $tpl->setData(array('HALL_NAME' => $hall->hall_name));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();

    }

    /*
     * TODO: finish this
     */
    public static function run_hall_occupancy_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $halls = HMS_Residence_Hall::get_halls(Term::getSelectedTerm());

        $tpl = new PHPWS_Template('hms');
        if(!$tpl->setFile('admin/reports/hall_occupancy.tpl')){
            return 'Template error....';
        }

        $total_beds = 0; // accumulator for counting beds
        $vacant_beds = 0;

        foreach($halls as $hall){
            // skip offline halls
            if($hall->is_online == 0){
                $tpl->setCurrentBlock('hall_repeat');
                $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - Offline'));
                $tpl->parseCurrentBlock();
                continue;
            }

            $beds_by_hall = 0;
            $vacant_beds_by_hall = 0;

            $floors = $hall->get_floors();

            if($floors == NULL){
                continue;
            }

            foreach($floors as $floor){
                // Skip offline floors
                if($floor->is_online == 0){
                    $tpl->setCurrentBlock('floor_repeat');
                    $tpl->setData(array('FLOOR_NUM' => $floor->floor_number . ' - Offline'));
                    $tpl->parseCurrentBlock();
                    continue;
                }

                $vacant_beds_by_floor = 0;
                $total_beds_by_floor  = 0;

                $rooms = $floor->get_rooms();

                if($rooms == NULL){
                    continue;
                }

                foreach($rooms as $room){
                    if(!$room->is_online == 1){
                        continue;
                    }
                    $beds = $room->get_beds();

                    if($beds == NULL){
                        continue;
                    }

                    foreach($beds as $bed){
                        $beds_by_hall++;
                        $total_beds_by_floor++;
                        $total_beds++;
                        if($bed->has_vacancy()){
                            $vacant_beds++;
                            $vacant_beds_by_hall++;
                            $vacant_beds_by_floor++;
                        }

                    }
                }

                $content = $vacant_beds_by_floor ."/". $total_beds_by_floor;
                $tpl->setCurrentBlock('floor_repeat');
                $tpl->setData(array('FLOOR_NUM'  => $floor->floor_number,
                                    'FLOOR_BEDS' => $content));
                $tpl->parseCurrentblock();
            }

            $tpl->setCurrentBlock('hall_repeat');
            $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - ' . $vacant_beds_by_hall . ' of ' . $beds_by_hall . ' beds are vacant.'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setData(array('BED_COUNT' => $vacant_beds, 'TOTAL_BEDS' => $total_beds));

        return $tpl->get();
    }

    public static function run_unassigned_beds_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $halls = HMS_Residence_Hall::get_halls(Term::getSelectedTerm());

        $tpl = &new PHPWS_Template('hms');
        if(!$tpl->setFile('admin/reports/unassigned_beds.tpl')){
            return 'Template error....';
        }

        $vacant_beds = 0; // accumulator for counting empty beds

        foreach($halls as $hall){
            // skip offline halls
            if($hall->is_online == 0){
                $tpl->setCurrentBlock('hall_repeat');
                $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - Offline'));
                $tpl->parseCurrentBlock();
                continue;
            }

            // Skip full halls
            if(!$hall->has_vacancy()){
                $tpl->setCurrentBlock('hall_repeat');
                $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - No vacancy'));
                $tpl->parseCurrentBlock();
                continue;
            }

            // skip Mountaineer Apts
            if($hall->hall_name == 'Mountaineer Apartments'){
                continue;
            }

            $vacant_beds_by_hall = 0;

            $floors = $hall->get_floors();

            foreach($floors as $floor){
                // Skip offline floors
                if($floor->is_online == 0){
                    $tpl->setCurrentBlock('floor_repeat');
                    $tpl->setData(array('FLOOR_NUM' => $floor->floor_number . ' - Offline'));
                    $tpl->parseCurrentBlock();
                    continue;
                }

                if(!$floor->has_vacancy()){
                    $tpl->setCurrentBlock('floor_repeat');
                    $tpl->setData(array('FLOOR_NUM' => $floor->floor_number . ' - No vacancy'));
                    $tpl->parseCurrentBlock();
                    continue;
                }

                $rooms = $floor->get_rooms();

                foreach($rooms as $room){
                    if(!$room->has_vacancy()){
                        //$tpl->setCurrentBlock('room_repeat');
                        //$tpl->setData(array('ROOM_NUM' => $room->room_number . ' - No vacancy'));
                        //$tpl->parseCurrentBlock();
                        continue;
                    }

                    // Skip offline, overflow, and reserved rooms
                    if($room->is_online == 0 || $room->is_overflow == 1 || $room->is_reserved == 1){
                        continue;
                    }

                    $beds = $room->get_beds();

                    foreach($beds as $bed){
                        if(!$bed->has_vacancy()){
                            continue;
                        }

                        $content = $bed->bed_letter;
                        if($bed->ra_bed == 1){
                            $content .= ' (RA)';
                        }

                        //$content .= ' ' . $bed->get_assigned_to_link(TRUE);
                        $content .= ' ' . '&lt;unassigned&gt;';

                        $tpl->setCurrentBlock('bed_repeat');
                        $tpl->setData(array('BED_NUM' => $content));
                        $tpl->parseCurrentBlock();
                        $vacant_beds++;
                        $vacant_beds_by_hall++;
                    }

                    $content = $room->room_number;
                    if($room->ra_room == 1){
                        $content .= ' (RA)';
                    }
                    if($room->private_room == 1){
                        $content .= ' (private)';
                    }
                    if($room->is_overflow == 1){
                        $content .= ' (overflow)';
                    }
                    if($room->is_medical == 1){
                        $content .= ' (medical)';
                    }
                    if($room->is_reserved == 1){
                        $content .= ' (reserved)';
                    }
                    if($room->gender_type == MALE){
                        $content .= ' (male)';
                    }else if($room->gender_type == FEMALE){
                        $content .= ' (female)';
                    }else{
                        $content .= ' (unknown gender)';
                    }

                    $tpl->setCurrentBlock('room_repeat');
                    $tpl->setData(array('ROOM_NUM' => $content));
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock('floor_repeat');
                $tpl->setData(array('FLOOR_NUM' => $floor->floor_number));
                $tpl->parseCurrentblock();
            }

            $tpl->setCurrentBlock('hall_repeat');
            $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - ' . $vacant_beds_by_hall . ' vacant beds'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setData(array('BED_COUNT' => $vacant_beds));

        return $tpl->get();
    }

    public function applied_student_data_export()
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $term = Term::getSelectedTerm();
        $filename = "hms_applications-$term-" . date("Y-m-d") . '.csv';
        $output = '';

        $apps = HousingApplication::getAllApplications(NULL, NULL, $term);

        $output .= "user name, banner id, first name, middle name, last name, student type, assignment, address 1, address 2, address 3, city, state, zip\n";

        foreach($apps as $application){

            $username   = $application->getUsername();
            $bannerId   = $application->getBannerId();
            $type       = $application->getStudentType();

            $assignment = HMS_Assignment::get_assignment($application->getUsername(), $term);

            if(!is_null($assignment)){
                $room = $assignment->where_am_i();
            }else{
                $room = '';
            }

            $student = StudentFactory::getStudentByUsername($username, $term);

            $first  = $student->getFirstName($username);
            $middle = $student->getMiddleName($username);
            $last   = $student->getLastName($username);

            $address = $student->getAddress(NULL);

            $output .= "$username,$bannerId,$first,$middle,$last,$type,$room,$address->line1,$address->line2,$address->line3,$address->city,$address->state,$address->zip\n";
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $output;
        exit;

    }

    public function assigned_student_data_export()
    {
        $term = Term::getSelectedTerm();

        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('hms_assignment.*');
        $db->addColumn('hms_residence_hall.*');
        $db->addColumn('hms_room.*');

        $db->addWhere('hms_assignment.term', $term);

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            return $results;
        }

        $filename = "hms_assignments-$term-" . date("Y-m-d") . '.csv';

        $output = "user name, banner id, first name, middle name, last name, student type, assignment, address 1, address 2, address 3, city, state, zip\n";

        foreach($results as $row){
            $student = StudentFactory::getStudentByUsername($row['asu_username'], $term);

            $bannerId = $student->getBannerId();

            if(!isset($bannerId) || is_null($bannerId) || empty($bannerId)){
                continue;
            }

            $first      = $student->getFirstName();
            $middle     = $student->getMiddleName();
            $last       = $student->getLastName();
            $type       = $student->studentType();

            $room = $row['hall_name'] . ' ' . $row['room_number'];

            $address = $student->getAddress('NULL'); 

            if(!$address || !isset($address) || is_null($address)){
                $line1 = "";
                $line2 = "";
                $line3 = "";
                $city  = "";
                $state = "";
                $zip   = "";
            } else {
                $line1 = $address->line1;
                $line2 = $address->line2;
                $line3 = $address->line3;
                $city  = $address->city;
                $state = $address->state;
                $zip   = $address->zip;
            }

            $output .= "$username,$bannerId,$first,$middle,$last,$type,$room,$line1,$line2,$line3,$city,$state,$zip\n";
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $output;
        exit;
    }

    public static function unassigned_applicants_report()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');

        $term = Term::getSelectedTerm();
        $sem = Term::getTermSem($term);

        $tpl = array();

        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        switch($sem){
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $pager = new DBPager('hms_new_application', 'SummerApplication');
                $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_summer_application', 'id', 'id');
                $pager->joinResult('id', 'hms_summer_application', 'id', 'room_type');
                $pager->addSortHeader('room_type', 'Room Type');
                break;
            case TERM_FALL:
                $pager = new DBPager('hms_new_application', 'FallApplication');
                $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_fall_application', 'id', 'id');
                $pager->joinResult('id', 'hms_fall_application', 'id', 'lifestyle_option');
                $pager->joinResult('id', 'hms_fall_application', 'id', 'preferred_bedtime');
                //$pager->joinResult('id', 'hms_fall_application', 'id', 'room_condition');
                $pager->addSortHeader('lifestyle_option','Lifestyle');
                $pager->addSortHeader('preferred_bedtime','Preferred Bedtime');
                $pager->addSortHeader('room_condition','Room Condition');
                break;
            case TERM_SPRING:
                $pager = new DBPager('hms_new_application', 'SpringApplication');
                $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_fall_application', 'id', 'id');
                $pager->joinResult('id', 'hms_spring_application', 'id', 'lifestyle_option');
                $pager->joinResult('id', 'hms_spring_application', 'id', 'preferred_bedtime');
                $pager->joinResult('id', 'hms_spring_application', 'id', 'room_condition');
                $pager->addSortHeader('lifestyle_option','Lifestyle');
                $pager->addSortHeader('preferred_bedtime','Preferred Bedtime');
                $pager->addSortHeader('room_condition','Room Condition');
                break;
            default:
                // error
                return "Invalid term specified.";
        }

        $pager->addSortHeader('banner_id', 'Banner ID');
        $pager->addSortHeader('username', 'User Name');
        $pager->addSortHeader('gender', 'Gender');
        $pager->addSortHeader('application_term', 'Application Term');
        $pager->addSortHeader('student_type', 'Student Type');

        $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_assignment', 'username', 'asu_username AND hms_new_application.term = hms_assignment.term');
        $pager->db->addWhere('hms_assignment.asu_username', 'NULL');
        $pager->db->addWhere('hms_new_application.term', $term);
        $pager->db->addWhere('hms_new_application.withdrawn', 0);

        $pager->setModule('hms');
        $pager->setTemplate('admin/reports/unassigned_applicants.tpl');
        $pager->setEmptyMessage('No students found.');
        $pager->addToggle('class="bgcolor1"');
        $pager->addToggle('class="bgcolor2"');
        $pager->addPageTags($tpl);
        $pager->addRowTags('unassignedApplicantsRows');
        $pager->setReportRow('unassignedApplicantsCSV');

        return $pager->get();
    }

    public static function special_needs()
    {
        $content = "<h2>Special Needs</h2>\n";

        $term = Term::getSelectedTerm();

        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('username');
        $db->addWhere('term', $term);
        $db->addWhere('physical_disability', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Physical: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['username']);
        }
        $content .= "</ul>\n";

        $db->resetWhere();
        $db->addWhere('term', $term);
        $db->addWhere('psych_disability', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Psychological: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['username']);
        }
        $content .= "</ul>\n";

        $db->resetWhere();
        $db->addWhere('term', $term);
        $db->addWhere('medical_need', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Medical: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['username']);
        }
        $content .= "</ul>\n";

        $db->resetWhere();
        $db->addWhere('term', $student);
        $db->addWhere('gender_need', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Gender: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['username']);
        }
        $content .= "</ul>\n";

        return $content;
    }

    public static function show_student($username) {
        $student = StudentFactory::getStudentByUsername($username, Term::getSelectedTerm());
        $name = $student->getFullName();
        $bid = $student->getBannerId();

        return "<li>$bid: $name</li>";
    }

    public static function assignment_roster_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        require_once(PHPWS_SOURCE_DIR . '/mod/hms/fpdf.php');
        define('HEIGHT', 0.1875);

        $term = Term::getSelectedTerm();

        $halls = HMS_Residence_Hall::get_halls($term);

        if(!isset($halls)){
            return 'No Online Halls found for the currently selected term.';
        }

        $pdf = new FPDF('L','mm','A4');

        foreach($halls as $hall){
            $hall->loadFloors();
            $floors = $hall->_floors;

            foreach($floors as $floor){
                $rooms = $floor->get_rooms();
                if(!is_array($rooms))
                continue;

                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(80);
                $pdf->Cell(120, 5, 'Appalachian State University', 0, 2, 'C');
                $pdf->Cell(120, 5, 'Residence Life Occupancy Report', 0, 2, 'C');
                $pdf->Ln(10);
                $pdf->Cell(30, 5, ''.$hall->hall_name, 0);
                $pdf->Ln(20);
                $pdf->Cell(20, 5, 'Floor', 1);
                $pdf->Cell(20, 5, 'Room', 1);
                $pdf->Cell(30, 5, 'Banner ID', 1);
                $pdf->Cell(88, 5, 'Name', 1);
                $pdf->Cell(40, 5, 'Username', 1);
                $pdf->Cell(20, 5, 'Year', 1);
                $pdf->Cell(30, 5, 'Birthdate', 1);
                $pdf->Cell(22, 5, 'Gender', 1);
                $pdf->Ln(5);

                foreach($rooms as $room){
                    //get the beds in the floor
                    $room->loadBeds();

                    //test($room->_beds);

                    if(is_null($room->_beds) || !isset($room->_beds)){
                        continue;
                    }

                    foreach($room->_beds as $bed){
                        //output the floor and room number
                        $pdf->Cell(20, 5, $floor->floor_number, 1);
                        //concatenate the room number with the bedroom label to save space
                        $pdf->Cell(20, 5, $room->room_number .' '. $bed->bedroom_label, 1);
                        //if the bed has an assignment
                        if($bed->loadAssignment()){
                            if(!is_null($bed->_curr_assignment)){
                                $student = StudentFactory::getStudentByUsername($bed->_curr_assignment->asu_username, $term);
                                $pdf->Cell(30, 5, ''.$student->getBannerId(), 1);
                                $pdf->Cell(88, 5, ''.$student->getFullName(),1);
                                $pdf->Cell(40, 5, ''.$bed->_curr_assignment->asu_username, 1);
                                $pdf->Cell(20, 5, ''.$student->getClass(), 1);
                                $pdf->Cell(30, 5, ''.$student->getDob(), 1);
                                $pdf->Cell(22, 5, ''.$student->getGender(), 1);
                            } else {
                                $pdf->Cell(30, 5, 'N/A', 1);
                                $pdf->Cell(88, 5, 'No Assignment', 1);
                                $pdf->Cell(40, 5, '', 1);
                                $pdf->Cell(20, 5, '', 1);
                                $pdf->Cell(30, 5, '', 1);
                                $pdf->Cell(22, 5, '', 1);
                            }
                        }
                        $pdf->Ln(5);
                    }
                }
            }
        }

        $pdf->Output();
        exit();
    }

    public static function roster_report()
    {
        $output = "Hall,Floor,Room,First Name,Last Name,Banner ID,Cell Phone Number, Email Address\n";

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $db = new PHPWS_DB('hms_assignment');

        $db->addColumn('hms_assignment.asu_username');
        $db->addColumn('hms_new_application.cell_phone');
        $db->addColumn('hms_room.room_number');
        $db->addColumn('hms_floor.floor_number');
        $db->addColumn('hms_residence_hall.hall_name');

        $db->addJoin('LEFT', 'hms_assignment', 'hms_new_application', 'asu_username', 'username');
        $db->addJoin('LEFT', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $db->addJoin('LEFT', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('term', Term::getSelectedTerm());

        $db->addOrder('hms_residence_hall.id ASC');

        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            Layout::add('Error running the Roster Report, please contact ESS');
        }

        foreach($results as $result){
            $student = StudentFactory::getStudentByUsername($result['asu_username'], Term::getSelectedTerm());

            $output .= "{$result['hall_name']},{$result['floor_number']},{$result['room_number']},{$student->getLastName()},{$student->getFirstName()},{$student->getBannerId()},{$result['cell_phone']},{$result['asu_username']}@appstate.edu\n";
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="Roster_Report'.Term::getCurrentTerm().'.csv"');
        echo $output;
        exit;
    }

    public function over_twenty_five_report(){
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $tpl = array();
        
        $term = Term::getCurrentTerm();

        $db = new PHPWS_DB('hms_new_application');
        $db->addWhere('term', $term);
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            Layout::add('<div color="font-color: red;">An error occured running the "Over-25" report, please contact ESS if this problem persists.</div>');
            return false;
        }

        foreach($results as $result){
            $student = StudentFactory::getStudentByUsername($result['username'], $term);

            if(strtotime("-25 years") > strtotime($student->getDob())){
                $tpl['students'][] = array('NAME'     => $student->getFullNameProfileLink(),
                                           'ASU_USERNAME'  => $result['username'],
                                           'DATE_OF_BIRTH' => $student->getDob(),
                                           'BANNER_ID'     => $student->getBannerId());
            }
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/over_twenty_five_report.tpl');
    }

    public static function run_no_banner_data_report()
    {
        PHPWS_Core::initModClass('hms', 'SOAP.php');
         
        $soap = SOAP::getInstance();
         
        $term = Term::getSelectedTerm();

        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('username');
        $db->addOrder('username');
        $results = $db->select();
        if(PHPWS_Error::logIfError($results)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($results->toString());
        }

        $content = "<h2>Students With No Banner Data</h2><br />";

        foreach($results as $row) {
            if(!$soap->isValidStudent($row['username'])) {
                $content .= $row['username'] . '<br />';
            }
        }

        return $content;
    }
}
?>
