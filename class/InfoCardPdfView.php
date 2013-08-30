<?php

require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdf.php';
require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdi.php';

PHPWS_Core::initModClass('hms', 'DamageTypeFactory.php');

/**
 * View class for generating the Resident Information Card PDF.
 *
 * @package Hms
 * @author Jeremy Booker
 */
class InfoCardPdfView {

    private $pdf;

    private $damageTypes;

    /**
     * Constructor
     *
     * @param FPDF $fpdf Reference to an FPDF object. This info card will be added as a new page to this PDF object.
     * @param InfoCard The info card model object to view
     */
    public function __construct()
    {
        $this->pdf = new FPDF('L', 'mm', 'Letter');

        $this->damageTypes = DamageTypeFactory::getDamageTypeAssoc();
    }

    public function getPdf()
    {
        return $this->pdf;
    }

	/**
     * Does the actual work of generating the PDF.
     */
    public function addInfoCard(InfoCard $infoCard)
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
        $this->pdf->cell(60, 5, $infoCard->getRoom()->getRoomNumber() . ' ' . $infoCard->getHall()->getHallName());

        // Name
        $name = $infoCard->getStudent()->getFullNameInverted();

        // Preferred Named
        $prefName = $infoCard->getStudent()->getPreferredName();
        if(!is_null($prefName) && $prefName != '' && $prefName != $infoCard->getStudent()->getFirstName()) {
            $name .= ' (' . $prefName . ')';
        }

        $this->pdf->setXY(110, 10);
        $this->pdf->cell(50, 5, $name);

        // Banner ID
        $this->pdf->setXY(240, 10);
        $this->pdf->cell(50, 5, $infoCard->getStudent()->getBannerId());

        /***************
         * Student Info
        */
        $this->pdf->setFont('Times', null, 14);

        // Term
        $this->pdf->setXY(10, 25);
        $this->pdf->cell(50, 5, Term::toString($infoCard->getCheckin()->getTerm()));

        // Date of Birth
        /***
         * $dob[0] == year
         * $dob[1] == month
         * $dob[2] == day
         */
        $dob = explode('-', $infoCard->getStudent()->getDOB());
        $this->pdf->setXY(90, 25);
        $this->pdf->cell(50, 5, 'Birthday: ' . $dob[1] . '/' . $dob[2] . '/' . $dob[0]);

        // Sex
        $this->pdf->setFont('Times', null, 14);
        $this->pdf->SetXY(146, 25);
        $this->pdf->cell(50, 5, $infoCard->getStudent()->getPrintableGender());

        // Classification
        $this->pdf->setXY(210, 25);
        $this->pdf->cell(50, 5, $infoCard->getStudent()->getPrintableClass());


        // Email
        $this->pdf->setXY(10, 35);
        $this->pdf->cell(50, 5, $infoCard->getStudent()->getUsername() . '@appstate.edu');

        // Cell phone
        $this->pdf->setXY(146, 35);
        $this->pdf->cell(50, 5, 'Cell: ' . $infoCard->getApplication()->getCellPhone());


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
        $this->pdf->cell(50, 5, $infoCard->getApplication()->getEmergencyContactName());

        $this->pdf->setXY(75, 54);
        $this->pdf->cell(50, 5, 'Relation: ' . $infoCard->getApplication()->getEmergencyContactRelationship());

        $this->pdf->setXY(11, 62);
        $this->pdf->cell(50, 5, 'Phone: ' . $infoCard->getApplication()->getEmergencyContactPhone());

        $this->pdf->setXY(75, 62);
        $this->pdf->cell(50, 5, $infoCard->getApplication()->getEmergencyContactEmail());

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
        $this->pdf->cell(50, 5, $infoCard->getApplication()->getMissingPersonName());

        $this->pdf->setXY(210, 54);
        $this->pdf->cell(50, 5, 'Relation: ' . $infoCard->getApplication()->getMissingPersonRelationship());

