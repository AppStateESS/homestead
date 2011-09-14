<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php'); // Just go ahead and do this here, since a lot of reports use it

class HMS_Reports{

    /**
     * Finds and lists all currently assigned students who have a banner type of F
     */
    public static function run_assigned_type_f(){

        $term = Term::getSelectedTerm();

        $db = new PHPWS_DB('hms_assignment');
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

    public static function run_unassigned_beds_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $halls = HMS_Residence_Hall::get_halls(Term::getSelectedTerm());

        $tpl = new PHPWS_Template('hms');
        if(!$tpl->setFile('admin/reports/unassigned_beds.tpl')){
            return 'Template error....';
        }

        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $rlcs = HMS_Learning_Community::getRLCList();

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

                $floor_content = $floor->floor_number;
                if($floor->rlc_id != null) {
                    $floor_content .= ' (' . $rlcs[$floor->rlc_id] . ')';
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
                $tpl->setData(array('FLOOR_NUM' => $floor_content));
                $tpl->parseCurrentblock();
            }

            $tpl->setCurrentBlock('hall_repeat');
            $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - ' . $vacant_beds_by_hall . ' vacant beds'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setData(array('BED_COUNT' => $vacant_beds));

        return $tpl->get();
    }

    public static function run_unassigned_beds_report_plus_offline_etc()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $halls = HMS_Residence_Hall::get_halls(Term::getSelectedTerm());

        $tpl = new PHPWS_Template('hms');
        if(!$tpl->setFile('admin/reports/unassigned_beds.tpl')){
            return 'Template error....';
        }

        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $rlcs = HMS_Learning_Community::getRLCList();

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

                $floor_content = $floor->floor_number;
                if($floor->rlc_id != null) {
                    $floor_content .= ' (' . $rlcs[$floor->rlc_id] . ')';
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
                $tpl->setData(array('FLOOR_NUM' => $floor_content));
                $tpl->parseCurrentblock();
            }

            $tpl->setCurrentBlock('hall_repeat');
            $tpl->setData(array('HALL_NAME' => $hall->hall_name . ' - ' . $vacant_beds_by_hall . ' vacant beds'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setData(array('BED_COUNT' => $vacant_beds));

        return $tpl->get();
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
                //$pager->addSortHeader('hms_fall_application.room_condition','Room Condition');
                break;
            case TERM_SPRING:
                $pager = new DBPager('hms_new_application', 'SpringApplication');
                $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_spring_application', 'id', 'id');
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
                $pdf->Cell(120, 5, 'University Housing Occupancy Report', 0, 2, 'C');
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
        $term = Term::getSelectedTerm();

        $output = "\n";

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $query = "SELECT hms_assignment.id, hms_assignment.asu_username, hms_new_application.cell_phone, hms_room.room_number, hms_floor.floor_number, hms_residence_hall.hall_name FROM hms_assignment LEFT JOIN (SELECT username, MAX(term) AS mterm FROM hms_new_application GROUP BY username) AS a ON hms_assignment.asu_username = a.username LEFT JOIN hms_new_application ON a.username = hms_new_application.username AND a.mterm = hms_new_application.term LEFT JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id LEFT JOIN hms_room ON hms_bed.room_id = hms_room.id LEFT JOIN hms_floor ON hms_room.floor_id = hms_floor.id LEFT JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id WHERE ( hms_assignment.term = $term) ORDER BY hms_residence_hall.id ASC";

        $results = PHPWS_DB::getAll($query);

        if(PHPWS_Error::logIfError($results)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($results->toString());
        }

        foreach($results as $result){
            try{
                $student = StudentFactory::getStudentByUsername($result['asu_username'], Term::getSelectedTerm());
            }catch(Exception $e){
                $output .="{$result['hall_name']},{$result['floor_number']},{$result['room_number']},ERROR,ERROR,ERROR,{$result['cell_phone']},{$result['asu_username']}@appstate.edu\n";
                continue;
            }

            $output .= "{$result['hall_name']},{$result['floor_number']},{$result['room_number']},{$student->getLastName()},{$student->getFirstName()},{$student->getBannerId()},{$result['cell_phone']},{$result['asu_username']}@appstate.edu\n";
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="Roster_Report'.Term::getCurrentTerm().'.csv"');
        echo $output;
        exit;
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
