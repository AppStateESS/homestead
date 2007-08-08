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
    var $hall       = NULL;
    var $room       = NULL;
    var $room_phone = NULL;
    var $roommate   = NULL;
    var $rm_email   = NULL;
    var $checkin    = NULL;
    var $message    = NULL;

    function Letter()
    {
    }

    function render(&$pdf)
    {
        $pdf->AddPage();
        $pdf->SetFont('Times');

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

        $pdf->Write(HEIGHT, "Hall: ");
        $pdf->Cell(2.25, HEIGHT, $this->hall);
        $pdf->Write(HEIGHT, " Room: ");
        $pdf->Cell(0.50, HEIGHT, $this->room);
        $pdf->Write(HEIGHT, " Room Phone: ");
        $pdf->Cell(1.25, HEIGHT, $this->room_phone);
        $pdf->Ln(HEIGHT);

        $pdf->Write(HEIGHT, "Roommate: ");
        $pdf->Cell(2.75, HEIGHT, $this->roommate);
        $pdf->Write(HEIGHT, " Email: ");
        $pdf->Cell(1.75, HEIGHT, $this->rm_email);
        $pdf->Ln(HEIGHT);
        $pdf->Ln(HEIGHT);

        $pdf->Write(HEIGHT, "Check-in Time: ");
        $pdf->Cell(4.5, HEIGHT, $this->checkin);
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

    function put_into_pile(&$freshmen, &$upperclassmen, $student)
    {
        $sql = "
            SELECT
                hms_cached_student_info.*,
                hms_assignment.id as a_id
            FROM hms_cached_student_info
            JOIN hms_assignment ON
                hms_cached_student_info.asu_username = hms_assignment.asu_username
            WHERE
                hms_cached_student_info.asu_username = '$student' AND
                hms_assignment.deleted = 0
        ";

        $row = PHPWS_DB::getRow($sql);
        if(PHPWS_Error::isError($row)) {
            test($row,1);
        }

        $sql = "
            UPDATE hms_assignment
            SET letter_printed = 1
            WHERE hms_assignment.id = {$row['a_id']}
        ";
        PHPWS_DB::getAll($sql);

        $letter = new HMS_Letter;

        $letter->address1 = $row['last_name']  . ', ' .
                            $row['first_name'] . ' '  .
                            $row['middle_name'];
                            
        $letter->address2 = $row['address1'];
        
        $citystatezip = $row['city']  . ', ' .
                        $row['state'] . ' '  .
                        $row['zip'];
                        
        if(empty($row['address2'])) {
            $letter->address3 = $citystatezip;
        } else {
            $letter->address3 = $row['address2'];
            if(empty($row['address3'])) {
                $letter->address4 = $citystatezip;
            } else {
                $letter->address4 = $row['address3'];
                $letter->address5 = $citystatezip;
            }
        }

        $letter->date     = strftime("%B %d, %Y");
        $letter->semester = "Fall, 2007";
        
        $letter->hall       = $row['hall_name'];
        $letter->room       = $row['room_number'];
        $letter->room_phone = $row['room_phone'];

        $letter->roommate = $row['roommate_name'];
        $letter->rm_email = $row['roommate_user'] . '@appstate.edu';

        $letter->checkin = $row['movein_time'];
        $letter->message = "Freshmen and transfer check-in is August 17 from 9am to 6pm.  Returning student check-in starts on August 18.  See above for your scheduled time.  If you have a conflict, you can check-in anytime after your scheduled time, until 6pm on August 21.  Failure to check-in by August 21 by 6pm will result in assignment cancellation.  (See pages 15-16 of the Residence Hall License Contract booklet).";

        if($row['student_type'] == 'F' && $row['credit_hours'] == 0) {
            $freshmen[] = $letter;
        } else {
            $upperclassmen[] = $letter;
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

        // Get everyone that needs a letter
        $sql = "
            SELECT
                hms_cached_student_info.asu_username,
                hms_cached_student_info.roommate_user
            FROM hms_cached_student_info
            JOIN hms_assignment ON
                hms_assignment.asu_username = hms_cached_student_info.asu_username
            WHERE
                hall_name != 'Mountaineer Apartments' AND
                hms_assignment.deleted = 0 AND
                hms_assignment.letter_printed = 0
            ORDER BY
                last_name,
                first_name,
                middle_name
        ";

        $results = PHPWS_DB::getAll($sql);
        if(PHPWS_Error::isError($results)) {
            test($results,1);
        }

        // Get out of here if no one needs a letter
        if(is_null($results)) {
            return "No new letters needed to be generated.";
        }

        // Throw out duplicates
        foreach($results as $result) {
            if(!in_array($result['asu_username'], $needs_letter))
                $needs_letter[] = $result['asu_username'];
            if(!in_array($result['roommate_user'], $needs_letter))
                $needs_letter[] = $result['roommate_user'];
        }

        // Separate into freshmen and upperclassmen
        // Also initialize HMS_Letter objects for them
        $f_letters = array();
        $u_letters = array();
        foreach($needs_letter as $student) {
            HMS_Letter::put_into_pile($f_letters, $u_letters, $student);
        }

        // Sort
        HMS_Letter::letterSort($f_letters);
        HMS_Letter::letterSort($u_letters);

        // Total counts of letters created
        $freshcount = count($f_letters);
        $uppercount = count($u_letters);
        
        // Initialize PDF and CSV files
        $pdf = HMS_Letter::pdf_factory();
        $csv = "";

        // Render the letters and CSVs
        $q = '"';
        foreach($f_letters as $letter) {
            $letter->render($pdf);
            $csv .= "$q{$letter->address1}$q,$q{$letter->address2}$q," .
                    "$q{$letter->address3}$q,$q{$letter->address4}$q," .
                    "$q{$letter->address5}$q\n";
        }
        foreach($u_letters as $letter) {
            $letter->render($pdf);
            $csv .= "$q{$letter->address1}$q,$q{$letter->address2}$q," .
                    "$q{$letter->address3}$q,$q{$letter->address4}$q," .
                    "$q{$letter->address5}$q\n";
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
        $content = "Generated letters for $freshcount freshmen and $uppercount upperclassmen.<br /><br />";
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
}

?>
