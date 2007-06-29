<?php

class HMS_Reports{

    function display_reports()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::display_reports();
    }
    
    function run_report()
	{
	    switch($_REQUEST['reports'])
	    {
            case 'housing_apps':
                return HMS_Reports::run_applicant_demographics_report();
                break;
            case 'housing_asss':
                return HMS_Reports::run_housing_demographics_report();
                break;
            case 'unassd_rooms':
                return HMS_Reports::run_unassigned_rooms_report();
                break;
            case 'unassd_beds':
                return HMS_Reports::run_unassigned_beds_report();
                break;
            case 'special':
                return HMS_Reports::run_special_circumstances_report();
                break;
            default:
                return "ugh";
                break;
        }
    }
    
    function run_housing_demographics_report()
	{
	    PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
	    
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addWhere('deleted', '0');
        $db->addOrder('hall_name', 'asc');
        $result = $db->select();

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
        }
        
        foreach($result as $line) {
            $db = &new PHPWS_DB('hms_assignment');
            $db->addColumn('hms_assignment.asu_username');
            $db->addWhere('bed_id', 'hms_beds.id');
            $db->addWhere('hms_beds.bedroom_id', 'hms_bedrooms.id');
            $db->addWhere('hms_bedrooms.room_id', 'hms_room.id');
            $db->addWhere('hms_room.floor_id', 'hms_floor.id');
            $db->addWhere('hms_floor.building', 'hms_residence_hall.id');
            $db->addWhere('hms_residence_hall.id', $line['id']);

            $db->addWhere('hms_assignment.deleted', 0);
            $db->addWhere('hms_beds.deleted', 0);
            $db->addWhere('hms_bedrooms.deleted', 0);
            $db->addWhere('hms_room.deleted', 0);
            $db->addWhere('hms_floor.deleted', 0);
            $db->addWhere('hms_residence_hall.deleted', 0);

            $db->addWhere('hms_bedrooms.is_online', 1);
            $db->addWhere('hms_room.is_online', 1);
            $db->addWhere('hms_floor.is_online', 1);
            $db->addWhere('hms_residence_hall.is_online', 1);

            $stuffs = $db->select();
         
            if(PEAR::isError($stuffs)) {
                PHPWS_Error::log($stuffs);   
                return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
            }

            foreach($stuffs as $stuff) {
                $person = HMS_SOAP::get_gender_class($stuff['asu_username']);
                if(isset($person) && $person != NULL) {
                    if(!isset($person['gender']) || $person['gender'] == NULL ||
                            ($person['gender'] != 'M' && $person['gender'] != 'F')) {
                        $problems[$line['hall_name']][$stuff['asu_username']][] =
                            'Gender is unrecognized ('.$person['gender'].')';
                    }
                    if(!isset($person['class']) || $person['class'] == NULL ||
                            ($person['class'] != 'NFR' && $person['class'] != 'FR' &&
                             $person['class'] != 'SO' && $person['class'] != 'JR' &&
                             $person['class'] != 'SR')) {
                        $problems[$line['hall_name']][$stuff['asu_username']][] =
                            'Class is unrecognized ('.$person['class'].')';
                    }
                    if(!isset($person['type']) || $person['type'] == NULL ||
                            ($person['type'] != 'C' && $person['type'] != 'T' &&
                             $person['type'] != 'F')) {
                        $problems[$line['hall_name']][$stuff['asu_username']][] =
                            'Type is unrecognized ('.$person['type'].')';
                    }

                    if(    ($person['type'] == 'F' && $person['class'] != 'NFR' && 
                            $person['class'] != 'FR') ||
                           ($person['type'] != 'F' && $person['class'] == 'NFR')) {
                        $problems[$line['hall_name']][$stuff['asu_username']][] =
                        'Type is '.$person['type'].' but Class is '.$person['class'];
                    }
                } else {
                    $problems[$line['hall_name']][$stuff['asu_username']][] =
                        'PERSON is unset or is null';
                }
                
                $t = $person['type'];
                $g = $person['gender'];
                $c = $person['class'];

                if(isset($building[$line['hall_name']][$t][$c][$g])) {
                    $building[$line['hall_name']][$t][$c][$g]++;
                } else {
                    $building[$line['hall_name']][$t][$c][$g] = 1;
                }
            }
        }

