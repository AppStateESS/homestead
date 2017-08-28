<?php

namespace Homestead\report\GenderDistributionByHall;

/**
 * TwentyOne - Report that calculates % of students 21 or older.
 *
 * @author Jeremy Booker
 * @package HMS
 */

class GenderDistributionByHall extends Report implements iCsvReport {

    const friendlyName = 'Gender Distribution By Hall';
    const shortName = 'GenderDistributionByHall';

    private $term;

    // Accumulator for output rows (sub-arrays)
    private $rows;

    private $totalCurrOccupancy;

    private $totalMales;
    private $totalFemales;
    private $totalCoed;

    private $totalMalePercent;
    private $totalFemalePercent;
    private $totalCoedPercent;

    public function __construct($id = 0)
    {
        parent::__construct($id);

        $this->rows = array();

        $this->totalCurrOccupancy = 0;
        $this->totalMales = 0;
        $this->totalFemales = 0;
        $this->totalCoed = 0;

        $this->totalMalePercent = 0;
        $this->totalFemalePercent = 0;
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        if (!isset($this->term) || is_null($this->term)) {
            throw new \InvalidArgumentException('Missing term.');
        }

        // Get all of the residence halls for this term
        $halls = ResidenceHallFactory::getHallsForTerm($this->term);

        foreach($halls as $hall){
            $hallName = $hall->hall_name;
            $maxOccupancy = $hall->get_number_of_online_nonoverflow_beds();
            $currOccupancy = $hall->get_number_of_assignees();
            $this->totalCurrOccupancy += $currOccupancy;

            $males = 0;
            $females = 0;
            $coed = 0;

            // Get all the assignments for this hall, joined up to the
            // room level so we can determine gender, and joined up to the
            // hall level so we can limit by hall
            $db = new \PHPWS_DB('hms_assignment');
            $db->addColumn('hms_assignment.*');
            $db->addColumn('hms_room.gender_type');

            $db->addJoin('LEFT', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
            $db->addJoin('LEFT', 'hms_bed', 'hms_room', 'room_id', 'id');
            $db->addJoin('LEFT', 'hms_room', 'hms_floor', 'floor_id', 'id');
            $db->addJoin('LEFT', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

            $db->addWhere('hms_assignment.term', $this->term);
            $db->addWhere('hms_residence_hall.id', $hall->id);

            $results = $db->select();

            if(empty($results)){
                continue;
            }elseif(\PEAR::isError($results)){
                throw new DatabaseException($results->toString());
            }

            // foreach assignment, tally up the genders
            foreach($results as $assign){
                if($assign['gender_type'] == MALE){
                    $males++;
                    $this->totalMales++;
                }else if($assign['gender_type'] == FEMALE){
                    $females++;
                    $this->totalFemales++;
                }
                else if($assign['gender_type']  == COED)
                {
                    $coed++;
                    $this->totalCoed++;
                }
            }

            if($males == 0){
                $malePercent = 0;
            }else{
                $malePercent = round(($males / $currOccupancy) * 100, 1);
            }

            if($females == 0){
                $femalePercent = 0;
            }else{
                $femalePercent = round(($females / $currOccupancy) * 100, 1);
            }

             if($coed == 0){
                $coedPercent = 0;
            }else{
                $coedPercent = round(($coed / $currOccupancy) * 100, 1);
            }

            $this->rows[] = array('hallName'       => $hallName,
                            'maxOccupancy'   => $maxOccupancy,
                            'currOccupancy'  => $currOccupancy,
                            'males'          => $males,
                            'malePercent'    => $malePercent,
                            'females'        => $females,
                            'femalePercent'  => $femalePercent,
                            'coed'           => $coed,
                            'coedPercent'    => $coedPercent);
            ;
        }

        $this->totalMalePercent = round(($this->totalMales / $this->totalCurrOccupancy) * 100,1);
        $this->totalFemalePercent = round(($this->totalFemales / $this->totalCurrOccupancy) * 100,1);
        $this->totalCoedPercent = round(($this->totalCoed / $this->totalCurrOccupancy) * 100,1);
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getTotalCurrOccupancy(){
        return $this->totalCurrOccupancy;
    }

    public function getTotalMales(){
        return $this->totalMales;
    }

    public function getTotalFemales(){
        return $this->totalFemales;
    }

    public function getTotalCoed()
    {
        return $this->totalCoed;
    }

    public function getTotalMalePercent(){
        return $this->totalMalePercent;
    }

    public function getTotalFemalePercent(){
        return $this->totalFemalePercent;
    }

    public function getTotalCoedPercent(){
        return $this->totalCoedPercent;
    }

    public function getCsvColumnsArray(){
        return array('Hall Name', 'Max Occupany', 'Current Occupancy', 'Males', 'Male %', 'Females', 'Female %');
    }

    public function getCsvRowsArray()
    {
        $result = $this->rows;
        $result[] = array('',
                        $this->getTotalCurrOccupancy(),
                        $this->getTotalMales(),
                        $this->getTotalMalePercent(),
                        $this->getTotalFemales(),
                        $this->getTotalFemalePercent());
        return $result;
    }
}
