<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

/**
 * Description of HallOccupancy
 *
 * @author matt
 */
class HallOccupancy extends Report {
    const friendlyName = 'Hall Occupancy';
    const shortName = 'HallOccupancy';

    private $term;
    private $rows;
    private $problems;

    public function __construct($id=0)
    {
        parent::__construct($id);
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function execute()
    {
        if (!isset($this->term) || is_null($this->term)) {
            throw new InvalidArgumentException('Missing term.');
        }

        $term = Term::getTermSem($this->term);

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $halls = HMS_Residence_Hall::get_halls(Term::getSelectedTerm());

        $total_beds = 0; // accumulator for counting beds
        $vacant_beds = 0;

        foreach ($halls as $hall) {

            $beds_by_hall = 0;
            $vacant_beds_by_hall = 0;

            $floors = $hall->get_floors();
            if ($floors == NULL) {
                continue;
            }
            foreach ($floors as $floor) {
                $vacant_beds_by_floor = 0;
                $total_beds_by_floor = 0;

                if ($floor->is_online == 0) {
                    $floor_array[$floor->floor_number]['floor_number'] = $floor->floor_number . ' - Offline';
                    $floor_array[$floor->floor_number]['vacancies_by_floor'] = null;
                    $floor_array[$floor->floor_number]['total_beds_by_floor'] = null;
                    continue;
                }


                $rooms = $floor->get_rooms();
                if ($rooms == NULL) {
                    continue;
                }
                foreach ($rooms as $room) {
                    if (!$room->is_online == 1) {
                        continue;
                    }
                    $beds = $room->get_beds();
                    if (!empty($beds)) {
                        foreach ($beds as $bed) {
                            $beds_by_hall++;
                            $total_beds_by_floor++;
                            $total_beds++;
                            if ($bed->has_vacancy()) {
                                $vacant_beds++;
                                $vacant_beds_by_hall++;
                                $vacant_beds_by_floor++;
                            }
                        }
                    }
                }
                $floor_array[$floor->floor_number]['floor_number'] = $floor->floor_number;
                $floor_array[$floor->floor_number]['vacancies_by_floor'] = $vacant_beds_by_floor;
                $floor_array[$floor->floor_number]['total_beds_by_floor'] = $total_beds_by_floor;
            }
            $hall_array[$hall->hall_name]['hall_name'] = $hall->hall_name;
            $hall_array[$hall->hall_name]['hall_vacancies'] = $vacant_beds_by_hall;
            $hall_array[$hall->hall_name]['hall_total_beds'] = $beds_by_hall;
            ksort($floor_array);
            $hall_array[$hall->hall_name]['floor_rows'] = $floor_array;
        }
        $this->rows = array('total_beds' => $total_beds, 'vacant_beds' => $vacant_beds, 'hall_rows' => $hall_array);
    }

    public function getRows()
    {
        return $this->rows;
    }

}

?>
