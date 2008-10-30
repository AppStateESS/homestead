<?php

/**
 * Letter
 *
 * This class facilitates the printing of letters for students in HMS.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

require_once(PHPWS_SOURCE_DIR . '/mod/hms/fpdf.php');
define('HEIGHT', 0.1875);

class HMS_Letter
{
    var $address1   = NULL;
    var $address2   = NULL;
    var $address3   = NULL;
    var $address4   = NULL;
    var $address5   = NULL;
    var $date       = NULL;
    var $semester   = NULL;
    //var $hall       = NULL;
    //var $room       = NULL;
    var $assignment = NULL;
    var $room_phone = NULL;
    var $roommate   = array();
    var $checkin    = NULL;
    var $message    = NULL;
    var $student_type = NULL;

    function Letter()
    {
    }

    function render(&$pdf)
    {
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);

        // Address
        if(is_null($this->address4) && is_null($this->address5))
            $pdf->Write(HEIGHT, "\n");
        $pdf->Write(HEIGHT, $this->address1 . "\n");
        $pdf->Write(HEIGHT, $this->address2 . "\n");
        $pdf->Write(HEIGHT, $this->address3 . "\n");
        if(!is_null($this->address4))
            $pdf->Write(HEIGHT, $this->address4 . "\n");
        if(!is_null($this->address5))
            $pdf->Write(HEIGHT, $this->address5 . "\n");
        $pdf->Ln(HEIGHT);

        // Date
        $pdf->Write(HEIGHT, $this->date . "\n\n");

        // First Body of Text
        $pdf->Write(HEIGHT, "The Department of Housing & Residence Life would like to welcome you to Appalachian State University and let you know we are preparing for your arrival.\n\nYou will find your housing assignment for the Fall Semester listed below.  Enclosed is additional information concerning living in the residence halls.  Please be sure to read all this information carefully.  ");
        $pdf->SetFont('Times','BI');
        $pdf->Write(HEIGHT, "IT IS IMPERATIVE THAT YOU BRING THIS LETTER WITH YOU");
        $pdf->SetFont('Times');
        $pdf->Write(HEIGHT, " so the move-in parking staff can issue a parking pass once you arrive on campus.\n\n");

        // Table
        $pdf->SetLineWidth(0.025);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Line($x, $y, $x + 6, $y);
        $pdf->SetY($y + 0.025);
        
        $pdf->Cell(0, HEIGHT, "Room Assignment Notification", NULL, 1, 'C');
        $pdf->Cell(0, HEIGHT, $this->semester, NULL, 1, 'C');
        $pdf->Ln(HEIGHT);

        $pdf->Write(HEIGHT, "Assignment: ");
        $pdf->Cell(2.75, HEIGHT, $this->assignment);
        
        if($this->room_phone != '' && !is_null($this->room_phone)){
            $pdf->Write(HEIGHT, " Room Phone: ");
            $pdf->Cell(1.25, HEIGHT, '828-266-' . $this->room_phone);
        }
        $pdf->Ln(HEIGHT);

        // Skip roommate info if no roommates
        if(!is_null($this->roommate)){
            $roommate_list = implode("\n", $this->roommate);
            $roommate_size = sizeof($this->roommate) * HEIGHT;
        
            $pdf->Write(HEIGHT, "Roommate: ");
            $pdf->MultiCell(5.00, HEIGHT, $roommate_list);
        }else{
            $pdf->Write(HEIGHT, "Roommate: To be determined.");
            $pdf->Cell(5.00, HEIGHT, $roommate_list);
        }
            
        $pdf->Ln(HEIGHT);

        $pdf->Write(HEIGHT, "Check-in Time: ");
        $pdf->Cell(2.4, HEIGHT, $this->checkin);

        if($this->student_type == TYPE_CONTINUING){
            $pdf->Cell(2, HEIGHT, "UPPERCLASSMEN ONLY");
        }else{
            $pdf->Cell(2, HEIGHT, "FRESHMEN & TRANSFER ONLY");
        }
        $pdf->Ln(HEIGHT);
        $pdf->Ln(HEIGHT);

        $y = $pdf->GetY();
        $pdf->Line($x, $y, $x + 6, $y);
        $pdf->SetY($y + 0.025);

        $pdf->Ln(HEIGHT);

        // Message
        $pdf->Write(HEIGHT, $this->message . "\n\n");

        // Second Body of Text
        $pdf->Write(HEIGHT, "Should you have any questions, please feel free to contact our office at 828-262-6111.  You may also visit our website at: www.housing.appstate.edu.\n\n");

        // Signature
        $pdf->Write(HEIGHT, "Sincerely,\n");
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Image(PHPWS_SOURCE_DIR . '/mod/hms/img/stacysig.png', $x, $y, NULL, .5, 'PNG');
        $pdf->SetY($y + .5);
        $pdf->Write(HEIGHT, "Stacy R. Sears\nAssistant Director\nHousing & Residence Life");
    }

    function put_into_pile(&$freshmen_male, &$freshmen_female, &$continuing_male, &$continuing_female, $student)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Suite.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $term = HMS_Term::get_selected_term();

        $assignment = HMS_Assignment::get_assignment($student, $term);
        if($assignment === NULL || $assignment == FALSE){
            test($assignment, 1); // This *shouldn't* ever happen...
        }else{
            $assignment_text    = $assignment->where_am_i();
            $bed_phone          = $assignment->get_phone_number(); //room phone number

            # Determine the student's type and figure out their movein time
            $type = HMS_SOAP::get_student_type($student, $term);

            //test($type,1);

            if($type == TYPE_CONTINUING){
                $movein_time_id = $assignment->get_rt_movein_time_id();
            }else{
                $movein_time_id = $assignment->get_ft_movein_time_id();
            }

            //test($assignment->get_ft_movein_time_id(),1);
            
            if($movein_time_id == NULL){
                //test($assignment, 1); // Will only happen if there's no move-in time set for the floor,student type
                $movein_time = "Unknown";
            }else{
                $movein_time_obj = new HMS_Movein_Time($movein_time_id);
                $movein_time = $movein_time_obj->get_formatted_begin_end();
            }
        }

        # Get a list of the roommates that are actually assigned with this student
        $room_id = $assignment->get_room_id();
        $room = new HMS_Room($room_id);

        if($room->is_in_suite()){
            # Go to the suite level to get all the roommates
            $suite = new HMS_Suite($room->suite_id);
            $assignees = $suite->get_assignees(); // get an array of student objects for those assigned to this suite
        }else{
            # Go to the room level to get all the roommates
            $assignees = $room->get_assignees(); // get an array of student objects for those assigned to this room
        }
        
        # Update this student's assignment to say we printed a letter
        $sql = "
            UPDATE hms_assignment
            SET letter_printed = 1
            WHERE hms_assignment.id = {$assignment->id}
        ";
        PHPWS_DB::getAll($sql);

        $letter = new HMS_Letter;

        $letter->address1 = HMS_SOAP::get_full_name_inverted($assignment->asu_username);

        $addr = HMS_SOAP::get_address($assignment->asu_username, NULL);
                            
        $letter->address2 = $addr->line1;
        
        $citystatezip = $addr->city  . ', ' .
                        $addr->state . ' '  .
                        $addr->zip;
                        
        if(empty($addr->line2)) {
            $letter->address3 = $citystatezip;
        } else {
            $letter->address3 = $addr->line2;
            if(empty($addr->line3)) {
                $letter->address4 = $citystatezip;
            } else {
                $letter->address4 = $addr->line3;
                $letter->address5 = $citystatezip;
            }
        }

        $letter->date     = strftime("%B %d, %Y");
        $letter->semester = "Fall, 2008";
        
        $letter->assignment = $assignment_text;
        $letter->room_phone = $bed_phone;
    
        // Skip adding roommate info if only this student is assigned
        if(sizeof($assignees) > 1){
            foreach($assignees as $roommate){
                // Don't add *this* student to the roommate list
                if($roommate->asu_username == $student){
                    continue;
                }
                $letter->roommate[] = HMS_SOAP::get_full_name_inverted($roommate->asu_username) . ' (' . $roommate->asu_username . '@appstate.edu)';
            }
        }else{
            $letter->roommate = NULL;
        }
        
        $letter->checkin = $movein_time;
        $letter->message = "Freshmen and transfer check-in is August 22 from 10am to 4pm.  Returning student check-in starts on August 23.  See above for your scheduled time.  Please go to your assigned hall office to pick up your residence hall room key and check-in information. If you have a conflict, you can check-in anytime after your scheduled time, until 6pm on August 26.  Failure to check-in by 6pm on August 26 will result in assignment cancellation (See pages 15-16 of the Residence Hall License Contract booklet).";

        $gender = HMS_SOAP::get_gender($student, TRUE);
        $type   = HMS_SOAP::get_student_type($student, $term);

        $letter->student_type = $type;

        if($type == TYPE_CONTINUING) {
            
            if($gender == MALE){
                $continuing_male[] = $letter;
            }
            if($gender == FEMALE){
                $continuing_female[] = $letter;
            }
        } else {
            if($gender == MALE){
                $freshmen_male[] = $letter;
            }
            if($gender == FEMALE){
                $freshmen_female[] = $letter;
            }
        }
    }

    function main()
    {
        switch($_REQUEST['op']) {
            case 'generate':
                return HMS_Letter::generate_updated();
            case 'list':
                return HMS_Letter::list_generated();
            case 'pdf':
                return HMS_Letter::pdf();
            case 'csv':
                return HMS_Letter::csv();
            case 'email_menu':
                return HMS_Letter::show_email_menu();
            case 'email':
                return HMS_Letter::email();
            default:
                HMS_Maintenance::main();
                break;
        }
    }

    function list_generated()
    {
        $files = scandir('/var/generated_docs');
        if(count($files) < 3) {
            return "No letters have been generated.";
        }

        $content = '<h2>Past Generated Letters</h2>' .
                   '<table cellpadding="5" width="99%"><tr>' .
                   '<th>Date Generated</th>' .
                   '<th>Type</th>' .
                   '<th>Filename</th>' .
                   '<th>Actions</th>' .
                   "</tr>\n";

        for($i = 0; $i < count($files); $i++) {
            $filename = $files[$i];

            if(substr($filename, 0, 16) != 'housing_letters_')
                continue;

            $namepart = substr($filename, 0, 30);

            $date = substr($filename, 16, 4) . '/' .
                    substr($filename, 20, 2) . '/' .
                    substr($filename, 22, 2) . ' ' .
                    substr($filename, 24, 2) . ':' .
                    substr($filename, 26, 2) . ':' .
                    substr($filename, 28, 2);

            $type = substr($filename, 31, 3);

            if($type != 'pdf' && $type != 'csv')
                continue;

            $link = PHPWS_Text::secureLink(_('Download'), 'hms',
                array('type'=>'letter', 'op'=>$type, 'file'=>$namepart));

            if($i % 4 > 1) {
                $bgcolor = ' bgcolor="#DDDDDD"';
            } else {
                $bgcolor = '';
            }

            $content .= "<tr>" .
                        "<td$bgcolor>$date</td>" .
                        "<td$bgcolor>$type</td>" .
                        "<td$bgcolor>$filename</td>" .
                        "<td$bgcolor>$link</td>" .
                        "</tr>\n";
        }

        $content .= '</table>';

        return $content;
    }

    function pdf()
    {
        if($_REQUEST['authkey'] != Current_User::getAuthKey()) {
            return "Access Denied";
        }
        
        if(isset($_REQUEST['file'])) {
            return HMS_Letter::print_file('pdf', $_REQUEST['file']);
        }

        return HMS_Letter::print_file('pdf');
    }

    function csv()
    {
        if($_REQUEST['authkey'] != Current_User::getAuthKey()) {
            return "Access Denied";
        }
        
        if(isset($_REQUEST['file'])) {
            return HMS_Letter::print_file('csv', $_REQUEST['file']);
        }

        return HMS_Letter::print_file('csv');
    }

    function print_file($ext, $name=null)
    {
        if(!isset($name)) {
            $files = scandir('/var/generated_docs');
            if(count($files) < 3) {
                return "No letters have been generated.";
            }
            if($ext == 'pdf') {
                $name = $files[count($files) - 1];
            } else if($ext == 'csv') {
                $name = $files[count($files) - 2];
            }
        } else {
            $name = $name . '.' . $ext;
        }

        $filename = "/var/generated_docs/$name";

        if(!file_exists($filename)) {
            return "'$name' does not exist.  Please contact ESS.";
        }

        if($ext == 'pdf') {
            header('Content-type: application/pdf');
        } else if($ext == 'csv') {
            header('Content-type: text/csv');
        } else {
            return "Bad type '$ext'.  Please contact ESS.";
        }

        header('Content-Disposition: attachment; filename="'.$name.'"');

        readfile($filename);
        
        exit;
    }

    function generate_updated()
    {
        // Initialize list of people that need a letter
        $needs_letter = array();
        
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $term = HMS_Term::get_selected_term();

        // Get everyone that needs a letter
        $sql = "
            SELECT
                hms_assignment.asu_username
            FROM hms_assignment
            WHERE
                hms_assignment.letter_printed = 0
                AND hms_assignment.term = {$term}
            ORDER BY
                asu_username
        ";

        $results = PHPWS_DB::getCol($sql);
        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        // Get out of here if no one needs a letter
        if(is_null($results)) {
            return "No new letters needed to be generated.";
        }

        // Separate into freshmen/transfer, and continuing, male and female
        // Initalize a letter object for each student and place it in the proper array
        $fm_letters = array(); // freshmen male
        $ff_letters = array(); // freshmen female
        $cm_letters = array(); // continuing male
        $cf_letters = array(); // continuing female

        $i = 0;
        foreach($results as $student) {
            /*
            if($i > 10){
                break;
            }*/
            HMS_Letter::put_into_pile($fm_letters, $ff_letters, $cm_letters, $cf_letters, $student);
            $i++;
        }

        // Sort
        HMS_Letter::letterSort($fm_letters);
        HMS_Letter::letterSort($ff_letters);
        HMS_Letter::letterSort($cm_letters);
        HMS_Letter::letterSort($cf_letters);

        // Total counts of letters created
        $fm_count = count($fm_letters);
        $ff_count = count($ff_letters);
        $cm_count = count($fm_letters);
        $cf_count = count($cf_letters);
        
        // Initialize PDF and CSV files
        $pdf = HMS_Letter::pdf_factory();
        $csv = "";

        // Render the letters and CSVs
        $q = '"';
        if(count($fm_letters) > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Times','',50);
            $pdf->Write(1, "MALE");
            $pdf->Write(1, "FRESHMEN");
            foreach($fm_letters as $letter) {
                $letter->render($pdf);
                $csv .= "$q{$letter->address1}$q,$q{$letter->address2}$q," .
                        "$q{$letter->address3}$q,$q{$letter->address4}$q," .
                        "$q{$letter->address5}$q\n";
            }
        }
        if(count($ff_letters) > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Times','',50);
            $pdf->Write(1, "FEMALE");
            $pdf->Write(1, "FRESHMEN");
            foreach($ff_letters as $letter) {
                $letter->render($pdf);
                $csv .= "$q{$letter->address1}$q,$q{$letter->address2}$q," .
                        "$q{$letter->address3}$q,$q{$letter->address4}$q," .
                        "$q{$letter->address5}$q\n";
            }
        }
        if(count($cm_letters) > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Times','',50);
            $pdf->Write(1, "MALE");
            $pdf->Write(1, "CONTINUING");
            foreach($cm_letters as $letter) {
                $letter->render($pdf);
                $csv .= "$q{$letter->address1}$q,$q{$letter->address2}$q," .
                        "$q{$letter->address3}$q,$q{$letter->address4}$q," .
                        "$q{$letter->address5}$q\n";
            }
        }
        if(count($cf_letters) > 0) {
            $pdf->AddPage();
            $pdf->SetFont('Times','',50);
            $pdf->Write(1, "FEMALE");
            $pdf->Write(1, "CONTINUING");
            foreach($cf_letters as $letter) {
                $letter->render($pdf);
                $csv .= "$q{$letter->address1}$q,$q{$letter->address2}$q," .
                        "$q{$letter->address3}$q,$q{$letter->address4}$q," .
                        "$q{$letter->address5}$q\n";
            }
        }

        // Unique filename for the generated files
        $now = strftime("%Y%m%d%H%M%S");
        $filename = "housing_letters_$now";
        
        // Write the PDF
        $pdf->Output("/var/generated_docs/$filename.pdf");

        // Write the CSV
        $fp = fopen("/var/generated_docs/$filename.csv", 'w');
        fwrite($fp,$csv);
        fclose($fp);
        
        // Report back to the user a job well done
        $content = "Generated letters for $fm_count male freshmen, $ff_count female freshmen, $cm_count male continuing, $cf_count female continuing.<br /><br />";
        $content .= PHPWS_Text::secureLink(_('Download PDF'), 'hms',
            array('type'=>'letter', 'op'=>'pdf', 'file'=>$filename));
        $content .= "<br /><br />";
        $content .= PHPWS_Text::secureLink(_('Download CSV'), 'hms',
            array('type'=>'letter', 'op'=>'csv', 'file'=>$filename));

        return $content;
    }

    function pdf_factory()
    {
        $pdf = new FPDF('P','in','Letter');
        $pdf->SetMargins(1.25,2.0625,1.25);
        $pdf->SetAutoPageBreak(FALSE);
        $pdf->Open();

        return $pdf;
    }

    // Insertion Sort, because we sort by name when we pull
    // it out of the DB, so it's sort of sorted.
    function letterSort(&$letters)
    {
        $count = count($letters);
        for($i = 0; $i < $count; $i++) {
            $temp = $letters[$i];
            $k = $i - 1;

            while($k >= 0 && 
                strcmp($temp->address1, $letters[$k]->address1) < 0) {
                $letters[$k+1] = $letters[$k];
                $k--;
            }
            
            $letters[$k+1] = $temp;
        }
    }

    function show_email_menu()
    {
        $message = 'Are you sure you want to send assignment status emails?<br /><br />';

        $form = &new PHPWS_Form('hms_send_email');
        $form->addSubmit('yes', 'Yes');
        $form->addButton('no', 'No');
        $form->addHidden('type', 'letter');
        $form->addHidden('op', 'email');

        $message .= implode(' ', $form->getTemplate());

        return $message;
    }
    
    function email()
    {
        // Accumulate output if any
        $output = '';

        $db = &new PHPWS_DB('hms_assignment');
        $db->addWhere('email_sent', 0);
        $db->addColumn('asu_username');
        $db->addColumn('bed_id');

        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            return 'Database Error no emails were sent';
        }

        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        foreach($result as $assignment){
            //get the students real name from their asu_username
            PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            $student = HMS_SOAP::get_student_info($assignment['asu_username']);
            $name = $student->first_name . (strlen($student->middle_name) > 1 ? ' ' . $student->middle_name . ' ' : ' ') . $student->last_name;

            //get the location of their assignment
            PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
            $bed = &new HMS_Bed($assignment['bed_id']);
            $location = $bed->where_am_i();

            //get the list of roommates
            $roommates = array();
            $room = $bed->get_parent();
            if($room->is_in_suite()){
                $suite = new HMS_Suite($room->suite_id);
                $assignees = $suite->get_assignees();
            } else {
                $assignees = $room->get_assignees();
            }

            if(sizeof($assignees > 1)){
                foreach($assignees as $roommate){
                    // Don't include *this* student in the roommate list
                    if($roommate->asu_username == $assignment['asu_username']){
                        continue;
                    }
                    $roommates[] = HMS_SOAP::get_full_name_inverted($roommate->asu_username) . ' (' . $roommate->asu_username . '@appstate.edu)';
                }
            } else {
                $roommates = null;
            }

            // Send the email

            HMS_Email::send_assignment_email($assignment['asu_username'], $name, $location, $roommates);

            // Mark the student as having received an email
            $db->reset();
            $db->addWhere('asu_username', $assignment['asu_username']);
            $db->addValue('email_sent', 1);
            $rslt = $db->update();

            if(PHPWS_Error::logIfError($rslt)){
                $output .= 'Database error, could not set email flag for ' . $assignment['asu_username'] . 'please contact ESS.';
            }
        }

        if(strlen($output) == 0){
            return 'Emails sent successfully';
        }

        return $output;
    }
}

?>