//        test($problems);

        $total['F']['NFR']['M'] = 0;
        $total['F']['FR']['M']  = 0;
        $total['C']['FR']['M']  = 0;
        $total['C']['SO']['M']  = 0;
        $total['C']['JR']['M']  = 0;
        $total['C']['SR']['M']  = 0;
        $total['T']['FR']['M']  = 0;
        $total['T']['SO']['M']  = 0;
        $total['T']['JR']['M']  = 0;
        $total['T']['SR']['M']  = 0;
        $total['F']['NFR']['F'] = 0;
        $total['F']['FR']['F']  = 0;
        $total['C']['FR']['F']  = 0;
        $total['C']['SO']['F']  = 0;
        $total['C']['JR']['F']  = 0;
        $total['C']['SR']['F']  = 0;
        $total['T']['FR']['F']  = 0;
        $total['T']['SO']['F']  = 0;
        $total['T']['JR']['F']  = 0;
        $total['T']['SR']['F']  = 0;

        $content = '';
/*
        if(isset($problems) && count($problems) > 0) {
            $content .= '<font color="red"><b>Some problems were found while retrieving data from Banner:</b></font><br />';
            foreach($problems as $problem) {
                $content .= $problem . '<br />';
            }
            $content .= '<br /><br />';
        }*/

        foreach($building as $hall) {
            ksort($hall);
            $name = key($building);

            $m = $building[$name]['F']['NFR']['M'] +
                 $building[$name]['F']['FR']['M'] +
                 $building[$name]['C']['FR']['M'] +
                 $building[$name]['C']['SO']['M'] +
                 $building[$name]['C']['JR']['M'] +
                 $building[$name]['C']['SR']['M'] +
                 $building[$name]['T']['FR']['M'] +
                 $building[$name]['T']['SO']['M'] +
                 $building[$name]['T']['JR']['M'] +
                 $building[$name]['T']['SR']['M'];

            $f = $building[$name]['F']['NFR']['F'] +
                 $building[$name]['F']['FR']['F'] +
                 $building[$name]['C']['FR']['F'] +
                 $building[$name]['C']['SO']['F'] +
                 $building[$name]['C']['JR']['F'] +
                 $building[$name]['C']['SR']['F'] +
                 $building[$name]['T']['FR']['F'] +
                 $building[$name]['T']['SO']['F'] +
                 $building[$name]['T']['JR']['F'] +
                 $building[$name]['T']['SR']['F'];
            
            $content .= '<table border="1">';
            $content .= '<tr><th colspan="12"><h2 style="text-align: center">' . $name . '</h2></th></tr>';
            $content .= '<tr>';
            $content .= '<th>TYPE</th>';
            $content .= '<th colspan="2">Freshmen (F)</th>';
            $content .= '<th colspan="4">Continuing (C)</th>';
            $content .= '<th colspan="4">Transfer (T)</th>';
            $content .= '<th rowspan="2">TOTAL</th>';
            $content .= '</tr><tr>';
            $content .= '<th>CLASS</th>';
            $content .= '<th>0 HRS</th><th>1+ HRS</th>';
            $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
            $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
            $content .= '</tr><tr>';
            $content .= '<th>Male</th>';
            $content .= '<td>' . $building[$name]['F']['NFR']['M'] . '</td>';
            $content .= '<td>' . $building[$name]['F']['FR']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['FR']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['SO']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['JR']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['SR']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['FR']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['SO']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['JR']['M']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['SR']['M']  . '</td>';
            $content .= '<td>' . $m . '</td>';
            $content .= '</tr><tr>';
            $content .= '<th>Female</th>';
            $content .= '<td>' . $building[$name]['F']['NFR']['F'] . '</td>';
            $content .= '<td>' . $building[$name]['F']['FR']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['FR']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['SO']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['JR']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['C']['SR']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['FR']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['SO']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['JR']['F']  . '</td>';
            $content .= '<td>' . $building[$name]['T']['SR']['F']  . '</td>';
            $content .= '<td>' . $f . '</td>';
            $content .= '</tr><tr>';
            $content .= '<th colspan="11" style="align: right;">Banner Problems</th>';
            $content .= '<td>' . count($problems[$name]) . '</td>';
            $content .= '</tr><tr>';
            $content .= '<th colspan="11" style="align: right;>Total</th>';
            $content .= '<td>' . ($m + $f) . '</td>';

            if(isset($problems[$name])) {
                $content .= '</tr><tr><td colspan="12">';
                $content .= '<strong>Specific Problems</strong><br />';
                foreach($problems[$name] as $user => $problem) {
                    $content .= $user;
                    foreach($problem as $issue) {
                        $content .= '; ' . $issue;
                    }
                    $content .= '<br />';
                }
                $content .= '<br />';
            }

            $content .= '</tr></table><br /><br />';

            $total['F']['NFR']['M'] += $building[$name]['F']['NFR']['M'];
            $total['F']['FR']['M']  += $building[$name]['F']['FR']['M'];
            $total['C']['FR']['M']  += $building[$name]['C']['FR']['M'];
            $total['C']['SO']['M']  += $building[$name]['C']['SO']['M'];
            $total['C']['JR']['M']  += $building[$name]['C']['JR']['M'];
            $total['C']['SR']['M']  += $building[$name]['C']['SR']['M'];
            $total['T']['FR']['M']  += $building[$name]['T']['FR']['M'];
            $total['T']['SO']['M']  += $building[$name]['T']['SO']['M'];
            $total['T']['JR']['M']  += $building[$name]['T']['JR']['M'];
            $total['T']['SR']['M']  += $building[$name]['T']['SR']['M'];
            $total['F']['NFR']['F'] += $building[$name]['F']['NFR']['F'];
            $total['F']['FR']['F']  += $building[$name]['F']['FR']['F'];
            $total['C']['FR']['F']  += $building[$name]['C']['FR']['F'];
            $total['C']['SO']['F']  += $building[$name]['C']['SO']['F'];
            $total['C']['JR']['F']  += $building[$name]['C']['JR']['F'];
            $total['C']['SR']['F']  += $building[$name]['C']['SR']['F'];
            $total['T']['FR']['F']  += $building[$name]['T']['FR']['F'];
            $total['T']['SO']['F']  += $building[$name]['T']['SO']['F'];
            $total['T']['JR']['F']  += $building[$name]['T']['JR']['F'];
            $total['T']['SR']['F']  += $building[$name]['T']['SR']['F'];
            
            next($building);
        }
        $content .= '======================================================<br /><br />';

        $m = $f = 0;

        $content .= '<table border="1">';
        $content .= '<tr><th colspan="12"><h2 style="text-align: center">TOTALS</h2></th></tr>';
        $content .= '<tr>';
        $content .= '<th>TYPE</th>';
        $content .= '<th colspan="2">Freshmen (F)</th>';
        $content .= '<th colspan="4">Continuing (C)</th>';
        $content .= '<th colspan="4">Transfer (T)</th>';
        $content .= '<th rowspan="2">TOTAL</th>';
        $content .= '</tr><tr>';
        $content .= '<th>CLASS</th>';
        $content .= '<th>0 HRS</th><th>1+ HRS</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '<th>FR</th><th>SO</th><th>JR</th><th>SR</th>';
        $content .= '</tr><tr>';
        $content .= '<th>Male</th>';
        $content .= '<td>' . ($m = $total['F']['NFR']['M']) . '</td>';
        $content .= '<td>' . ($m = $total['F']['FR']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['C']['FR']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['C']['SO']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['C']['JR']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['C']['SR']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['T']['FR']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['T']['SO']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['T']['JR']['M'])  . '</td>';
        $content .= '<td>' . ($m = $total['T']['SR']['M'])  . '</td>';
        $content .= '<td>' . $m . '</td>';
        $content .= '</tr><tr>';
        $content .= '<th>Female</th>';
        $content .= '<td>' . ($f = $total['F']['NFR']['F']) . '</td>';
        $content .= '<td>' . ($f = $total['F']['FR']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['C']['FR']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['C']['SO']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['C']['JR']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['C']['SR']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['T']['FR']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['T']['SO']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['T']['JR']['F'])  . '</td>';
        $content .= '<td>' . ($f = $total['T']['SR']['F'])  . '</td>';
        $content .= '<td>' . $f . '</td>';
        $content .= '</tr><tr>';
        $content .= '<th colspan="11" style="align: right;">Banner Problems</th>';
        $content .= '<td>' . /* TODO: COUNT */ '</td>';
        $content .= '</tr><tr>';
        $content .= '<th colspan="11" style="align: right;">Total</th>';
        $content .= '<td>' . ($m + $f) . '</td>';

        // TODO: Specific Problems
        
        $content .= '</tr></table><br /><br />';
        $content .=  "<br /> ";
        
