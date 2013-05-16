<?php 

/**
 * View class for generating the Resident Information Card PDF.
 *
 * @package Hms
 * @author Jeremy Booker
 */
class InfoCardPdfView {

    private $student;
    private $hall;
    private $room;
    private $checkin;
    private $application;

    private $pdf;

    /**
     * Constructor
     *
     * @param Student $student
     * @param HMS_Residence_Hall $hall
     * @param HMS_Room $room
     * @param HousingApplication $application
     * @param Checkin $checkin
     */
    public function __construct(Student $student, HMS_Residence_Hall $hall, HMS_Room $room, HousingApplication $application, Checkin $checkin)
    {
        $this->student		= $student;
        $this->hall			= $hall;
        $this->room			= $room;
        $this->application	= $application;
        $this->checkin		= $checkin;

        require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdf.php';
        require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdi.php';

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
        $this->pdf = new FPDI('L', 'mm', 'Letter');

        $pagecount = $this->pdf->setSourceFile(PHPWS_SOURCE_DIR . 'mod/hms/pdf/ric-opt.pdf');
        $tplidx = $this->pdf->importPage(1);
        $this->pdf->addPage();
        $this->pdf->useTemplate($tplidx);

        $this->pdf->setFont('Times', null, 16);

        /******
         * Top Row
        */

        // Room Number
        $this->pdf->setXY(22, 19);
        $this->pdf->cell(60, 5, $this->room->getRoomNumber());

        // Hall
        $this->pdf->setFont('Times', null, 14);
        $this->pdf->setXY(55, 19);
        $this->pdf->cell(50, 5, $this->hall->getHallName());

        // Banner ID
        $this->pdf->setFont('Times', null, 18);
        $this->pdf->setXY(215, 19);
        $this->pdf->cell(50, 5, $this->student->getBannerId());

        /***************
         * Student Info
        */

        // Name
        $this->pdf->setFont('Times', null, 14);
        $this->pdf->setXY(18, 28);
        $this->pdf->cell(50, 5, $this->student->getLastName());

        $this->pdf->setXY(70, 28);
        $this->pdf->cell(50, 5, $this->student->getFirstName());

        $this->pdf->setXY(140, 28);
        $this->pdf->cell(50, 5, $this->student->getMiddleName());

        // Classification
        //TODO

        // Date of Birth
        $dob = explode('-', $this->student->getDOB());
        $this->pdf->setXY(25, 41);
        $this->pdf->cell(50, 5, $dob[1]); // Month

        $this->pdf->setXY(35, 41); // Day
        $this->pdf->cell(50, 5, $dob[2]);

        $this->pdf->setXY(45, 41); // Year
        $this->pdf->cell(50, 5, $dob[0]);


        //TODO
        // Preferred name?

        // Email
        $this->pdf->setXY(175, 41);
        $this->pdf->cell(50, 5, $this->student->getUsername());

        // Permanent Address
        $address = $this->student->getAddress(null);

        $this->pdf->setXY(35, 50);
        $this->pdf->cell(50, 5, $address->line1);

        //TODO line2, line 3?

        $this->pdf->setXY(6, 58);
        $this->pdf->cell(50, 5, $address->city);

        $this->pdf->setXY(66, 58);
        $this->pdf->cell(50, 5, $address->state);

        $this->pdf->setXY(100, 58);
        $this->pdf->cell(50, 5, $address->zip);

        // Cell phone
        $this->pdf->setXY(180, 58);
        $this->pdf->cell(50, 5, $this->application->getCellPhone());

        /*********************
         * Emergency Contact *
        */

        $this->pdf->setXY(50, 70);
        $this->pdf->cell(50, 5, $this->application->getEmergencyContactName());

        $this->pdf->setXY(186, 70);
        $this->pdf->cell(50, 5, $this->application->getEmergencyContactRelationship());

        $this->pdf->setXY(54, 80);
        $this->pdf->cell(50, 5, $this->application->getEmergencyContactPhone());

        $this->pdf->setXY(153, 80);
        $this->pdf->cell(50, 5, $this->application->getEmergencyContactEmail());

        $this->pdf->setXY(109, 88);
        $this->pdf->cell(50, 5, $this->application->getEmergencyMedicalCondition());

        /*******************
         * Missing Persons *
        */
        $this->pdf->setXY(10, 173);
        $this->pdf->cell(50, 5, $this->application->getMissingPersonName());

        $this->pdf->setXY(10, 185);
        $this->pdf->cell(50, 5, $this->application->getMissingPersonRelationship());

        $this->pdf->setXY(10, 194);
        $this->pdf->cell(30, 1, $this->application->getMissingPersonPhone());

        $this->pdf->setXY(40, 194);
        $this->pdf->cell(30, 1, $this->application->getMissingPersonEmail());

        /************
         * Key Code *
        */
        $this->pdf->setXY(160, 122);
        $this->pdf->cell(50, 5, $this->checkin->getKeyCode());
    }
}

?>
