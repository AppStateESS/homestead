<?php

class HMS_Reports{

    /**
     * Returns an array listing all the possible reports
     */
    function get_reports()
    {
        $reports = array(
                        'housing_apps'  => 'Housing Applications Received',
                        'housing_asss'  => 'Assignment Demographics',
                        'assigned_f'    => 'Assigned Type F Students',
                        'special_needs' => 'Special Needs Applicants',
                        'unassd_apps'   => 'Unassigned Applicants',
                        'movein_times'  => 'Move-in Times',
                        'unassd_beds'   => 'Currently Unassigned Beds',
                        'no_ban_data'  => 'Students Without Banner Data'
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

    /**
     * Shows the user a page where he/she can select which report to run from a drop down list
     */
    function display_reports()
    {
        $tpl = array();
        if(!Current_User::allow('hms', 'reports')){
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $vars['type'] = 'reports';
        $vars['op']   = 'run_report';

        $reports = HMS_Reports::get_reports();
        $tpl['REPORTS'] = array();
        foreach($reports as $code=>$name) {
            $vars['report'] = $code;
            
            $js = array();
            $js['width']       = 800;
            $js['height']      = 600;
            $js['label']       = $name;
            $js['title']       = "Run '$name' Report";
            $js['address']     = PHPWS_Text::linkAddress('hms', $vars, true);
            $js['window_name'] = 'hms_report';
            
            $tpl['REPORTS'][]['REPORT_LINK'] =  javascript('open_window', $js);
        }

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_reports.tpl');
        return $final;
    }
    
    function run_report()
	{
        if(!Current_User::allow('hms', 'reports')){
            $tpl = array();
            PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        $content = '<p><a href="javascript:window.print()">Print Report</a></p>';
        
        // Go ahead an initalize the Term class, since it's going to be needed by all reports
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        
	    switch($_REQUEST['report'])
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
                break;
            case 'movein_times':
               return HMS_Reports::run_move_in_times_report();
               break;
            case 'unassd_beds':
                return HMS_Reports::run_unassigned_beds_report();
                break;
            case 'no_ban_data':
                return HMS_Reports::run_no_banner_data_report();
                break;
            /*
            case 'unassd_rooms':
                return HMS_Reports::run_unassigned_rooms_report();
                break;
            case 'reqd_roommate':
                return HMS_Reports::run_unconfirmed_roommates_report();
                break;
            case 'assd_alpha':
                return HMS_Reports::run_assigned_students_report();
                break;
            case 'special':
                return HMS_Reports::run_special_circumstances_report();
                break;
            case 'hall_structs':
                return HMS_Reports::display_hall_structures();
                break;
            case 'no_deposit':
                return HMS_Reports::run_no_deposit_report();
                break;
            case 'bad_type':
                return HMS_Reports::run_bad_type_report();
                break;
            case 'gender':
                return HMS_Reports::run_gender_report();
                break;
                */
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
    function run_assignment_demographics_report()
	{
	    PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        # Start the timer
        $start_time = microtime();

        $building = array(); // Define an array to hold each building's summary
	   
        # Get a list of hall ID's and names 
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addWhere('term', HMS_Term::get_selected_term());
        $db->addWhere('is_online', 1); // only get halls that are online
        $db->addOrder('hall_name', 'asc');
        $result = $db->select();

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
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
         
            if(PEAR::isError($assignments)) {
                PHPWS_Error::log($assignments);   
                return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
            }

            # Initalize this hall's summary
            foreach(array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, TYPE_READMIT) as $init_type){
                foreach(array(CLASS_FRESHMEN, CLASS_SOPHOMORE, CLASS_JUNIOR, CLASS_SENIOR) as $init_class){
                    foreach(array(MALE, FEMALE) as $init_gender){
                        $building[$hall_row['hall_name']][$init_type][$init_class][$init_gender] = 0;
                    }
                }
            }

            # For each assignment we found in this hall...
            foreach($assignments as $assignment) {
                # Get the gender (in numeric form) of the student for this assignment
                $gender = HMS_SOAP::get_gender($assignment['asu_username'], TRUE);
                
                # Check the gender for bad data
                if(!isset($gender) || $gender === NULL || ($gender != MALE && $gender != FEMALE)) {
                    $problems[] = $assignment['asu_username'] .': Gender is unrecognized ('. $gender .')';
                }
                    
                # Get the class of the student for this assignment
                $class = HMS_SOAP::get_student_class($assignment['asu_username'], HMS_Term::get_selected_term());

                # Check the class for bad data
                if(!isset($class) || $class === NULL ||
                    ($class != CLASS_FRESHMEN && $class != CLASS_SOPHOMORE && 
                     $class != CLASS_JUNIOR && $class != CLASS_SENIOR)) {
                    $problems[] = $assignment['asu_username'] . ': Class is unrecognized ('. $class .')';
                }

                # Get the type of the student for this assignment
                $type = HMS_SOAP::get_student_type($assignment['asu_username'], HMS_Term::get_selected_term());

                # Check the type for bad data
                if(!isset($type) || $type === NULL ||
                   ($type != TYPE_FRESHMEN && $type != TYPE_TRANSFER && $type != TYPE_CONTINUING && $type != TYPE_READMIT)) {
                    $problems[] = $assignment['asu_username'] . ': Type is unrecognized ('. $type .')';
                }

                $credit_hours = HMS_SOAP::get_credit_hours($assignment['asu_username']);
                
                # Check for a freshmen type, but a mis-matched class
                if(($type == TYPE_FRESHMEN && $class != CLASS_FRESHMEN)){
                    $problems[] = $assignment['asu_username'] . ": Type is $type, class is $class, credit hours is $credit_hours";
                }

                # Check for a mis-matched type/class/hours situation
                if( $type == TYPE_CONTINUING && $class == CLASS_FRESHMEN && $credit_hours == 0){
                    $problems[] = $assignment['asu_username'] . ": Type is $type, class is $class, credit hours is $credit_hours";
                }

                $t = $type;
                $g = $gender;
                $c = $class;

                $building[$hall_row['hall_name']][$t][$c][$g]++;
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

        $content = '';

        # Print out the header
        $term = HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $content .= '<br /><br />';
        $content .= "$term - Housing assignments by building, student type, class, and gender";
        $content .= '<br /><br />';
        
        # Show any problems that occured
        if(isset($problems) && count($problems) > 0) {
            $content .= '<font color="red"><b>Some problems were found while retrieving data from Banner:</b></font><br />';
            foreach($problems as $problem) {
                $content .= $problem . '<br />';
            }
            $content .= '<br /><br />';
        }

        foreach($building as $hall) {
            ksort($hall);
            $name = key($building);
            $content .= '<table border="1">';
            $content .= '<tr><th colspan="17"><h2 style="text-align: center">' . $name . '</h2></th></tr>';
            $content .= '<tr>';
            $content .= '<td rowspan="2"></td>';
            $content .= '<th colspan="4">Freshmen (F)</th>';
            $content .= '<th colspan="4">Continuing (C)</th>';
            $content .= '<th colspan="4">Transfer (T)</th>';
            $content .= '<th colspan="4">Readmit (Z)</th>';
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
            $content .= '</tr></table><br /><br />';

            $total['F']['FR']['M']  += $building[$name]['F']['FR'][MALE];
            $total['F']['SO']['M']  += $building[$name]['F']['SO'][MALE];
            $total['F']['JR']['M']  += $building[$name]['F']['JR'][MALE];
            $total['F']['SR']['M']  += $building[$name]['F']['SR'][MALE];
            $total['C']['FR']['M']  += $building[$name]['C']['FR'][MALE];
            $total['C']['SO']['M']  += $building[$name]['C']['SO'][MALE];
            $total['C']['JR']['M']  += $building[$name]['C']['JR'][MALE];
            $total['C']['SR']['M']  += $building[$name]['C']['SR'][MALE];
            $total['T']['FR']['M']  += $building[$name]['T']['FR'][MALE];
            $total['T']['SO']['M']  += $building[$name]['T']['SO'][MALE];
            $total['T']['JR']['M']  += $building[$name]['T']['JR'][MALE];
            $total['T']['SR']['M']  += $building[$name]['T']['SR'][MALE];
            $total['Z']['FR']['M']  += $building[$name]['Z']['FR'][MALE];
            $total['Z']['SO']['M']  += $building[$name]['Z']['SO'][MALE];
            $total['Z']['JR']['M']  += $building[$name]['Z']['JR'][MALE];
            $total['Z']['SR']['M']  += $building[$name]['Z']['SR'][MALE];
            
            $total['F']['FR']['F']  += $building[$name]['F']['FR'][FEMALE];
            $total['F']['SO']['F']  += $building[$name]['F']['SO'][FEMALE];
            $total['F']['JR']['F']  += $building[$name]['F']['JR'][FEMALE];
            $total['F']['SR']['F']  += $building[$name]['F']['SR'][FEMALE];
            $total['C']['FR']['F']  += $building[$name]['C']['FR'][FEMALE];
            $total['C']['SO']['F']  += $building[$name]['C']['SO'][FEMALE];
            $total['C']['JR']['F']  += $building[$name]['C']['JR'][FEMALE];
            $total['C']['SR']['F']  += $building[$name]['C']['SR'][FEMALE];
            $total['T']['FR']['F']  += $building[$name]['T']['FR'][FEMALE];
            $total['T']['SO']['F']  += $building[$name]['T']['SO'][FEMALE];
            $total['T']['JR']['F']  += $building[$name]['T']['JR'][FEMALE];
            $total['T']['SR']['F']  += $building[$name]['T']['SR'][FEMALE];
            $total['Z']['FR']['F']  += $building[$name]['Z']['FR'][FEMALE];
            $total['Z']['SO']['F']  += $building[$name]['Z']['SO'][FEMALE];
            $total['Z']['JR']['F']  += $building[$name]['Z']['JR'][FEMALE];
            $total['Z']['SR']['F']  += $building[$name]['Z']['SR'][FEMALE];
            
            next($building);
        }
        $content .= '======================================================';

        $content .= '<table border="1">';
        $content .= '<tr><th colspan="17" style="text-align: center"><h2>TOTALS</h2></th></tr>';
        $content .= '<tr>';
        $content .= '<td rowspan="2"></td>';
        $content .= '<th colspan="4">Freshmen (F)</th>';
        $content .= '<th colspan="4">Continuing (C)</th>';
        $content .= '<th colspan="4">Transfer (T)</th>';
        $content .= '<th colspan="4">Readmit (Z)</th>';
        $content .= '</tr><tr>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '</tr><tr>';
        $content .= '<th>Male</th>';
        $content .= '<td>' . $total['F']['FR']['M']   . '</td>';
        $content .= '<td>' . $total['F']['SO']['M']   . '</td>';
        $content .= '<td>' . $total['F']['JR']['M']   . '</td>';
        $content .= '<td>' . $total['F']['SR']['M']   . '</td>';
        $content .= '<td>' . $total['C']['FR']['M']   . '</td>';
        $content .= '<td>' . $total['C']['SO']['M']   . '</td>';
        $content .= '<td>' . $total['C']['JR']['M']   . '</td>';
        $content .= '<td>' . $total['C']['SR']['M']   . '</td>';
        $content .= '<td>' . $total['T']['FR']['M']   . '</td>';
        $content .= '<td>' . $total['T']['SO']['M']   . '</td>';
        $content .= '<td>' . $total['T']['JR']['M']   . '</td>';
        $content .= '<td>' . $total['T']['SR']['M']   . '</td>';
        $content .= '<td>' . $total['Z']['FR']['M']   . '</td>';
        $content .= '<td>' . $total['Z']['SO']['M']   . '</td>';
        $content .= '<td>' . $total['Z']['JR']['M']   . '</td>';
        $content .= '<td>' . $total['Z']['SR']['M']   . '</td>';
        $content .= '</tr><tr>';
        $content .= '<th>Female</th>';
        $content .= '<td>' . $total['F']['FR']['F']   . '</td>';
        $content .= '<td>' . $total['F']['SO']['F']   . '</td>';
        $content .= '<td>' . $total['F']['JR']['F']   . '</td>';
        $content .= '<td>' . $total['F']['SR']['F']   . '</td>';
        $content .= '<td>' . $total['C']['FR']['F']   . '</td>';
        $content .= '<td>' . $total['C']['SO']['F']   . '</td>';
        $content .= '<td>' . $total['C']['JR']['F']   . '</td>';
        $content .= '<td>' . $total['C']['SR']['F']   . '</td>';
        $content .= '<td>' . $total['T']['FR']['F']   . '</td>';
        $content .= '<td>' . $total['T']['SO']['F']   . '</td>';
        $content .= '<td>' . $total['T']['JR']['F']   . '</td>';
        $content .= '<td>' . $total['T']['SR']['F']   . '</td>';
        $content .= '<td>' . $total['Z']['FR']['F']   . '</td>';
        $content .= '<td>' . $total['Z']['SO']['F']   . '</td>';
        $content .= '<td>' . $total['Z']['JR']['F']   . '</td>';
        $content .= '<td>' . $total['Z']['SR']['F']   . '</td>';
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

    function run_applicant_demographics_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        # Note the start time
        $start_time = microtime();

        $db = &new PHPWS_DB('hms_application');
        $db->addColumn('asu_username');
        $db->addWhere('deleted', '0');
        $db->addWhere('term', HMS_Term::get_selected_term());
        $db->addOrder('asu_username', 'ASC');
        $results = $db->select();

        if(PEAR::isError($results)) {
            PHPWS_Error::log($results);
            return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
        }

        # Initalize the array for totals
        foreach(array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING) as $init_type){
            foreach(array(CLASS_FRESHMEN, CLASS_SOPHOMORE, CLASS_JUNIOR, CLASS_SENIOR) as $init_class){
                foreach(array(MALE, FEMALE) as $init_gender){
                    $application_totals[$init_type][$init_class][$init_gender] = 0;
                }
            }
        }

        $application_totals['bad_data'] = 0;
        
        $content = '';
        foreach($results as $line) {
            $gender = HMS_SOAP::get_gender($line['asu_username'], TRUE);
            $class  = HMS_SOAP::get_student_class($line['asu_username'], HMS_Term::get_selected_term());
            $type   = HMS_SOAP::get_student_type($line['asu_username'], HMS_Term::get_selected_term());

            if($gender === NULL || $class === NULL || $type === NULL) {
                    $application_totals['bad_data']++;
                    continue;
            }

            $application_totals[$type][$class][$gender]++;
        }

        $term = HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);

        $content .= '<br /><br />';
        $content .= "$term - Housing Applications received by class and gender:<br /><br />";
        $content .= '<table border="1">';
        $content .= '<tr><th colspan="11" style="text-align: center"><h2>TOTALS</h2></th></tr>';
        $content .= '<tr>';
        $content .= '<td rowspan="2"></td>';
        $content .= '<th colspan="1">Freshmen (F)</th>';
        $content .= '<th colspan="4">Continuing (C)</th>';
        $content .= '<th colspan="4">Transfer (T)</th>';
        $content .= '</tr><tr>';
        $content .= '<th>FR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '</tr><tr>';
        $content .= '<th>Male</th>';
        $content .= '<td>' . $application_totals['F']['FR'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['FR'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['SO'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['JR'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['SR'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['FR'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['SO'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['JR'][MALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['SR'][MALE]   . '</td>';
        $content .= '</tr><tr>';
        $content .= '<th>Female</th>';
        $content .= '<td>' . $application_totals['F']['FR'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['FR'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['SO'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['JR'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['C']['SR'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['FR'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['SO'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['JR'][FEMALE]   . '</td>';
        $content .= '<td>' . $application_totals['T']['SR'][FEMALE]   . '</td>';
        $content .= '</tr></table><br /><br />';
        $content .=  "<br /> ";
 
        $content .= "No Class or Gender Data Available<br />";
        $content .= "Total: " . $application_totals['bad_data'] . "<br />";
        $content .= "<br />";
        $content .= "<br />";

        $elapsed_time = microtime() - $start_time;

        $content .= "Elapsed time: $elapsed_time seconds <br /><br />";
    
        return $content;
    }

    /**
     * Finds and lists all currently assigned students who have a banner type of F
     */
    function run_assigned_type_f(){

        $db = &new PHPWS_DB('hms_assignment');
        $db->addWhere('term', HMS_Term::get_selected_term());

        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            return "Database error!\n";
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
            $user = $assignment['asu_username'];
            if(HMS_SOAP::get_student_type($user, HMS_Term::get_selected_term()) == TYPE_FRESHMEN){
                $content .= '<tr>';
                $content .= '<td>' . $user . '</td>';
                $content .= '<td>' . HMS_SOAP::get_banner_id($user) . '</td>';
                $content .= '<td>' . HMS_SOAP::get_application_term($user) . '</td>';
                $content .= '<td>' . HMS_SOAP::get_student_class($user, HMS_Term::get_selected_term()) . '</td>';
                $content .= '<td>' . HMS_SOAP::get_student_type($user, HMS_Term::get_selected_term()) . '</td>';
                $content .= '<td>' . HMS_SOAP::get_credit_hours($user) . '</td>';
                $content .= '<td>' . HMS_SOAP::get_dob($user) . '</td>';
                $content .= '</tr>';
            }
        }

        $content .= '</table';

        return $content;
    }

    function run_move_in_times_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $halls = HMS_Residence_Hall::get_halls(HMS_Term::get_selected_term());

        $tpl = &new PHPWS_Template('hms');
        if(!$tpl->setFile('admin/reports/move_in_times.tpl')){
            return 'Template error...';
        }

        foreach($halls as $hall){
            
            $floors = $hall->get_floors();

            foreach($floors as $floor){
                $tpl->setCurrentBlock('floor_repeat');
                
                if(is_null($floor->ft_movein_time_id)){
                    $ft_time = 'None';
                }else{
                    $ft_movein  = &new HMS_Movein_Time($floor->ft_movein_time_id);
                    $ft_time    = $ft_movein->get_formatted_begin_end();
                }

                if(is_null($floor->rt_movein_time_id)){
                    $rt_time = 'None';
                }else{
                    $rt_movein  = &new HMS_Movein_Time($floor->rt_movein_time_id);
                    $rt_time    = $rt_movein->get_formatted_begin_end();
                }
                
                $tpl->setData(array('FLOOR_NUM' => $floor->floor_number,
                                    'FT_TIME'   => $ft_time,
                                    'RT_TIME'   => $rt_time));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('hall_repeat');
            $tpl->setData(array('HALL_NAME' => $hall->hall_name));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();

    }
    /* TODO: finish
    function run_hall_occupancy_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $halls = HMS_Residence_Hall::get_halls(HMS_Term::get_selected_term());

        $tpl = &new PHPWS_Template('hms');
        if(!$tpl->setFile('admin/reports/unassigned_beds.tpl')){
            return 'Template error....';
        }

        $beds = 0; // accumulator for counting beds

        foreach($halls as $hall){
            // skip offline halls
            if($hall->is_online == 0){
                $tpl->setCurrentBlock('hall_repeat');
                $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - Offline'));
                $tpl->parseCurrentBlock();
                continue;
            }
           
            $beds_by_hall = 0;

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

                    $beds = $room->get_beds();

                    foreach($beds as $bed){
                        if(!$bed->has_vacancy()){
                            continue;
                        }

                        $content = $bed->bed_letter;
                        if($bed->ra_bed == 1){
                            $content .= ' (RA)';
                        }
                        
                        $content .= ' ' . $bed->get_assigned_to_link();

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
                    if($room->is_lobby == 1){
                        $content .= ' (lobby)';
                    }
                    if($room->is_medical == 1){
                        $content .= ' (medical)';
                    }
                    if($room->is_reserved == 1){
                        $content .= ' (reserved)';
                    }
                    if($room->gender_type == MALE){
                        $content .= ' (male)';
                    }else if($room->gender_type == FEAMLE){
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
    } */
    function run_unassigned_beds_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $halls = HMS_Residence_Hall::get_halls(HMS_Term::get_selected_term());

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

                    $beds = $room->get_beds();

                    foreach($beds as $bed){
                        if(!$bed->has_vacancy()){
                            continue;
                        }

                        $content = $bed->bed_letter;
                        if($bed->ra_bed == 1){
                            $content .= ' (RA)';
                        }
                        
                        $content .= ' ' . $bed->get_assigned_to_link();

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
                    if($room->is_lobby == 1){
                        $content .= ' (lobby)';
                    }
                    if($room->is_medical == 1){
                        $content .= ' (medical)';
                    }
                    if($room->is_reserved == 1){
                        $content .= ' (reserved)';
                    }
                    if($room->gender_type == MALE){
                        $content .= ' (male)';
                    }else if($room->gender_type == FEAMLE){
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

    function run_unassigned_rooms_report()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hms_residence_hall.hall_name');
        $db->addColumn('hms_floor.floor_number');
        $db->addColumn('hms_room.id');
        $db->setDistinct();
        $db->addWhere('hms_assignment.bed_id',      'hms_beds.id');
        $db->addWhere('hms_beds.bedroom_id',        'hms_bedrooms.id');
        $db->addWhere('hms_bedrooms.room_id',       'hms_room.id');
        $db->addWhere('hms_room.floor_id',          'hms_floor.id');
        $db->addWhere('hms_floor.building',         'hms_residence_hall.id');
        $db->addOrder('hms_residence_hall.hall_name');
        $db->addOrder('hms_floor.floor_number');
        $db->addOrder('hms_room.id');

        $result = $db->select();

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
        }

        foreach ($result as $room) {
            $ids[] = $room['id'];
        }

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('hms_residence_hall.hall_name');
        $db->addColumn('hms_floor.floor_number');
        $db->addColumn('hms_room.gender_type');
        $db->addColumn('hms_room.id','count');
        $db->addWhere('hms_room.floor_id',            'hms_floor.id');
        $db->addWhere('hms_floor.building',           'hms_residence_hall.id');
        $db->addWhere('hms_room.is_online',           1);
        $db->addWhere('hms_floor.is_online',          1);
        $db->addWhere('hms_residence_hall.is_online', 1);
	    $db->addWhere('hms_room.id',                  $ids, 'not in');
        $db->addOrder('hms_residence_hall.hall_name');
        $db->addOrder('hms_floor.floor_number');
        $db->addGroupBy('hms_residence_hall.hall_name');
        $db->addGroupBy('hms_floor.floor_number');
        $db->addGroupBy('hms_room.gender_type');

        $result = $db->select();

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
        }

        $content  = "<h2>Unassigned Rooms</h2>";
        $content .= '<p>Please note that this report only shows rooms that '.
                    'are <b>completely empty</b>; this means that any rooms '.
                    'that have an assignment but also have empty beds are '.
                    '<b>not</b> counted in this report.</p>';

        $hall = 'none';
        $floor = -1;
        $totalf = -1;
        $totalm = -1;
        $totalc = -1;
        $currentf = 0;
        $currentm = 0;
        $currentc = 0;
        foreach($result as $row) {
            if($floor == -1) $floor = $row['floor_number'];

            if(($floor != $row['floor_number'] || $hall != $row['hall_name']) && $hall != 'none') {
                $content .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Floor '.$floor.': (F) '.$currentf.', (M) '.$currentm.', (C) '.$currentc.'<br />';
                $floor = $row['floor_number'];
                $currentf = 0;
                $currentm = 0;
                $currentc = 0;
            }

            if($hall != $row['hall_name']) {
                $hall = $row['hall_name'];

                if($totalf > -1 && $totalm > -1 && $totalc > -1) {
                    $content .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total: (F) '.$totalf.', (M) '.$totalm.', (C) '.$totalc.'</b><br />';
                }
                $totalf = $totalm = $totalc = 0;
                $content .= '<br /><b>'.$row['hall_name'].'</b><br />';
            }
            
//            $content .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Floor '.$row['floor_number'].': ('.$gender.') '.$row['count'].'<br />';
            
            if($row['gender_type'] == 0) {        // Female 0
                $currentf = $row['count'];
                $totalf  += $row['count'];
            } else if($row['gender_type'] == 1) { // Male 1
                $currentm = $row['count'];
                $totalm  += $row['count'];
            } else if($row['gender_type'] == 2) { // Coed 2
                $currentc = $row['count'];
                $totalc  += $row['count'];
            } else {
                // Unknown
            }
        }
        $content .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total: (F) '.$totalf.', (M) '.$totalm.', (C) '.$totalc.'</b><br />';

        return $content;
    }

    /*
    function run_unassigned_beds_report()
    {
        $sql = "select hall.hall_name,
               floor.floor_number,
               room.gender_type,
               count(beds.id)

        from   hms_residence_hall as hall,
               hms_room as room,
               hms_floor as floor,
               hms_bedrooms as br,
               hms_beds as beds

        left outer join hms_assignment as assign
        on     beds.id = assign.bed_id

        where (beds.bedroom_id = br.id  AND
               br.room_id = room.id     AND
               room.floor_id = floor.id AND
               floor.building = hall.id AND

               br.is_online = 1         AND
               room.is_online = 1       AND
               floor.is_online = 1      AND
               hall.is_online = 1)      AND

              (assign.bed_id is null    OR
               0 not in (select deleted from hms_assignment where bed_id = assign.bed_id))

        group by hall.hall_name, floor.floor_number, room.gender_type

        order by hall.hall_name, floor.floor_number, room.gender_type";
                
        $result = PHPWS_DB::getAll($sql);

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
        }

        $content  = "<h2>Unassigned Beds</h2>";
        $content .= '<p>This report shows individual beds that have not '.
                    'been assigned.  Please note that one room may contain '.
                    'several beds.</p>';

        $hall = 'none';
        $floor = -1;
        $totalf = -1;
        $totalm = -1;
        $totalc = -1;
        $currentf = 0;
        $currentm = 0;
        $currentc = 0;
        foreach($result as $row) {
            if($floor == -1) $floor = $row['floor_number'];

            if(($floor != $row['floor_number'] || $hall != $row['hall_name']) && $hall != 'none') {
                $content .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Floor '.$floor.': (F) '.$currentf.', (M) '.$currentm.', (C) '.$currentc.'<br />';
                $floor = $row['floor_number'];
                $currentf = 0;
                $currentm = 0;
                $currentc = 0;
            }

            if($hall != $row['hall_name']) {
                $hall = $row['hall_name'];

                if($totalf > -1 && $totalm > -1 && $totalc > -1) {
                    $content .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total: (F) '.$totalf.', (M) '.$totalm.', (C) '.$totalc.'</b><br />';
                }
                $totalf = $totalm = $totalc = 0;
                $content .= '<br /><b>'.$row['hall_name'].'</b><br />';
            }
            
//            $content .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Floor '.$row['floor_number'].': ('.$gender.') '.$row['count'].'<br />';
            
            if($row['gender_type'] == 0) {        // Female 0
                $currentf = $row['count'];
                $totalf  += $row['count'];
            } else if($row['gender_type'] == 1) { // Male 1
                $currentm = $row['count'];
                $totalm  += $row['count'];
            } else if($row['gender_type'] == 2) { // Coed 2
                $currentc = $row['count'];
                $totalc  += $row['count'];
            } else {
                // Unknown
            }
        }
        $content .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total: (F) '.$totalf.', (M) '.$totalm.', (C) '.$totalc.'</b><br />';

        return $content;
    }
    */
    
    function run_unconfirmed_roommates_report()
    {
        $db = &new PHPWS_DB('hms_roommate_approval');
        $db->addColumn('number_roommates');
        $db->addColumn('roommate_zero');
        $db->addColumn('roommate_one');
        $db->addColumn('roommate_two');
        $db->addColumn('roommate_three');
        $db->addOrder('roommate_zero');
        $results = $db->select();

        $content  = '<h2>Unapproved Requested Roommates</h2><br /><br />';

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $count = 0;
        foreach($results as $row) {
            $zero  = $row['roommate_zero'];
            $one   = $row['roommate_one'];
            $two   = $row['roommate_two'];
            $three = $row['roommate_three'];

            $content .= "($zero) " . HMS_SOAP::get_name($zero) . '<br />';
            $content .= "($one) "  . HMS_SOAP::get_name($one)  . '<br />';
            
            if($row['number_roommates'] > 2) {
                $content .= "($two) " . HMS_SOAP::get_name($two) . '<br />';
            }

            if($row['number_roommates'] > 3) {
                $content .= "($three) " . HMS_SOAP::get_name($three) . '<br />';
            }

            $content .= '<br />';
            
            $count++;
        }

        $content .= '<strong>Total Pairs: ' . $count . '</strong>';

        return $content;
    }

    function run_assigned_students_report()
    {
        $content = '<h2>Assigned Students Report</h2>';
        if(!isset($_REQUEST['action'])) {
            $content .= 'If anyone has created any assignments since the last time New Data was Generated, then the report will be out of date.  If in doubt, generate new data, although this will take a few minutes.<br /><br />';
        }

        switch($_REQUEST['action']) {
            case 'generate':
                PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
                HMS_Assignment::generate_student_assignment_data();
                $content .= '<font color="blue">New banner data has been generated.</font><br /><br />';
                break;
            case 'run':
                return HMS_Reports::do_assigned_students_report();
                break;
            case 'pdf':
                return HMS_Reports::create_pdf_letters();
                break;
        }
        
        $link['type']    = 'reports';
        $link['op']      = 'run_report';
        $link['reports'] = 'assd_alpha';
        $link['action']  = 'generate';
        $content .= PHPWS_Text::secureLink('Generate New Data', 'hms', $link);
        $content .= '<br /><br />';

        $link['type']    = 'reports';
        $link['op']      = 'run_report';
        $link['reports'] = 'assd_alpha';
        $link['action']  = 'run';
        $content .= PHPWS_Text::secureLink('Run Report', 'hms', $link);

        return $content;
    }

    function do_assigned_students_report()
    {
        $db = &new PHPWS_DB('hms_cached_student_info');
        $db->addOrder('last_name');
        $db->addOrder('first_name');
        $db->addOrder('middle_name');

        $results = $db->select();

        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $content  = '<h2>Assigned Students</h2><br />';
        $content .= '<table border="1"><tr>';
        $content .= '<th>Username</th>';
        $content .= '<th>Student</th>';
        $content .= '<th>Banner</th>';
        $content .= '<th>Assignment</th>';
        $content .= '</tr>';

        foreach($results as $row) {
            $content .= '<tr><td>';
            $content .= $row['asu_username'] . '<br />';
            $content .= '</td><td><strong>';
            $content .= $row['last_name']   . ', ';
            $content .= $row['first_name']  . ' ';
            $content .= $row['middle_name'] . '</strong><br />';
            $content .= $row['address1'] . '<br />';
            if(isset($row['address2']) && !empty($row['address2'])) {
                $content .= $row['address2'] . '<br />';
            }
            if(isset($row['address3']) && !empty($row['address3'])) {
                $content .= $row['address3'] . '<br />';
            }

            $content .= $row['city']  . ', ';
            $content .= $row['state'] . ' ';
            $content .= $row['zip']   . '<br />';
            $content .= $row['phone_number'] . '<br />';
            
            $content .= '</td><td>';
            $content .= '<b>Gender:</b> ' . $row['gender'] . '<br />';
            $content .= '<b>Type:</b> ' . $row['student_type'] . '<br />';
            $content .= '<b>Class:</b> ' . $row['class'] . '<br />';
            $content .= '<b>Credits:</b> ' . $row['credit_hours'] . '<br />';
            $content .= '<b>Deposit:</b> ' . ($row['deposit_waived'] == 'true' ? "WAIVED" : $row['deposit_date']);
            
            $content .= '</td><td>';
            $content .= $row['hall_name'] . ' ' . $row['room_number'] . '<br />';
            $content .= $row['room_phone'] . '<br />';
            $content .= $row['movein_time'] . '<br /><br />';
            $content .= $row['roommate_name'] . '<br />';
            $content .= $row['roommate_user'] . '@appstate.edu';
            $content .= '</td></tr>';
        }
        $content .= '</table>';
        return $content;
    }

    function create_pdf_letters()
    {

    }

    function list_generated_student_assignment_data()
    {
        
    }

    function run_special_circumstances_report()
    {
        $db = &new PHPWS_DB('hms_assignment');
        $db->addWhere('hms_assignment.asu_username', 'hms_application.asu_username', 'ILIKE');
        $db->addWhere('hms_assignment.deleted', 0);

        $db->addColumn('hms_assignment.asu_username');

        $results = $db->select();

        $content = '<h2>Continuing Students who Filled Out Online Application</h2>';

        foreach($results as $row) {
	        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            $person = HMS_SOAP::get_gender_class($row['asu_username']);
            $type   = HMS_SOAP::get_student_type($row['asu_username']);

            if($person['class'] != 'NFR' && $type != 'T') {
                $content .= '<li>('.$row['asu_username'].') '.HMS_SOAP::get_full_name($row['asu_username']).'</li>';
            }
        }

        return $content;
    }

    function display_hall_structures()
    {
        $sql = "
            SELECT hms_residence_hall.id AS hall_id,
                   hms_residence_hall.banner_building_code,
                   hms_residence_hall.hall_name,
                   hms_residence_hall.is_online AS hall_online,
                   hms_floor.id AS floor_id,
                   hms_floor.floor_number,
                   hms_floor.is_online AS floor_online,
                   hms_room.id AS room_id,
                   hms_room.room_number,
                   hms_room.displayed_room_number,
                   hms_room.bedrooms_per_room,
                   hms_room.is_online AS room_online,
                   hms_bedrooms.id AS bedroom_id,
                   hms_bedrooms.bedroom_letter,
                   hms_bedrooms.number_beds,
                   hms_bedrooms.is_online AS bedroom_online,
                   hms_beds.id AS bed_id,
                   hms_beds.bed_letter
            FROM hms_residence_hall,
                 hms_floor,
                 hms_room,
                 hms_bedrooms,
                 hms_beds
            WHERE hms_beds.bedroom_id  = hms_bedrooms.id       AND
                  hms_bedrooms.room_id = hms_room.id           AND
                  hms_room.floor_id    = hms_floor.id          AND
                  hms_floor.building   = hms_residence_hall.id AND
                  hms_beds.deleted           = 0 AND
                  hms_bedrooms.deleted       = 0 AND
                  hms_room.deleted           = 0 AND
                  hms_floor.deleted          = 0 AND
                  hms_residence_hall.deleted = 0
            ORDER BY hms_residence_hall.hall_name,
                     hms_floor.floor_number,
                     hms_room.room_number,
                     hms_bedrooms.bedroom_letter,
                     hms_beds.bed_letter
        ";

        $results = PHPWS_DB::getAll($sql);

        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $content = '<h2>Appalachian State University Residence Halls</h2>';

        $hall_id     = -1;
        $floor_id    = -1;
        $room_id     = -1;
        $bedrooms_id = -1;
        $beds_id     = -1;

        $first_hall  = 1;
        $first_floor = 1;

        $count = 0;
        
        foreach($results as $result) {
            if($result['hall_id'] != $hall_id) {
                $count = 0;
                
                if(!$first_hall) $content .= '</table><br />';
                else $first_hall = 0;
                
                $hall_id = $result['hall_id'];
                $content .= '<table border="1">';
                $content .= '<tr><th colspan="22">(' .
                            $result['hall_id'] . ') ' .
                            $result['hall_name'] . ' (' .
                            $result['banner_building_code'];
                if($result['hall_online'] == 0) {
                    $content .= ' - OFFLINE';
                }
                $content .= ')</th></tr>';
            }
        
            if(++$count == 21)
            {
                $count = 0;
                $floor_id = -1;
            }

            if($result['floor_id'] != $floor_id) {
                $count = 0;

                if(!$first_floor) $content .= '</tr>';
                else $first_floor = 0;

                $floor_id = $result['floor_id'];
                $content .= '<tr>';
                $content .= '<th>' . $result['floor_number'] . '<br />';
                if($result['floor_online'] == 0) {
                    $content .= 'OFF<br />';
                }
                $content .= '<span style="font-size: .3em">' . 
                            '(' . $result['floor_id'] . ') ' .
                            '</span></th>';
            }

            $content .= '<td>' . $result['room_number'] .
                        $result['bedroom_letter'] .
                        $result['bed_letter'] . '<br />';
            if($result['room_online'] == 0 ||
               $result['bedroom_online'] == 0) {
                $content .= 'OFF<br />';
            }
            $content .= '<span style="font-size: .3em">' .
                        '(' . $result['room_id'] . ',' .
                        $result['bedroom_id'] . ',' .
                        $result['bed_id'] . ')</span>';
            $content .= '</td>';
        }

        $content .= '</tr>';
        $content .= '</table>';

        return $content;
    }

    function unassigned_applicants_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $term = HMS_Term::get_selected_term();
        
        $sql = "
            SELECT asu_username AS user,
                   student_status AS status,
                   gender         AS gender,
                   lifestyle_option,
                   preferred_bedtime,
                   room_condition,
                   hms_application.meal_option
            FROM hms_application
            LEFT OUTER JOIN hms_assignment
            ON hms_assignment.asu_username = hms_application.asu_username
            WHERE hms_assignment.asu_username IS NULL
            AND hms_application.term = {$term}
            AND hms_application.withdrawn = 0
            ORDER BY student_status, gender, asu_username
        ";
        $results = PHPWS_DB::getAll($sql);
        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        if(sizeof($results) == 0){
            $content = "<h2>Unassigned Applicants</h2>";
            $content .= "No unassigned applicants found.";
            return $content;
        }

        $ff_count = 0;
        $fm_count = 0;
        $tf_count = 0;
        $tm_count = 0;

        $content = "key: [gender, type, lifestyle preference, bed time, room condition, meal option]<br /><br />";

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        foreach($results as $row) {
            $student = HMS_SOAP::get_student_info($row['user'], $term);

            // TODO: THIS IS A HACK.  Consider removing it.
            if($student->student_type == TYPE_WITHDRAWN)
                continue;

            $app = PHPWS_Text::secureLink($row['user'], 'hms',
                array('type'    => 'student',
                      'op'      => 'view__application',
                      'student' => $row['user']));
            $content .= "($app) " . $student->last_name . ", " .
                        $student->first_name . " " .
                        $student->middle_name . " [" .
                        ($row['gender']             == 0 ? "Female, " : "Male, ") .
                        ($row['status']             == 1 ? "Freshman, " : "Transfer, ") .
                        ($row['lifestyle_option']   == 1 ? "Single, " : "Co-ed, ") .
                        ($row['preferred_bedtime']  == 1 ? "Early, " : "Late, ") .
                        ($row['room_condition']     == 1 ? "Neat, " : "Cluttered, ");

            switch($row['meal_option']){
                case BANNER_MEAL_LOW:
                    $content .= "Low";
                    break;
                case BANNER_MEAL_STD:
                    $content .= "Std";
                    break;
                case BANNER_MEAL_HIGH:
                    $content .= "High";
                    break;
                case BANNER_MEAL_SUPER:
                    $content .= "Super";
                    break;
            }
                        
            $content .= "]<br />";

            if($row['gender'] == 0) {
                if($row['status'] == 1) {
                    $ff_count++;
                } else {
                    $tf_count++;
                }
            } else {
                if($row['status'] == 1) {
                    $fm_count++;
                } else {
                    $tm_count++;
                }
            }
        }

        $head  = "<h2>Unassigned Applicants</h2><br />";
        $head .= "<p><strong>Freshman Female:</strong> $ff_count</p>";
        $head .= "<p><strong>Freshman Male:</strong> $fm_count</p>";
        $head .= "<p><strong>Transfer Female:</strong> $tf_count</p>";
        $head .= "<p><strong>Transfer Male:</strong> $tm_count</p>";

        return $head . $content;
    }

    function run_no_banner_data_report()
    {
        $db = new PHPWS_DB('hms_application');
        $db->addColumn('asu_username');
        $db->addOrder('asu_username');
        $results = $db->select();
        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $content = "<h2>Students With No Banner Data</h2><br />";

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $count = 0;
        $total = count($results);
        $whole = 0;
        foreach($results as $row) {
            if(!HMS_SOAP::is_valid_student($row['asu_username'])) {
                $content .= $row['asu_username'] . '<br />';
            }

            $percent = ((++$count / $total) * 100);
            if($percent >= $whole) {
                echo $whole++ . '%... ';
                ob_flush();
                flush();
            }
        }

        return $content;
    }

    function run_no_deposit_report()
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('asu_username');
        $db->addWhere('deleted',0);
        $db->addOrder('asu_username');
        $results = $db->select();
        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $content = "<h2>Assigned Students with No Deposit</h2><br />";

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');

        $waivers = array();
        $no_date = array();
        $not_both = array();

        $count = 0;
        $total = count($results);
        $whole = 0;
        foreach($results as $row) {
            $student = HMS_SOAP::get_student_info($row['asu_username']);
            if($student->deposit_waived != 'false')
                $waivers[] = "(" . $row['asu_username']  . ") " .
                                   $student->last_name   . ", " .
                                   $student->first_name  . " "  .
                                   $student->middle_name . "<br />";
               continue;

            if(isset($student->deposit_date))
                continue;
            else
                $no_date[] = "(" . $row['asu_username']  . ") " .
                                   $student->last_name   . ", " .
                                   $student->first_name  . " "  .
                                   $student->middle_name . "<br />";

            $not_both[] = "(" . $row['asu_username']  . ") " .
                                $student->last_name   . ", " .
                                $student->first_name  . " "  .
                                $student->middle_name . "<br />";

            $percent = ((++$count / $total) * 100);
            if($percent >= $whole) {
                echo $whole++ . '%... ';
                ob_flush();
                flush();
            }
        }
        $content .= "<h3>Deposit Waived</h3>";
        foreach($waivers as $waiver) {
            $content .= $waiver . '<br />';
        }

        $content .= "<br /><h3>No Deposit Date</h3>";
        foreach($no_date as $blah) {
            $content .= $blah . '<br />';
        }

        $content .= "<br /><h3>Really Have No Deposit</h3>";
        foreach($not_both as $blah) {
            $content .= $blah . '<br />';
        }

        $content .= '<br />Waived Count: ' . count($waivers);
        $content .= '<br />No Date Count: ' . count($no_date);
        $content .= '<br />No Deposit Count: ' . count($not_both);

        return $content;
    }

    function run_bad_type_report()
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('hms_assignment.asu_username');
        $db->addColumn('hms_room.room_number');
        $db->addColumn('hms_residence_hall.hall_name');
        $db->addWhere('hms_assignment.bed_id', 'hms_beds.id');
        $db->addWhere('hms_beds.bedroom_id', 'hms_bedrooms.id');
        $db->addWhere('hms_bedrooms.room_id', 'hms_room.id');
        $db->addWhere('hms_room.floor_id', 'hms_floor.id');
        $db->addWhere('hms_floor.building', 'hms_residence_hall.id');
        $db->addWhere('hms_assignment.deleted', 0);
        $db->addWhere('hms_beds.deleted', 0);
        $db->addWhere('hms_bedrooms.deleted', 0);
        $db->addWhere('hms_room.deleted', 0);
        $db->addWhere('hms_floor.deleted', 0);
        $db->addWhere('hms_residence_hall.deleted', 0);
        $db->addOrder('hms_residence_hall.hall_name');
        $db->addOrder('hms_room.room_number');

        $results = $db->select();
        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $content = "<h2>Assigned Students Withdrawn or Bad Type</h2><br />";

        PHPWS_Core::initModClass('hms','HMS_SOAP.php');

        $content .= "<table><tr>" .
                    "<th>Hall</th>" .
                    "<th>Room</th>" .
                    "<th>Username</th>" .
                    "<th>Name</th>" .
                    "<th>Type</th>" .
                    "</tr>";
        foreach($results as $row) {
            $student = HMS_SOAP::get_student_info($row['asu_username']);
            $type = $student->student_type;
            if($type == 'F' || $type == 'C' || $type == 'T')
                continue;
                
            $content .= "<tr>";
            $content .= "<td>{$row['hall_name']}</td>";
            $content .= "<td>{$row['room_number']}</td>";
            $content .= "<td>{$row['asu_username']}</td>";
            $content .= "<td>{$student->last_name}, " .
                        "{$student->first_name} " .
                        "{$student->middle_name}</td>";
            $content .= "<td>$type</td>";
            $content .= "</tr>";
        }
        $content .= "</table>";

        return $content;
    }
    
    function run_gender_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $sql = "
            SELECT
                hms_room.gender_type           AS room_gender,
                hms_floor.gender_type          AS floor_gender,
                hms_residence_hall.gender_type AS hall_gender,
                hms_residence_hall.banner_building_code AS bldg_id,
                hms_room.id as room_id,
                hms_room.room_number
            FROM hms_room
            JOIN hms_floor ON
                hms_room.floor_id = hms_floor.id
            JOIN hms_residence_hall ON
                hms_floor.building = hms_residence_hall.id
            WHERE
                hms_room.deleted = 0 AND
                hms_floor.deleted = 0 AND
                hms_residence_hall.deleted = 0
            ORDER BY
                hms_residence_hall.hall_name,
                hms_room.room_number
        ";

        $results = PHPWS_DB::getAll($sql);
        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        $genders = array(
            0 => 'Female',
            1 => 'Male',
            2 => 'Coed');

        $issues = array();
        $count = 0;
        $total = count($results);
        $whole = 0;
        foreach($results as $row) {
            $bid = $row['bldg_id'].' '.$row['room_number'];
            
            $percent = ((++$count / $total) * 100);
            if($percent >= $whole) {
                echo $whole++ . '%... ';
                ob_flush();
                flush();
            }

            // Get Roommates
            $sql = "
                SELECT asu_username
                FROM hms_assignment
                JOIN hms_beds ON hms_assignment.bed_id = hms_beds.id
                JOIN hms_bedrooms ON hms_beds.bedroom_id = hms_bedrooms.id
                WHERE
                    hms_bedrooms.room_id = {$row['room_id']} AND
                    hms_assignment.deleted = 0
            ";

            $mates = PHPWS_DB::getAll($sql);
            if(PHPWS_Error::isError($mates)) {
                test($mates,1);
            }

            // Make sure the roommates are the same gender
            $roomgender = 2;
            foreach($mates as $mate) {
                $gender = HMS_SOAP::get_gender($mate['asu_username'], TRUE);
                if($roomgender == 2) {
                    $roomgender = $gender;
                    continue;
                }

                if($gender != $roomgender) {
                    $issues[] = "<font color=\"red\">($bid) ".$mate['asu_username'].
                                " and $lastuser are roomed together but are of different gender!</font>";
                    $roomgender = -1;
                    break;
                }
            }

            if($roomgender == -1) continue;

            // If no one is in the room, we don't know what to do with it, so skip
            if($roomgender == 2) {
                $issues[] = "($bid) No occupants, skipping...";
                continue;
            }

            // Warn us if the roommates are not the same gender as the room
            if($roomgender != $row['room_gender']) {
                $issues[] = "($bid) Changing gender from ".
                            $genders[$row['room_gender']].' to '.
                            $genders[$roomgender];
            }

            // Set the gender of the room to that of its occupants
            $db = new PHPWS_DB('hms_room');
            $db->addWhere('id',$row['room_id']);
            $db->addValue('gender_type', $roomgender);
            $result = $db->update();

            if(PHPWS_Error::isError($result)) {
                test($result,1);
            }
        }

        $content = "<h2>Gender Mismatches</h2><br /><br />\n";
        foreach($issues as $issue) {
            $content .= "$issue<br />\n";
        }

        return $content;
    }

    function special_needs()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        
        $content = "<h2>Special Needs</h2>\n";
        
        $db = new PHPWS_DB('hms_application');
        $db->addColumn('asu_username');
        $db->addWhere('term', HMS_Term::get_selected_term());
        $db->addWhere('physical_disability', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Physical: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['asu_username']);
        }
        $content .= "</ul>\n";

        $db->resetWhere();
        $db->addWhere('term', HMS_Term::get_selected_term());
        $db->addWhere('psych_disability', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Psychological: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['asu_username']);
        }
        $content .= "</ul>\n";

        $db->resetWhere();
        $db->addWhere('term', HMS_Term::get_selected_term());
        $db->addWhere('medical_need', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Medical: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['asu_username']);
        }
        $content .= "</ul>\n";

        $db->resetWhere();
        $db->addWhere('term', HMS_Term::get_selected_term());
        $db->addWhere('gender_need', 1);
        $results = $db->select();
        $count = count($results);

        $content .= "<h3>Gender: $count</h3>\n<ul>\n";
        foreach($results as $row) {
            $content .= HMS_Reports::show_student($row['asu_username']);
        }
        $content .= "</ul>\n";

        return $content;
    }

    function show_student($username) {
        $student = HMS_SOAP::get_student_info($username);
        $name = $student->last_name . ', ' . $student->first_name . ' ' .
            $student->middle_name;
        $bid = $student->banner_id;
        $phone = $student->phone->area_code . '-' .
            substr($student->phone->number,0,3) . '-' .
            substr($student->phone->number,-4,4);
        
        return "<li>$bid: $name [$phone]</li>";
    }

    function main()
    {
        if( !Current_User::allow('hms', 'reports') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $op = $_REQUEST['op'];
        switch($op){
            case 'display_reports':
                return HMS_Reports::display_reports();
                break;
            case 'run_report':
                Layout::nakedDisplay(HMS_Reports::run_report());
                break;
            default:
                # No such 'op', or no 'op' specified
                # TODO: Find a way to throw an error here
                return $op;
                break;
        }
    }
}

?>