/*        if(isset($problems) && count($problems) > 0) {
            $content .= '<h2 style="color: red;">Errors:</h2>';
            $content .=  '<span style="color: red; font-weight: bold;">Unknown Gender, Type, or Class: ' . count($problems) . '</span><br /> ';
        }
        $content .=  "<br /><br /> ";*/

        return $content;
    }

    function run_applicant_demographics_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $db = &new PHPWS_DB('hms_application');
        $db->addColumn('hms_student_id');
        $db->addWhere('deleted', '0');
        $db->addOrder('hms_student_id', 'ASC');
        $results = $db->select();

        if(PEAR::isError($results)) {
            PHPWS_Error::log($results);
            return '<font color="red"><b>A database error occurred running this report.  Please contact Electronic Student Services immediately.</b></font>';
        }
        
        $content = '';

        foreach($results as $line) {
            $person = HMS_SOAP::get_gender_class($line['hms_student_id']);

            if(!$person['gender'] && !$person['class']) {
                if(isset($application['null'])) {
                    $application['null']++;
                } else {
                    $application['null'] = 1;
                }
                continue;
            }

            $g = $person['gender'];
            $c = $person['class'];

            if(isset($application[$c][$g])) {
                $application[$c][$g]++;
            } else {
                $application[$c][$g] = 1;
            }
        }

        $content .= "Housing Applications received by class and gender:<br /><br />";
        $content .= "New Freshman <br />";
        $content .= "Male: " . $application["NFR"]["M"] . "<br />";
        $content .= "Female: " . $application["NFR"]["F"] . "<br />";
        $content .= "<br />**Note: New Freshmen are classified as any freshman with 0 completed credit hours at Appalachian State University**<br />\n";
        $content .= "<br />";
        $content .= "Freshmen <br />";
        $content .= "Male: " . $application["FR"]["M"] . "<br />";
        $content .= "Female: " . $application["FR"]["M"] . "<br />";
        $content .= "<br />";
        $content .= "Sophomore <br />";
        $content .= "Male: " . $application["SO"]["M"] . "<br />";
        $content .= "Female: " . $application["SO"]["F"] . "<br />";
        $content .= "<br />";
        $content .= "Junior <br />";
        $content .= "Male: " . $application["JR"]["M"] . "<br />";
        $content .= "Female: " . $application["JR"]["F"] . "<br />";
        $content .= "<br />";
        $content .= "Senior <br />";
        $content .= "Male: " . $application["SR"]["M"] . "<br />";
        $content .= "Female: " . $application["SR"]["F"] . "<br />";
        $content .= "<br />";
        $content .= "No Class or Gender Data Available<br />";
        $content .= "Total: " . $application["null"] . "<br />";
        $content .= "<br />";
        $content .= "<br />";
    
        return $content;
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
        $db->addWhere('hms_assignment.deleted',     0);
        $db->addWhere('hms_beds.deleted',           0);
        $db->addWhere('hms_bedrooms.deleted',       0);
        $db->addWhere('hms_room.deleted',           0);
        $db->addWhere('hms_floor.deleted',          0);
        $db->addWhere('hms_residence_hall.deleted', 0);
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
        $db->addWhere('hms_room.deleted',             0);
        $db->addWhere('hms_floor.deleted',            0);
        $db->addWhere('hms_residence_hall.deleted',   0);
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

               beds.deleted = 0         AND
               br.deleted = 0           AND
               room.deleted = 0         AND
               floor.deleted = 0        AND
               hall.deleted  = 0        AND

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

    function run_special_circumstances_report()
    {
        $db = &new PHPWS_DB('hms_assignment');
        $db->addWhere('hms_assignment.asu_username', 'hms_application.hms_student_id');
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

    function main(){
        $op = $_REQUEST['op'];
        switch($op){
            case 'display_reports':
                return HMS_Reports::display_reports();
                break;
            case 'run_report':
                return HMS_Reports::run_report();
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
