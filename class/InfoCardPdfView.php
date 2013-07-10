<?php 

require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdf.php';
require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdi.php';

/**
 * View class for generating the Resident Information Card PDF.
 *
 * @package Hms
 * @author Jeremy Booker
 */
class InfoCardPdfView {

    private $pdf; // Reference to a FPDF object
    
    private $student;
    private $hall;
    private $room;
    private $checkin;
    private $application;
    private $damages;

    /**
     * Constructor
     *
     * @param Student $student
     * @param HMS_Residence_Hall $hall
     * @param HMS_Room $room
     * @param HousingApplication $application
     * @param Checkin $checkin
     */
    public function __construct(FPDF &$fpdf, Student $student, HMS_Residence_Hall $hall, HMS_Room $room, HousingApplication $application, Checkin $checkin, Array $damages)
    {
        $this->pdf = $fpdf;
        
        $this->student		= $student;
        $this->hall			= $hall;
        $this->room			= $room;
        $this->application	= $application;
        $this->checkin		= $checkin;
        $this->damages      = $damages;
        
        $this->generatePdf();
    }


    /**
     * Returns the FPDI object representing the PDF document.
     *
     * @return FPDI
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * Does the actual work of generating the PDF.
     */
    private function generatePdf()
    {
        //$this->pdf = new FPDI('L', 'mm', 'Letter');

        //$pagecount = $this->pdf->setSourceFile(PHPWS_SOURCE_DIR . 'mod/hms/pdf/ric-opt.pdf');
        //$tplidx = $this->pdf->importPage(1);
        $this->pdf->addPage();
        //$this->pdf->useTemplate($tplidx);

        $this->pdf->setFont('Times', 'B', 20);

        /******
         * Top Row
         */

        // Room Number & Hall Name
        $this->pdf->setXY(10, 10);
        $this->pdf->cell(60, 5, $this->room->getRoomNumber() . ' ' . $this->hall->getHallName());

        // Name
        $name = $this->student->getFullNameInverted();

        // Preferred Named
        $prefName = $this->student->getPreferredName();
        if(!is_null($prefName) && $prefName != '' && $prefName != $this->student->getFirstName()) {
            $name .= ' (' . $prefName . ')';
        }
        
        $this->pdf->setXY(110, 10);
        $this->pdf->cell(50, 5, $name);
        
        // Banner ID
        $this->pdf->setXY(240, 10);
        $this->pdf->cell(50, 5, $this->student->getBannerId());

        /***************
         * Student Info
        */
        $this->pdf->setFont('Times', null, 14);
        
        // Term
        $this->pdf->setXY(10, 25);
        $this->pdf->cell(50, 5, Term::toString($this->checkin->getTerm()));
        
        // Date of Birth
        /***
         * $dob[0] == year
         * $dob[1] == month
         * $dob[2] == day
         */
        $dob = explode('-', $this->student->getDOB());
        $this->pdf->setXY(90, 25);
        $this->pdf->cell(50, 5, 'Birthday: ' . $dob[1] . '/' . $dob[2] . '/' . $dob[0]);
        
        // Sex
        $this->pdf->setFont('Times', null, 14);
        $this->pdf->SetXY(146, 25);
        $this->pdf->cell(50, 5, $this->student->getPrintableGender());
        
        // Classification
        $this->pdf->setXY(210, 25);
        $this->pdf->cell(50, 5, $this->student->getPrintableClass());
        
        
        // Email
        $this->pdf->setXY(10, 35);
        $this->pdf->cell(50, 5, $this->student->getUsername() . '@appstate.edu');
        
        // Cell phone
        $this->pdf->setXY(146, 35);
        $this->pdf->cell(50, 5, 'Cell: ' . $this->application->getCellPhone());
        

        /*********************
         * Emergency Contact *
        */

        // Background box
        $this->pdf->setXY(10, 45);
        $this->pdf->setFillColor(250,250,250);
        $this->pdf->setDrawColor(150, 150, 150);
        $this->pdf->cell(125, 25, ' ', 1, 0, 'L', 1);
        
        // Box header
        $this->pdf->setXY(11, 46);
        $this->pdf->setFont('Times', null, 16);
        $this->pdf->cell(50, 5, 'Emergency Contact');
        
        $this->pdf->setXY(11, 54);
        $this->pdf->setFont('Times', null, 14);
        $this->pdf->cell(50, 5, $this->application->getEmergencyContactName());

        $this->pdf->setXY(75, 54);
        $this->pdf->cell(50, 5, 'Relation: ' . $this->application->getEmergencyContactRelationship());

        $this->pdf->setXY(11, 62);
        $this->pdf->cell(50, 5, 'Phone: ' . $this->application->getEmergencyContactPhone());

        $this->pdf->setXY(75, 62);
        $this->pdf->cell(50, 5, $this->application->getEmergencyContactEmail());

        /*******************
         * Missing Persons *
         */
        
        // Background Box
        $this->pdf->setXY(145, 45);
        $this->pdf->setFillColor(250,250,250);
        $this->pdf->setDrawColor(150, 150, 150);
        $this->pdf->cell(125, 25, ' ', 1, 0, 'L', 1);
        
        // Box Header
        $this->pdf->setXY(146, 46);
        $this->pdf->setFont('Times', null, 16);
        $this->pdf->cell(50, 5, 'Missing Person Contact');
        
        
        $this->pdf->setXY(146, 54);
        $this->pdf->setFont('Times', null, 14);
        $this->pdf->cell(50, 5, $this->application->getMissingPersonName());

        $this->pdf->setXY(210, 54);
        $this->pdf->cell(50, 5, 'Relation: ' . $this->application->getMissingPersonRelationship());

        $this->pdf->setXY(146, 62);
        $this->pdf->cell(50, 5, 'Phone: ' . $this->application->getMissingPersonPhone());

        $this->pdf->setXY(210, 62);
        $this->pdf->cell(50, 5, $this->application->getMissingPersonEmail());

        
        /************
         * Medical Stuff
         */

        $medicalConditions = $this->application->getEmergencyMedicalCondition();
        
        if($medicalConditions != '') {
            
            // Bounding box
            $this->pdf->setXY(10, 75);
            $this->pdf->setFillColor(250,250,250);
            $this->pdf->setDrawColor(150, 150, 150);
            $this->pdf->cell(260, 27, ' ', 1, 0, 'L', 1);
            
            // Label
            $this->pdf->setXY(10, 77);
            $this->pdf->setFont('Times', null, 16);
            $this->pdf->cell(50, 5, 'Medical Conditions: ');
            
            // Text
            $this->pdf->setXY(10, 83);
            $this->pdf->setFont('Courier', 'B', 15);
            $this->pdf->write(6, $this->application->getEmergencyMedicalCondition());
        } else {
            // Label
            $this->pdf->setXY(10, 77);
            $this->pdf->setFont('Times', null, 16);
            $this->pdf->cell(50, 5, 'Medical Conditions: ');
            
            // Text
            $this->pdf->setXY(10, 83);
            $this->pdf->setFont('Courier', null, 10);
            $this->pdf->setTextColor(150, 150, 150);
            $this->pdf->write(6, '(No medical conditions listed.)');
        }
        
        $this->pdf->setTextColor(0, 0, 0);
        
        /*****************
         * Damages & Check-in / Check-out
         */
        
        // Horizontal Line
        $this->pdf->setDrawColor(0, 0, 0);
        $this->pdf->line(10, 105, 270, 105);
        
        // Verticle Line
        $this->pdf->line(145, 105, 145, 200);
        
        // Check-in
        $this->pdf->setFont('Times', null, 16);
        $this->pdf->setXY(10, 108);
        $this->pdf->cell(50, 5, 'Check-in');
        
        // Check-out
        $this->pdf->setXY(150, 108);
        $this->pdf->cell(50, 5, 'Check-out');
        $this->pdf->setFont('Times', null, 14);
        
        // Check-in Date/time
        $this->pdf->setXY(80, 108);
        $this->pdf->cell(50, 5, date('j M, Y @ g:ia', $this->checkin->getCheckinDate()));
        
        /************
         * Key Code *
         */
        $this->pdf->setXY(10, 118);
        $this->pdf->cell(50, 5, 'Key Code:');
        
        $this->pdf->setXY(33, 118);
        $this->pdf->cell(50, 5, $this->checkin->getKeyCode());
        
        
        /*************
         * Damages   *
         */
        
        $this->pdf->setXY(10, 130);
        $this->pdf->cell(50, 5, 'Existing Room Damages:');
        
        if (isset($this->damages) && count($this->damages) > 0) {
            // Turn the font size down
            $this->pdf->setFont('Times', null, 9);
            
            $damageTypes = DamageTypeFactory::getDamageTypeAssoc();
            
            $xOffset = 10;
            $yOffset = 140; // Distance down the page, we'll increment this as we go
            
            foreach ($this->damages as $dmg) {
                $this->pdf->setXY($xOffset, $yOffset);
            
                $this->pdf->cell(50, 5, '(' . $dmg->getSide() . ') ' . $damageTypes[$dmg->getDamageType()]['category'] . ' ' . $damageTypes[$dmg->getDamageType()]['description']);
                $yOffset += 6;
            }
        }
    }
}

?>
