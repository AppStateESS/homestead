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
                        'unassd_beds_etc'       => 'All Unassigned Beds (includes offline, reserved, etc)',
                        'no_ban_data'           => 'Students Without Banner Data',
                        'vacancy_report'        => 'Hall Occupancy Report',
                        'roster_report'         => 'Floor Roster Report',
                        'applied_data_export'   => 'Applied Student Data Export',
                        'assigned_data_export'  => 'Assigned Student Data Export',
                        'full_roster_report'    => 'Package Desk Roster',
                        'over_twenty_five'      => 'Over 25 report',
                        'single_vs_coed'        => 'Single gender vs. Co-ed report',
                        'reappAvailability'    => 'Re-application Availability Report'
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
            case 'unassd_beds_etc':
                return HMS_Reports::run_unassigned_beds_report_plus_offline_etc();
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
            case 'single_vs_coed':
                return HMS_Reports::single_vs_coed();
            case 'reappAvailability':
                return HMS_Reports::reappAvailability();
            default:
                $content .= "ugh";
            break;
        }

        return $content;
    }

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
                    if ( isset($rlcs[$floor->rlc_id]) )
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

    /**
     * Report lists rooms in each residence hall that are still available, along with
     * the available beds in the room.  Also, show the number of beds allocated to the
     * lotter for each residence hall.
     *
     */
    public static function reappAvailability()
    {
        $term = Term::getSelectedTerm();

        // Available rooms in each residence hall.
        $db = new PHPWS_DB('hms_bed');
        $db->addJoin('LEFT', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');
        $db->addWhere('hms_bed.ra_bed', 0);
        $db->addWhere('hms_room.private_room', 0);
        $db->addWhere('hms_room.is_overflow', 0);
        $db->addWhere('hms_room.is_medical', 0);
        $db->addWhere('hms_room.is_reserved', 0);
        $db->addWhere('hms_room.is_online', 1);
        $db->addWhere('hms_bed.term', $term);
        $db->addColumn('hms_room.room_number');
        $db->addColumn('hms_bed.bed_letter', null, null, True);
        $db->addColumn('hms_residence_hall.hall_name');
        $db->addGroupBy('hms_residence_hall.hall_name');
        $db->addGroupBy('hms_room.room_number');
        $db->addOrder('hms_residence_hall.hall_name');
        $availRooms = $db->select();

        // Allocated beds for lottery.
        $db = new PHPWS_DB('hms_bed');
        $db->addJoin('LEFT' , 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT' , 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT' , 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');
        $db->addJoin('RIGHT', 'hms_bed', 'hms_lottery_reservation', 'id', 'bed_id');
        $db->addWhere('hms_lottery_reservation.term', $term);
        $db->addColumn('hms_residence_hall.hall_name');
        $db->addColumn('hms_bed.id', null, null, True);
        $db->addGroupBy('hms_residence_hall.hall_name');
        $db->setIndexBy('hall_name');
        $lotteryBeds = $db->select();

        $tpl = new PHPWS_Template('hms');
        $tpl->setFile('admin/reports/reapp_availability.tpl');

        //
        // "The parent row must be parsed after the child rows."

        // Preload currHall with first residence hall name
        $currHall = $availRooms[0]['hall_name'];
        foreach($availRooms as $row){
            // Change halls, create new block.
            if($currHall != $row['hall_name'] || $currHall == null){
                $tpl->setCurrentBlock('halls');
                // Get allocated beds for the residence hall.
                $lottCount = isset($lotteryBeds[$currHall]['count']) ? $lotteryBeds[$currHall]['count'] : 0;
                $tpl->setData(array('HALL_NAME' => $currHall,
                                    'LOTTERY_BEDS' => $lottCount));

                $tpl->parseCurrentBlock();
                $currHall = $row['hall_name'];
            }
            // Add room to residence hall template block.
            $tpl->setCurrentBlock('rooms');
            $tpl->setData(array('ROOM_NUM' => $row['room_number'],
                                'BED_COUNT' => $row['count']));
            $tpl->parseCurrentBlock();
        }

        // Get last residence hall. Can't parse parent before child with template class.
        $tpl->setCurrentBlock('halls');
        $tpl->setData(array('HALL_NAME' => $currHall));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }
}
?>