        $this->pdf->setXY(146, 62);
        $this->pdf->cell(50, 5, 'Phone: ' . $infoCard->getApplication()->getMissingPersonPhone());

        $this->pdf->setXY(210, 62);
        $this->pdf->cell(50, 5, $infoCard->getApplication()->getMissingPersonEmail());


        /************
         * Medical Stuff
         */

        $medicalConditions = $infoCard->getApplication()->getEmergencyMedicalCondition();

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
            $this->pdf->write(6, $infoCard->getApplication()->getEmergencyMedicalCondition());
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

        /*******************
         * Check-in Column *
         */
        $this->pdf->setFont('Times', null, 16);
        $this->pdf->setXY(10, 108);
        $this->pdf->cell(50, 5, 'Check-in');
        $this->pdf->setFont('Times', null, 14);

        // Check-in Date/time
        $this->pdf->setXY(80, 108);
        $this->pdf->cell(50, 5, date('j M, Y @ g:ia', $infoCard->getCheckin()->getCheckinDate()));

        /************
         * Key Code *
         */
        $this->pdf->setXY(10, 118);
        $this->pdf->cell(50, 5, 'Key Code:');

        $this->pdf->setXY(33, 118);
        $this->pdf->cell(50, 5, $infoCard->getCheckin()->getKeyCode());


        /********************
         * Check-in Damages *
         */

        $this->pdf->setXY(10, 130);
        $this->pdf->cell(50, 5, 'Existing Room Damages:');

        $damageList = $infoCard->getCheckinDamages();

        if ($damageList !== null && count($damageList) > 0) {
            // Turn the font size down
            $this->pdf->setFont('Times', null, 9);

            $xOffset = 10;
            $yOffset = 140; // Distance down the page, we'll increment this as we go

            foreach ($damageList as $dmg) {
                $this->pdf->setXY($xOffset, $yOffset);

                $this->pdf->cell(50, 5, '(' . $dmg->getSide() . ') ' . $this->damageTypes[$dmg->getDamageType()]['category'] . ' ' . $this->damageTypes[$dmg->getDamageType()]['description']);
                $yOffset += 6;
            }
        }

        /********************
         * Check-out Column *
         */
        // Check-out
        $this->pdf->setFont('Times', null, 16);
        $this->pdf->setXY(150, 108);
        $this->pdf->cell(50, 5, 'Check-out');
        $this->pdf->setFont('Times', null, 14);


        // If the checkout timestamp is set, show it as a nice date
        $checkoutTimestamp = $infoCard->getCheckin()->getCheckoutDate();
        if (!is_null($checkoutTimestamp)) {
            // Check-out Date/time
            $this->pdf->setXY(210, 108);
            $this->pdf->cell(50, 5, date('j M, Y @ g:ia', $checkoutTimestamp));
        }

        // Key code at check-out
        $this->pdf->setXY(150, 118);
        $this->pdf->cell(50, 5, 'Key Code:');

        $this->pdf->setXY(173, 118);
        $this->pdf->cell(50, 5, $this->pdf->cell(50, 5, $infoCard->getCheckin()->getCheckoutKeyCode()));


        /*********************
         * Check-out Damages *
         */

        $this->pdf->setXY(150, 130);
        $this->pdf->cell(50, 5, 'Damages at Check-out:');

        $checkoutDamages = $infoCard->getCheckoutDamages();

        if ($checkoutDamages !== null && count($checkoutDamages) > 0) {
            // Turn the font size down
            $this->pdf->setFont('Times', null, 9);

            $xOffset = 150;
            $yOffset = 140; // Distance down the page, we'll increment this as we go

            foreach ($checkoutDamages as $dmg) {
                $this->pdf->setXY($xOffset, $yOffset);

                $this->pdf->cell(50, 5, '(' . $dmg->getSide() . ') ' . $this->damageTypes[$dmg->getDamageType()]['category'] . ' ' . $this->damageTypes[$dmg->getDamageType()]['description']);
                $yOffset += 6;
            }
        }
    }
}

?>
