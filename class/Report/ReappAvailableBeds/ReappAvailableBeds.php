<?php

namespace Homestead\Report\ReappAvailableBeds;

use \Homestead\Report;
use \Homestead\iCsvReport;
use \Homestead\HMS_Residence_Hall;
use \Homestead\HMS_Util;

/**
* Report for accessing the number of beds in each hall are still Available
* for reapplying students.
*
* @author Chris Detsch
* @package HMS
*/
class ReappAvailableBeds extends Report implements iCsvReport
{

    const friendlyName = 'Reapplication Available Beds';
    const shortName = 'ReappAvailableBeds';

    private $term;

    private $data;

    public function __construct($id = 0)
    {
        parent::__construct($id);

        $data = array();
    }

    public function execute()
    {
        $halls = HMS_Residence_Hall::get_halls($this->term);

        $rows = array();

        foreach ($halls as $hall)
        {
            if($hall->count_avail_lottery_rooms('1') || $hall->count_avail_lottery_rooms('0'))
            {
                $row = array();

                $row['HALL_NAME'] = $hall->getHallName();

                $row['MALE_FREE'] = $hall->count_avail_lottery_rooms('1');
                $row['FEMALE_FREE'] = $hall->count_avail_lottery_rooms('0');


                $rooms = $hall->get_rooms();

                $roomRows = "";

                foreach ($rooms as $room) {
                    if($room->count_avail_lottery_beds() > 0)
                    {

                        $roomRow = "<tr><td>";
                        $roomRow = $roomRow . $room->getRoomNumber();
                        $roomRow = $roomRow . "</td><td>";
                        $roomRow = $roomRow . HMS_Util::formatGender($room->getGender());
                        $roomRow = $roomRow . "</td><td>";
                        $roomRow = $roomRow . $room->count_avail_lottery_beds();
                        $roomRow = $roomRow . "</td></tr>";
                        $roomRows = $roomRows . $roomRow;
                    }

                }
                $row['ROOMS'] = $roomRows;

                $rows[] = $row;

            }

        }

        $this->data = $rows;

    }

    /****************************
    * Accessor/Mutator Methods *
    ****************************/

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getCsvColumnsArray()
    {
        return array_keys($this->data[0]);
    }

    public function getCsvRowsArray(){
        return $this->data;
    }

    public function getData()
    {
        return $this->data;
    }


}
